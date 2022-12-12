@props(['replies' => false, 'comment'])

<div
    id="vgcomment-{{ $comment->uuid }}"
    class="vcomment-row @if ($replies) children @endif"
    x-data="LivewireComments.comment('{{ $comment->uuid }}', $wire)"
    x-init="emojiPicker('.emoji-button')"
>

    <div class="vgcomment__header">
        <div class="author">
            <div class="avatar">
                <img
                    class="avatar__image"
                    src="{{ $comment->avatar_url }}"
                    alt="{{ $comment->author_name }}"
                >
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
                    <time
                        pubdate
                        datetime="{{ $comment->created_at }}"
                        title="{{ $comment->time }}"
                    >
                        {{ $comment->time }}
                    </time>
                </p>

            </div>
        </div>

    </div>

    <div class="vgcomment__body">
        <livewire:livewire-comments::form
            method="edit"
            :wire:key="'edit-'.$comment->id"
            :editId="$comment->id"
        />
    </div>

    <div class="vgcomment__footer">
        <div class="vgcomment__reactions">
            <div class="reactions__group">

                <button
                    type="button"
                    class="emoji-button  vcomments__btn primary-tone"
                >
                    <x-heroicons::icon
                        name="face-smile-o"
                        class="w-3 h-3"
                    />
                </button>

                @foreach ($comment->reactionsGroup() as $reaction)
                    <button
                        type="button"
                        class="vcomments__btn @if ($reaction->where('user_reacted', true)->count() > 0) active @else none @endif"
                    >
                        <span class="emoji">{{ $reaction->first()->type }}</span>
                        <span class="text">{{ $reaction->count() }}</span>
                    </button>
                @endforeach


            </div>
        </div>

        <button
            class="vcomments__btn none comment-reply"
            type="button"
        >
            <x-heroicons::icon
                x-show="reply"
                name="chevron-up-o"
                class="mr-2"
            />

            <x-heroicons::icon
                x-show="!reply"
                name="chat-bubble-left-right-o"
                class="mr-2"
            />
            {{ __('vgcomment::comment.reply') }}
        </button>
    </div>

    @foreach ($comment->replies as $replies)
        <x-livewire-comments::comment
            :key="$replies->uuid"
            :replies="true"
            :comment="$replies"
        />
    @endforeach

</div>
