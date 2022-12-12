<div>
    @foreach($replies as $comment)
        <x-livewire-comments::comment :key="$comment->id" :replies="true" :comment="$comment"/>
    @endforeach
    <div>
        {{ $replies->links() }}
    </div>
</div>
