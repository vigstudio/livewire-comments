<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Vigstudio\VgComment\Facades\CommentServiceFacade;

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
    ];

    protected $queryString = [];

    protected function getListeners()
    {
        return [
            'post-success-comments' => 'listenCommentPosted',
            'cancel-edit' => 'listenCancelEdit',
        ];
    }

    public function getAuthProperty()
    {
        return CommentServiceFacade::getAuth();
    }

    public function mount()
    {
        $this->request['page_id'] = $this->pageId;

        $this->request['commentable_id'] = ! empty($this->commentable) ? $this->commentable['id'] : null;
        $this->request['commentable_type'] = ! empty($this->commentable) ? get_class($this->commentable) : null;
    }

    public function getCommentsProperty()
    {
        return CommentServiceFacade::get($this->request);
    }

    public function render()
    {
        return view('livewire-comments::livewire.comments');
    }

    public function react(string $uuid, string $type)
    {
        CommentServiceFacade::reaction($uuid, $type);
    }

    public function edit($id)
    {
        $comment = CommentServiceFacade::findById($id);

        if (! $comment || ! vgcomment_policy($comment->id, 'update')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $this->editId = $comment->uuid;

        return true;
    }

    public function delete($id)
    {
        $comment = CommentServiceFacade::findById($id);

        CommentServiceFacade::delete($comment->uuid);
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
}
