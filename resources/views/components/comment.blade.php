@props(['replies' => false, 'comment' => null])

<div id="vgcomment-{{ $comment->uuid }}"
     class="vcomment-row @if ($replies) children @endif"
     x-data="LivewireComments.comment('{{ $comment->uuid }}', $wire)"
     x-init="emojiPicker('.emoji-button')"
     x-bind:class="{ 'active': window.location.hash == '#vgcomment-{{ $comment->uuid }}' }">

    <div wire:ignore
         class="vgcomment__header"
         x-data="LivewireComments.menu({ open: false })"
         x-init="init()"
         :id="$id('dropdown-menu')"
         @keydown.escape.stop="open = false;"
         @click.away="onClickAway($event)">

        <div class="vgcomment__author">
            <div class="vgcomment__avatar">
                <img class="avatar__image"
                     src="{{ $comment->author_avatar }}"
                     alt="{{ $comment->author_name }}">
                {{-- <span class="avatar--badge">Admin</span> --}}
            </div>

            <div class="vgcomment__info">
                <p>
                    {{ $comment->author_name }}
                    @if ($comment->parent)
                        <span class="vgcomment__reply_text ">
                            {{ __('Reply to :') }} {{ $comment->parent->author_name }}
                        </span>
                    @endif
                </p>
                <p class="vgcomment__time">
                    <time pubdate
                          datetime="{{ $comment->created_at }}"
                          title="{{ $comment->time }}">
                        {{ $comment->time }}
                    </time>
                </p>

            </div>
        </div>

        <div class="vgcomment__dropdown">

            <div>
                <button
                        type="button"
                        class="vgcomment__dropdown_btn"
                        id="menu-button"
                        x-ref="button"
                        @click="onButtonClick();"
                        @keyup.space.prevent="onButtonEnter()"
                        @keydown.enter.prevent="onButtonEnter()"
                        aria-expanded="true"
                        aria-haspopup="true"
                        x-bind:aria-expanded="open.toString()"
                        @keydown.arrow-up.prevent="onArrowUp()"
                        @keydown.arrow-down.prevent="onArrowDown()">

                    <x-heroicons::icon name="ellipsis-vertical-s" class="vgcomemnt_icon-4" />
                </button>
            </div>

            <div x-show="open"
                 class="vgcomment__dropdown-menu"
                 x-ref="menu-items"
                 role="menu"
                 aria-orientation="vertical"
                 aria-labelledby="menu-button"
                 tabindex="-1"
                 @keydown.arrow-up.prevent="onArrowUp()"
                 @keydown.arrow-down.prevent="onArrowDown()"
                 @keydown.tab="open = false"
                 @keydown.enter.prevent="open = false;"
                 @keyup.space.prevent="open = false;"
                 style="display: none;">

                <div class="vgcomment__py-1" role="none">
                    @if ($comment->policy['update'])
                        <a href="javascript:void(0);"
                           class="vgcomment__dropdown-menu--item"
                           x-state:on="Active"
                           x-state:off="Not Active"
                           :class="{ 'vgcomment_select': activeIndex === 0, 'vgcomment__not_select': !(activeIndex === 0) }"
                           role="menuitem"
                           tabindex="-1"
                           :id="$id('menu-item-0')"
                           @mouseenter="onMouseEnter($event)"
                           @mousemove="onMouseMove($event, 0)"
                           @mouseleave="onMouseLeave($event)"
                           x-on:click="$wire.edit({{ $comment->id }}).then((value) => edit = value); open = false;">
                            {{ __('vgcomment::comment.edit') }}
                        </a>
                    @endif

                    @if ($comment->policy['delete'])
                        <a href="javascript:void(0);"
                           class="vgcomment__dropdown-menu--item"
                           :class="{ 'vgcomment_select': activeIndex === 1, 'vgcomment__not_select': !(activeIndex === 1) }"
                           role="menuitem"
                           tabindex="-1"
                           :id="$id('menu-item-1')"
                           @mouseenter="onMouseEnter($event)"
                           @mousemove="onMouseMove($event, 1)"
                           @mouseleave="onMouseLeave($event)"
                           x-on:click="$wire.delete({{ $comment->id }}, 'alert').then((value) => edit = value); open = false;">
                            {{ __('vgcomment::comment.delete') }}
                        </a>
                    @endif

                    @if ($comment->policy['report'])
                        <a href="javascript:void(0);"
                           class="vgcomment__dropdown-menu--item"
                           :class="{ 'vgcomment_select': activeIndex === 2, 'vgcomment__not_select': !(activeIndex === 2) }"
                           role="menuitem"
                           tabindex="-1"
                           :id="$id('menu-item-2')"
                           @mouseenter="onMouseEnter($event)"
                           @mousemove="onMouseMove($event, 2)"
                           @mouseleave="onMouseLeave($event)"
                           x-on:click="$wire.report({{ $comment->id }}, 'alert').then((value) => edit = value); open = false;">
                            {{ __('vgcomment::comment.report') }}
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="vgcomment__body">

        @if ($this->editId == $comment->uuid)
            <livewire:livewire-comments::form method="edit" :wire:key="'edit-'.$comment->id" :editId="$comment->id" />
        @else
            <div x-data="LivewireComments.content()">{!! $comment->content_html !!}</div>
        @endif

    </div>

    <div class="vgcomment__attachments">
        @foreach ($comment->files as $file)
            <a href="{{ $file->url_stream }}" target="_blank" class="file">
                <x-heroicons::icon name="paper-clip-s" class="vgcomemnt_icon-4" />
                <span class="vgcomment_w-full">{{ $file->file_name }} - ({{ number_format($file->size / 1000, 2) }} KB)</span>
            </a>
        @endforeach
    </div>

    <div class="vgcomment__footer">
        <div class="vgcomment__reactions">
            <div class="reactions__group">

                <button type="button" class="emoji-button  vcomments__btn primary-tone">
                    <x-heroicons::icon name="face-smile-o" class="vgcomemnt_icon-3" />
                </button>

                @foreach ($comment->reactionsGroup() as $reaction)
                    <button type="button"
                            class="vcomments__btn @if ($reaction->where('user_reacted', true)->count() > 0) active @else none @endif"
                            @if ($reaction->where('user_reacted', true)->count() > 0) wire:click="unReact('{{ $comment->uuid }}', '{{ $reaction->first()->type }}')" @endif>
                        <span class="emoji">{{ $reaction->first()->type }}</span>
                        <span class="text">{{ $reaction->count() }}</span>
                    </button>
                @endforeach

            </div>
        </div>

        <button class="vcomments__btn none comment-reply" type="button">
            <x-heroicons::icon x-show="reply" name="chevron-up-o" class="vgcomment_mr-2" />
            <x-heroicons::icon x-show="!reply" name="chat-bubble-left-right-o" class="vgcomment_mr-2" />
            {{ __('vgcomment::comment.reply') }}
        </button>
    </div>

    <div class="vgcomment__replybox" x-show="reply">

        <livewire:livewire-comments::form method="submit"
                                          :wire:key="'form-'.$comment->id"
                                          :commentable="$comment->commentable"
                                          :pageId="$comment->page_id"
                                          :request="['root_id' => $comment->root_id ? $comment->root_id : $comment->id, 'parent_id' => $comment->id]" />
    </div>

    @foreach ($comment->replies as $replies)
        <x-livewire-comments::comment :key="$replies->uuid" :replies="true" :comment="$replies" />
    @endforeach
</div>
