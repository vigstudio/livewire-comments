<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class CommentsComponent extends Component
{
    use Actions\Alert;
    use WithPagination;

    public $pageId = null;

    public $commentable;

    public $editId;

    public $request = [
        'root_id' => null,
        'page_id' => null,
        'commentable_type' => null,
        'commentable_id' => null,
        'order' => 'latest',
    ];

    public $loading = true;

    protected $queryString = [
    ];

    protected function getListeners()
    {
        $commentable_id = ! empty($this->commentable) ? $this->commentable['id'] : null;
        $commentable_type = ! empty($this->commentable) ? get_class($this->commentable) : null;

        $hash = vgcomment_page_hash($this->pageId, $commentable_id, $commentable_type);

        return [
            'post-success-comments' => 'listenCommentPosted',
            'cancel-edit' => 'listenCancelEdit',
            'confirm-submit' => 'confirmAction',
            "echo:vgcomment_{$hash},.BroadcastCommentCreatedEvent" => 'listenEchoCommentPosted',
        ];
    }

    public function deferLoading()
    {
        $this->loading = false;
    }

    public function updatingRequest()
    {
        $this->resetPage('vgcomment_page');
    }

    public function mount()
    {
        $this->request['page_id'] = $this->pageId;

        $this->request['commentable_id'] = ! empty($this->commentable) ? $this->commentable['id'] : null;
        $this->request['commentable_type'] = ! empty($this->commentable) ? get_class($this->commentable) : null;
    }

    public function getComments()
    {
        if ($this->loading) {
            return new Paginator(Paginator::resolveCurrentPage(), 0, 10);
        }

        return CommentServiceFacade::get($this->request);
    }

    public function render()
    {
        $comments = $this->getComments();

        return view('livewire-comments::livewire.comments', compact('comments'));
    }

    public function checkPermission($id, $action): bool
    {
        $comment = CommentServiceFacade::findById($id);

        if (! $comment || ! vgcomment_policy($comment->id, $action)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        return true;
    }

    public function react(string $uuid, string $type)
    {
        CommentServiceFacade::reaction($uuid, $type);
    }

    public function edit($id)
    {
        $comment = CommentServiceFacade::findById($id);

        $this->checkPermission($id, 'update');

        $this->editId = $comment->uuid;

        return true;
    }

    public function confirmAction($id, $action)
    {
        if ($action == 'delete') {
            $this->delete($id, 'delete');
        }

        if ($action == 'report') {
            $this->report($id, 'report');
        }
    }

    public function report(string $id, $action = 'alert')
    {
        $this->checkPermission($id, 'report');

        if ($action == 'alert') {
            $this->dispatchBrowserEvent('confirm-action', ['id' => $id, 'message' => trans('vgcomment::comment.report_confirm'), 'action' => 'report']);
        }

        if ($action == 'report') {
            $comment = CommentServiceFacade::findById($id);
            CommentServiceFacade::report($comment->uuid);
        }
    }

    public function delete($id, $action = 'alert')
    {
        $this->checkPermission($id, 'delete');

        if ($action == 'alert') {
            $this->dispatchBrowserEvent('confirm-action', ['id' => $id, 'message' => trans('vgcomment::comment.delete_confirm'), 'action' => 'delete']);
        }

        if ($action == 'delete') {
            $comment = CommentServiceFacade::findById($id);
            CommentServiceFacade::delete($comment->uuid);
        }
    }

    public function listenCommentPosted($result)
    {
        if (! $result) {
            return;
        }

        $this->reset('editId');
    }

    public function listenCancelEdit()
    {
        $this->reset('editId');
    }

    public function paginationView()
    {
        return 'livewire-comments::pagination';
    }

    public function listenEchoCommentPosted($event)
    {
        $comment = CommentServiceFacade::findById($event['comment']['id']);
    }
}
