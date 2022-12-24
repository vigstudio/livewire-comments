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
                     src="{{ $comment->author_avatar }}"
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

            <div>
                <button
                        type="button"
                        class="flex items-center rounded-full vcomments__btn none"
                        id="menu-button"
                        x-ref="button"
                        @click="onButtonClick()"
                        @keyup.space.prevent="onButtonEnter()"
                        @keydown.enter.prevent="onButtonEnter()"
                        aria-expanded="true"
                        aria-haspopup="true"
                        x-bind:aria-expanded="open.toString()"
                        @keydown.arrow-up.prevent="onArrowUp()"
                        @keydown.arrow-down.prevent="onArrowDown()">

                    <x-heroicons::icon name="ellipsis-vertical-s" class="w-4 h-4" />
                </button>
            </div>


            <div x-show="open"
                 x-transition:enter="enter"
                 x-transition:enter-start="enter-start"
                 x-transition:enter-end="enter-end"
                 x-transition:leave="leave"
                 x-transition:leave-start="leave-start"
                 x-transition:leave-end="leave-end"
                 class="dropdown-menu"
                 x-ref="menu-items"
                 x-description="Dropdown menu, show/hide based on menu state."
                 x-bind:aria-activedescendant="activeDescendant"
                 role="menu"
                 aria-orientation="vertical"
                 aria-labelledby="menu-button"
                 tabindex="-1"
                 @keydown.arrow-up.prevent="onArrowUp()"
                 @keydown.arrow-down.prevent="onArrowDown()"
                 @keydown.tab="open = false"
                 @keydown.enter.prevent="open = false; focusButton()"
                 @keyup.space.prevent="open = false; focusButton()">

                <div class="py-1" role="none">
                    <a href="javascript:void(0);" class="dropdown-menu--item"
                       x-state:on="Active"
                       x-state:off="Not Active"
                       :class="{ 'bg-gray-100 text-gray-900': activeIndex === 0, 'text-gray-700': !(activeIndex === 0) }"
                       role="menuitem"
                       tabindex="-1"
                       id="menu-item-0"
                       @mouseenter="onMouseEnter($event)"
                       @mousemove="onMouseMove($event, 0)"
                       @mouseleave="onMouseLeave($event)"
                       x-on:click="$wire.edit({{ $comment->id }}).then((value) => edit = value); open = false; focusButton()">

                        {{ __('vgcomment::comment.edit') }}
                    </a>

                    <a href="javascript:void(0);"
                       class="dropdown-menu--item"
                       :class="{ 'bg-gray-100 text-gray-900': activeIndex === 1, 'text-gray-700': !(activeIndex === 1) }"
                       role="menuitem"
                       tabindex="-1"
                       id="menu-item-1"
                       @mouseenter="onMouseEnter($event)"
                       @mousemove="onMouseMove($event, 1)"
                       @mouseleave="onMouseLeave($event)"
                       x-on:click="$wire.delete({{ $comment->id }}, 'alert').then((value) => edit = value); open = false; focusButton()">
                        {{ __('vgcomment::comment.delete') }}
                    </a>

                    <a href="javascript:void(0);"
                       class="dropdown-menu--item"
                       :class="{ 'bg-gray-100 text-gray-900': activeIndex === 2, 'text-gray-700': !(activeIndex === 2) }"
                       role="menuitem"
                       tabindex="-1"
                       id="menu-item-2"
                       @mouseenter="onMouseEnter($event)"
                       @mousemove="onMouseMove($event, 2)"
                       @mouseleave="onMouseLeave($event)"
                       x-on:click="$wire.report({{ $comment->id }}, 'alert').then((value) => edit = value); open = false; focusButton()">
                        {{ __('vgcomment::comment.report') }}
                    </a>
                </div>
            </div>

        </div>

    </div>

    <div class="vgcomment__body">

        @if ($this->editId == $comment->uuid)
            <livewire:livewire-comments::form method="edit" :wire:key="'edit-'.$comment->id" :editId="$comment->id" />
        @else
            <div wire:ignore x-data="LivewireComments.content()">{!! $comment->content_html !!}</div>
        @endif

    </div>

    <div class="vgcomment__attachments">
        @foreach ($comment->files as $file)
            <a href="{{ $file->url_stream }}" target="_blank" class="file">
                <x-heroicons::icon name="paper-clip-s" class="w-4 h-4" />
                <span class="w-full">{{ $file->file_name }} - ({{ number_format($file->size / 1000, 2) }} KB)</span>
            </a>
        @endforeach
    </div>

    <div class="vgcomment__footer">
        <div class="vgcomment__reactions">
            <div class="reactions__group">

                <button type="button" class="emoji-button  vcomments__btn primary-tone">
                    <x-heroicons::icon name="face-smile-o" class="w-3 h-3" />
                </button>

                @foreach ($comment->reactionsGroup() as $reaction)
                    <button type="button" class="vcomments__btn @if ($reaction->where('user_reacted', true)->count() > 0) active @else none @endif">
                        <span class="emoji">{{ $reaction->first()->type }}</span>
                        <span class="text">{{ $reaction->count() }}</span>
                    </button>
                @endforeach

            </div>
        </div>

        <button class="vcomments__btn none comment-reply" type="button">
            <x-heroicons::icon x-show="reply" name="chevron-up-o" class="mr-2" />
            <x-heroicons::icon x-show="!reply" name="chat-bubble-left-right-o" class="mr-2" />
            {{ __('vgcomment::comment.reply') }}
        </button>
    </div>

    <div class="vgcomment__replybox" x-show="reply">

        <livewire:livewire-comments::form method="submit"
                                          :wire:key="'form-'.$comment->id"
                                          :commentable="$comment->commentable" :
                                          pageId="$comment->page_id"
                                          :request="['root_id' => $comment->root_id ? $comment->root_id : $comment->id, 'parent_id' => $comment->id]" />
    </div>

</div>
