<div class="vcomments-box">

    <x-livewire-comments::modal />
    <x-livewire-comments::alert />

    <livewire:livewire-comments::form
                                      method="submit"
                                      :pageId="$pageId"
                                      :commentable="$commentable" />

    @foreach ($this->comments as $comment)
        <x-livewire-comments::comment :comment="$comment" />

        @foreach ($comment->replies as $replies)
            <x-livewire-comments::comment
                                          :key="$replies->uuid"
                                          :replies="true"
                                          :comment="$replies" />
        @endforeach
    @endforeach

    <div>
        {{ $this->comments->links() }}
    </div>
    <script src="https://www.google.com/recaptcha/api.js?render={{ Config::get('vgcomment.recaptcha_key') }}"></script>
</div>
