<div class="vgcomment_box" wire:init.defer.500ms="deferLoading('comments')">

    @if ($loading)
    @else
        <div wire:ignore>
            <x-livewire-comments::modal />
            <x-livewire-comments::alert />
            <x-livewire-comments::confirm />
        </div>

        <div class="vgcomment_box_navbar">
            <h2 class="vgcomment_box_navbar-title">
                {{ __('vgcomment::comment.discussion') }} ({{ $comments->total() }})
            </h2>

            <div class="vgcomment_box_navbar-dropdown">
                <div class="vgcomment_box_navbar_dropdown-group">
                    <select wire:model.live="request.order" class="vgcomment_box_navbar_dropdown-select">
                        <option value="latest">{{ __('vgcomment::comment.latest') }}</option>
                        <option value="oldest">{{ __('vgcomment::comment.oldest') }}</option>
                        <option value="popular">{{ __('vgcomment::comment.popular') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <livewire:livewire-comments::form method="submit" :pageId="$pageId" :commentable="$commentable" />

        @foreach ($comments as $comment)
            <x-livewire-comments::comment :comment="$comment" />
        @endforeach

        <div>
            {{ $comments->links() }}
        </div>

    @endif
</div>
