<div class="vcomments-box">

    <div class="flex justify-between items-center mb-6 border-b-2 border-gray-400 px-4">
        <h2 class="text-lg lg:text-2xl font-bold text-gray-900">
            {{ __('vgcomment::comment.discussion') }} ({{ $this->comments->total() }})
        </h2>

        <div class="flex justify-between items-center mb-2">
            <div class="flex justify-between">
                <select wire:model="request.order" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option value="latest">{{ __('vgcomment::comment.latest') }}</option>
                    <option value="oldest">{{ __('vgcomment::comment.oldest') }}</option>
                    <option value="popular">{{ __('vgcomment::comment.popular') }}</option>
                </select>
            </div>
        </div>
    </div>

    <livewire:livewire-comments::form method="submit" :pageId="$pageId" :commentable="$commentable" />

    @foreach ($this->comments as $comment)
        <x-livewire-comments::comment :comment="$comment" />

        @foreach ($comment->replies as $replies)
            <x-livewire-comments::comment :key="$replies->uuid" :replies="true" :comment="$replies" />
        @endforeach
    @endforeach

    <div>
        {{ $this->comments->links() }}
    </div>


    <x-livewire-comments::modal />
    <x-livewire-comments::alert />
    <x-livewire-comments::confirm />


    <script src="https://www.google.com/recaptcha/api.js?render={{ Config::get('vgcomment.recaptcha_key') }}"></script>
</div>
