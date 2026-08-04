<div class="vg-box" wire:init="deferLoading">
    @if ($loading)
        <div class="vg-empty">Loading comments…</div>
    @else
        <div wire:ignore>
            <x-livewire-comments::modal />
            <x-livewire-comments::alert />
            <x-livewire-comments::confirm />
        </div>

        <livewire:livewire-comments::form method="submit" :pageId="$pageId" :commentable="$commentable" />

        <div class="vg-discussion-bar">
            <h3 class="vg-discussion-bar__title">
                {{ __('vgcomment::comment.discussion') }} ({{ $comments->total() }})
            </h3>
            <label class="vg-discussion-bar__sort">
                <span>{{ __('vgcomment::comment.latest') }}</span>
                <select wire:model.live="request.order">
                    <option value="latest">{{ __('vgcomment::comment.latest') }}</option>
                    <option value="oldest">{{ __('vgcomment::comment.oldest') }}</option>
                    <option value="popular">{{ __('vgcomment::comment.popular') }}</option>
                </select>
            </label>
        </div>

        <div class="vg-list">
            @forelse ($comments as $comment)
                <x-livewire-comments::comment :comment="$comment" />
            @empty
                <p class="vg-empty">No comments yet. Be the first to start the discussion.</p>
            @endforelse
        </div>

        <div class="vg-pagination">
            {{ $comments->links() }}
        </div>
    @endif
</div>
