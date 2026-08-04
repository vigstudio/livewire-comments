<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Vigstudio\VgComment\Facades\CommentServiceFacade;

class Comments extends Component
{
    use Actions\Alert;
    use WithPagination;

    #[Locked]
    public $pageId = null;

    #[Locked]
    public $commentable = null;

    public $editId;

    public array $request = [
        'order' => 'latest',
    ];

    public bool $loading = false;

    protected function getListeners(): array
    {
        $commentableId = ! empty($this->commentable) ? $this->commentable->getKey() : null;
        $commentableType = ! empty($this->commentable) ? get_class($this->commentable) : null;
        $hash = vgcomment_page_hash($this->pageId, $commentableId, $commentableType);

        return [
            'post-success-comments' => 'listenCommentPosted',
            'cancel-edit' => 'listenCancelEdit',
            'confirm-submit' => 'confirmAction',
            "echo:vgcomment_{$hash},.BroadcastCommentCreatedEvent" => 'listenEchoCommentPosted',
        ];
    }

    public function deferLoading(): void
    {
        $this->loading = false;
    }

    public function updatingRequest(): void
    {
        $this->resetPage('vgcomment_page');
    }

    public function getComments()
    {
        if ($this->loading) {
            return [];
        }

        return CommentServiceFacade::get($this->queryPayload(), false);
    }

    public function render()
    {
        return view('livewire-comments::livewire.comments', [
            'comments' => $this->getComments(),
        ]);
    }

    public function checkPermission($id, string $action): bool
    {
        $comment = CommentServiceFacade::findById((int) $id);

        if (! $comment || ! vgcomment_policy($comment->id, $action)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        return true;
    }

    public function react(string $uuid, string $type): void
    {
        CommentServiceFacade::reaction($uuid, $type);
    }

    public function unReact(string $uuid, string $type): void
    {
        CommentServiceFacade::deleteReaction($uuid, $type);
    }

    public function toggleReact(string $uuid, string $type): void
    {
        $comment = CommentServiceFacade::findByUuid($uuid);

        if (! $comment) {
            return;
        }

        $userReacted = $comment->reactions()
            ->where('type', $type)
            ->get()
            ->contains(fn ($reaction) => (bool) $reaction->user_reacted);

        if ($userReacted) {
            CommentServiceFacade::deleteReaction($uuid, $type);

            return;
        }

        CommentServiceFacade::reaction($uuid, $type);
    }

    public function edit($id): bool
    {
        if (! $this->checkPermission($id, 'update')) {
            return false;
        }

        $comment = CommentServiceFacade::findById((int) $id);
        $this->editId = $comment->uuid;

        return true;
    }

    public function confirmAction($id, $action): void
    {
        if ($action === 'delete') {
            $this->delete($id, 'delete');
        }

        if ($action === 'report') {
            $this->report($id, 'report');
        }
    }

    public function report(string $id, string $action = 'alert'): void
    {
        if (! $this->checkPermission($id, 'report')) {
            return;
        }

        if ($action === 'alert') {
            $this->dispatch('confirm-action', [
                'id' => $id,
                'message' => trans('vgcomment::comment.report_confirm'),
                'action' => 'report',
            ]);

            return;
        }

        if ($action === 'report') {
            $comment = CommentServiceFacade::findById((int) $id);
            CommentServiceFacade::report($comment->uuid);
        }
    }

    public function delete($id, string $action = 'alert'): void
    {
        if (! $this->checkPermission($id, 'delete')) {
            return;
        }

        if ($action === 'alert') {
            $this->dispatch('confirm-action', [
                'id' => $id,
                'message' => trans('vgcomment::comment.delete_confirm'),
                'action' => 'delete',
            ]);

            return;
        }

        if ($action === 'delete') {
            $comment = CommentServiceFacade::findById((int) $id);
            CommentServiceFacade::delete($comment->uuid);
        }
    }

    public function listenCommentPosted($result = null): void
    {
        $this->reset('editId');
        $this->resetPage('vgcomment_page');
    }

    public function listenCancelEdit(): void
    {
        $this->reset('editId');
    }

    public function paginationView(): string
    {
        return 'livewire-comments::pagination';
    }

    public function listenEchoCommentPosted($event): void
    {
        // Broadcast refresh hook — list re-renders on next Livewire request.
    }

    protected function queryPayload(): array
    {
        return [
            'order' => $this->request['order'] ?? 'latest',
            'page_id' => $this->pageId,
            'commentable_id' => ! empty($this->commentable) ? $this->commentable->getKey() : null,
            'commentable_type' => ! empty($this->commentable) ? get_class($this->commentable) : null,
        ];
    }
}
