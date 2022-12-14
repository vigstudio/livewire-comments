@props(['replies' => false, 'comment' => null])

<div id="vgcomment-{{ $comment->uuid }}"
     class="vcomment-row @if ($replies) children @endif"
     x-data="LivewireComments.comment('{{ $comment->uuid }}', $wire)"
     x-init="emojiPicker('.emoji-button')"
     x-bind:class="{ 'active': window.location.hash == '#vgcomment-{{ $comment->uuid }}' }">

    <div class="vgcomment__header">
        <div class="author">
            <div class="avatar">
                <img class="avatar__image"
                     src="{{ $comment->avatar_url }}"
                     alt="{{ $comment->author_name }}">
                {{-- <span class="avatar--badge">Admin</span> --}}
            </div>

            <div class="info">
                <p>
                    {{ $comment->author_name }}
                    @if ($comment->parent)
                        <span class="reply_text ">
                            {{ __('Reply to :') }} {{ $comment->parent->author_name }}
                        </span>
                    @endif
                </p>
                <p class="time">
                    <time pubdate
                          datetime="{{ $comment->created_at }}"
                          title="{{ $comment->time }}">
                        {{ $comment->time }}
                    </time>
                </p>

            </div>
        </div>

        <div x-data="LivewireComments.menu({ open: false })"
             x-init="init()"
             @keydown.escape.stop="open = false; focusButton()"
             @click.away="onClickAway($event)"
             class="dropdown">


            <div class="py-1 relative inline-block">

                <button class="vcomments__btn none text-xs"
                        type="button"
                        x-on:click="$wire.edit({{ $comment->id }}).then((value) => edit = value)">
                    <x-heroicons::icon name="pencil-square-o"
                                       class="w-4 mr-2" />
                    {{ __('vgcomment::comment.edit') }}
                </button>

                <button x-show="!remove_mode"
                        class="vcomments__btn none text-xs"
                        type="button"
                        x-on:click="remove_mode = true">
                    <x-heroicons::icon name="trash-o"
                                       class="w-4 mr-2" />
                    {{ __('vgcomment::comment.remove') }}
                </button>

                <div class="absolute right-0 w-96 origin-top-right vcomments__btn none text-xs"
                     x-show="remove_mode">
                    <p class="mr-2 w-100">Do you want to remove this comment?</p>
                    <button x-show="remove_mode"
                            class="vcomments__btn danger text-xs mr-2"
                            type="button"
                            x-on:click="$wire.delete({{ $comment->id }})">
                        OK!
                    </button>

                    <button x-show="remove_mode"
                            class="vcomments__btn secondary text-xs"
                            type="button"
                            x-on:click="remove_mode = false">
                        {{ __('vgcomment::comment.cancel') }}
                    </button>
                </div>



                {{-- <a
                            href="#"
                            class="dropdown-menu--item"
                            :class="{ 'active': activeIndex === 2 }"
                            @mouseenter="activeIndex = 2"
                            @mouseleave="activeIndex = -1"
                            @click="open = false; focusButton()"
                        >{{ __('vgcomment::comment.report') }}</a>
                    --}}
            </div>

        </div>

    </div>

    <div class="vgcomment__body">

        @if ($this->editId == $comment->uuid)
            <livewire:livewire-comments::form method="edit"
                                              :wire:key="'edit-'.$comment->id"
                                              :editId="$comment->id" />
        @else
            <div wire:ignore
                 x-data="LivewireComments.content()">{!! $comment->content_html !!}</div>
        @endif

    </div>

    <div class="vgcomment__attachments">
        @foreach ($comment->files as $file)
            <a href="{{ $file->url_stream }}"
               target="_blank"
               class="file">
                <x-heroicons::icon name="paper-clip-s"
                                   class="w-4 h-4" />
                <span class="w-full">
                    {{ $file->file_name }} - ({{ number_format($file->size / 1000, 2) }} KB)
                </span>

            </a>
        @endforeach
    </div>

    <div class="vgcomment__footer">
        <div class="vgcomment__reactions">
            <div class="reactions__group">

                <button type="button"
                        class="emoji-button  vcomments__btn primary-tone">
                    <x-heroicons::icon name="face-smile-o"
                                       class="w-3 h-3" />
                </button>

                @foreach ($comment->reactionsGroup() as $reaction)
                    <button type="button"
                            class="vcomments__btn @if ($reaction->where('user_reacted', true)->count() > 0) active @else none @endif">
                        <span class="emoji">{{ $reaction->first()->type }}</span>
                        <span class="text">{{ $reaction->count() }}</span>
                    </button>
                @endforeach


            </div>
        </div>

        <button class="vcomments__btn none comment-reply"
                type="button">
            <x-heroicons::icon x-show="reply"
                               name="chevron-up-o"
                               class="mr-2" />

            <x-heroicons::icon x-show="!reply"
                               name="chat-bubble-left-right-o"
                               class="mr-2" />
            {{ __('vgcomment::comment.reply') }}
        </button>
    </div>

    <div class="vgcomment__replybox"
         x-show="reply">
        <livewire:livewire-comments::form method="submit"
                                          :wire:key="'form-'.$comment->id"
                                          :commentable="$comment->commentable"
                                          :pageId="$comment->page_id"
                                          :request="[
                                              'root_id' => $comment->root_id ? $comment->root_id : $comment->id,
                                              'parent_id' => $comment->id,
                                          ]" />
    </div>

</div>
