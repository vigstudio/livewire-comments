@props(['replies' => false, 'comment' => null])

@php
    $reactionsGrouped = $comment->reactionsGroup();
    $policy = $comment->policy ?? [];
    $hasActions = ($policy['update'] ?? false) || ($policy['delete'] ?? false) || ($policy['report'] ?? false);
@endphp

<article
    id="vgcomment-{{ $comment->uuid }}"
    class="vg-comment {{ $replies ? 'vg-comment--reply' : '' }}"
    x-data="LivewireComments.comment('{{ $comment->uuid }}')"
    x-init="init()"
    @post-success-comments.window="reply = false"
    x-bind:class="{ 'is-active': window.location.hash == '#vgcomment-{{ $comment->uuid }}' }"
>
    <div class="vg-comment__layout">
        <div class="vg-comment__avatar">
            <img src="{{ $comment->author_avatar }}" alt="{{ $comment->author_name }}">
        </div>

        <div class="vg-comment__main">
            <header
                class="vg-comment__header"
                wire:ignore
                x-data="LivewireComments.menu({ open: false })"
                x-init="init()"
                @keydown.escape.stop="open = false"
                @click.away="onClickAway($event)"
            >
                <div class="vg-comment__meta">
                    <strong class="vg-comment__author">{{ $comment->author_name }}</strong>
                    @if ($comment->parent)
                        <span class="vg-comment__replying">replied to {{ $comment->parent->author_name }}</span>
                    @endif
                    <span class="vg-comment__sep">·</span>
                    <time class="vg-comment__time" datetime="{{ $comment->created_at }}">{{ $comment->time }}</time>
                </div>

                @if ($hasActions)
                    <div class="vg-menu">
                        <button
                            type="button"
                            class="vg-icon-btn"
                            x-ref="button"
                            @click="onButtonClick()"
                            x-bind:aria-expanded="open.toString()"
                            title="{{ __('vgcomment::comment.more_actions') }}"
                        >
                            <x-heroicons::icon name="ellipsis-vertical-s" class="vg-icon" />
                        </button>
                        <div
                            class="vg-menu__panel"
                            x-show="open"
                            x-ref="menu-items"
                            x-cloak
                            role="menu"
                            style="display: none;"
                        >
                            @if ($policy['update'] ?? false)
                                <button type="button" class="vg-menu__item" role="menuitem" x-on:click="$wire.edit({{ $comment->id }}); open = false;">
                                    <x-heroicons::icon name="pencil-s" class="vg-icon" />
                                    {{ __('vgcomment::comment.edit') }}
                                </button>
                            @endif
                            @if ($policy['report'] ?? false)
                                <button type="button" class="vg-menu__item" role="menuitem" x-on:click="$wire.report({{ $comment->id }}, 'alert'); open = false;">
                                    <x-heroicons::icon name="flag-s" class="vg-icon" />
                                    {{ __('vgcomment::comment.report') }}
                                </button>
                            @endif
                            @if ($policy['delete'] ?? false)
                                <button type="button" class="vg-menu__item vg-menu__item--danger" role="menuitem" x-on:click="$wire.delete({{ $comment->id }}, 'alert'); open = false;">
                                    <x-heroicons::icon name="trash-s" class="vg-icon" />
                                    {{ __('vgcomment::comment.delete') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </header>

            <div class="vg-comment__body">
                @if ($this->editId == $comment->uuid)
                    <livewire:livewire-comments::form method="edit" :wire:key="'edit-'.$comment->id" :editId="$comment->id" />
                @else
                    <div x-data="LivewireComments.content()">{!! $comment->content_html !!}</div>
                @endif
            </div>

            @php
                $contentHtml = (string) ($comment->content_html ?? '');
                $visibleFiles = $comment->files->filter(function ($file) use ($contentHtml) {
                    // Skip attachment render when the same URL is already embedded in markdown HTML.
                    if ($file->isImage() && $contentHtml !== '' && str_contains($contentHtml, (string) $file->url_stream)) {
                        return false;
                    }

                    return true;
                });
            @endphp
            @if ($visibleFiles->isNotEmpty())
                <div class="vg-comment__attachments">
                    @foreach ($visibleFiles as $file)
                        @if ($file->isImage())
                            <a href="{{ $file->url_stream }}" target="_blank" rel="noopener noreferrer" class="vg-file vg-file--image" title="{{ $file->file_name }}">
                                <img src="{{ $file->url_stream }}" alt="{{ $file->file_name }}" loading="lazy">
                                <span>{{ $file->file_name }}</span>
                            </a>
                        @else
                            <a href="{{ $file->url_stream }}" target="_blank" rel="noopener noreferrer" class="vg-file" title="{{ $file->file_name }}">
                                <span aria-hidden="true">📄</span>
                                <span>{{ $file->file_name }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            <footer class="vg-comment__footer">
                <div class="vg-reactions">
                    @foreach ($reactionsGrouped as $type => $group)
                        @php
                            $count = $group->count();
                            $userReacted = $group->contains(fn ($reaction) => (bool) $reaction->user_reacted);
                        @endphp
                        @if ($count > 0)
                            <button
                                type="button"
                                class="vg-reaction-pill {{ $userReacted ? 'is-active' : '' }}"
                                wire:click="toggleReact('{{ $comment->uuid }}', '{{ $type }}')"
                                wire:loading.attr="disabled"
                            >
                                <span>{{ $type }}</span>
                                <span class="vg-reaction-pill__count">{{ $count }}</span>
                            </button>
                        @endif
                    @endforeach

                    <div class="vg-reaction-add" data-reaction-add wire:ignore>
                        <button type="button" class="vg-icon-btn" data-reaction-toggle title="{{ __('vgcomment::comment.add_reaction') }}">
                            <x-heroicons::icon name="face-smile-o" class="vg-icon" />
                        </button>
                    </div>
                </div>

                <button type="button" class="vg-text-btn" @click="reply = !reply">
                    <x-heroicons::icon name="chat-bubble-left-right-o" class="vg-icon" />
                    {{ __('vgcomment::comment.reply') }}
                </button>
            </footer>

            <div class="vg-comment__reply" x-show="reply" x-cloak>
                <livewire:livewire-comments::form
                    method="submit"
                    :wire:key="'form-'.$comment->id"
                    :commentable="$comment->commentable"
                    :pageId="$comment->page_id"
                    :request="[
                        'content' => '',
                        'author_name' => null,
                        'author_email' => null,
                        'author_url' => null,
                        'root_id' => $comment->root_id ? $comment->root_id : $comment->id,
                        'parent_id' => $comment->id,
                        'recaptcha_token' => null,
                    ]"
                />
            </div>

            @if ($comment->replies->isNotEmpty())
                <div class="vg-thread">
                    @foreach ($comment->replies as $reply)
                        <x-livewire-comments::comment :key="$reply->uuid" :replies="true" :comment="$reply" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
