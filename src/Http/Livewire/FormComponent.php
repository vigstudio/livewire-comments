<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Livewire\Component;
use Livewire\WithFileUploads;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Illuminate\Support\Str;

class FormComponent extends Component
{
    use Actions\Alert;
    use WithFileUploads;
    use CommentValidator;

    protected $config;

    public $method = 'submit';

    public $request = [
        'content' => '',
        'page_id' => null,
        'commentable_type' => null,
        'commentable_id' => null,
        'author_name' => null,
        'author_email' => null,
        'author_url' => null,
        'root_id' => null,
        'parent_id' => null,
        'recaptcha_token' => null,
    ];

    //Clipboard File Data
    public $clipboard = [];

    public $attachments = [];

    public $commentable;

    public $pageId = null;

    public $preview;

    public $editId;

    public function mount()
    {
        if ($this->editId) {
            $this->method = 'edit';
            $this->request['content'] = CommentServiceFacade::findById($this->editId)?->content;
        }

        if (session()->has('author')) {
            $this->request['author_name'] = session('author.name');
            $this->request['author_email'] = session('author.email');
            $this->request['author_url'] = session('author.url');
        }

        $this->request['page_id'] = $this->pageId;
        $this->request['commentable_id'] = ! empty($this->commentable) ? $this->commentable->id : null;
        $this->request['commentable_type'] = ! empty($this->commentable) ? get_class($this->commentable) : null;
    }

    public function getAuthProperty()
    {
        return CommentServiceFacade::getAuth();
    }

    public function getAllowGuestProperty()
    {
        $allowGuest = Config::get('vgcomment.allow_guests');

        return $allowGuest;
    }

    public function render()
    {
        return view('livewire-comments::livewire.form');
    }

    public function submit()
    {
        $this->config = Config::get('vgcomment');

        $this->storeCommentValidator(new Request($this->request))->validate();

        $result = CommentServiceFacade::store($this->request);

        if ($result) {
            CommentServiceFacade::registerFilesForComment($result, $this->attachments);
            $this->request['content'] = '';
            $this->request['recaptcha_token'] = '';

            $this->reset('preview');

            $this->emit('post-success-comments', $result);

            $this->dispatchBrowserEvent('post-success-comments');
        }
    }

    public function edit()
    {
        $this->config = Config::get('vgcomment');

        $this->updateCommentValidator(new Request($this->request))->validate();

        try {
            $comment = CommentServiceFacade::findById($this->editId);

            $result = CommentServiceFacade::update($this->request, $comment->uuid);

            $this->request['content'] = '';

            $this->reset('preview');

            $this->reset('editId');

            $this->emit('post-success-comments', $comment);

            $this->dispatchBrowserEvent('post-success-comments');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function uploadFile()
    {
        $files = CommentServiceFacade::upload($this->clipboard);

        $this->reset('clipboard');

        if (! $files) {
            $this->dispatchBrowserEvent('insert-content', 'false');

            return false;
        }

        foreach ($files as $file) {
            $this->dispatchBrowserEvent('insert-content', $file);
        }

        return $files;
    }

    public function preview()
    {
        $render = FormatterFacade::render($this->parse());
        $this->preview = $render;
    }

    public function cancel()
    {
        $this->reset('editId');
        $this->emit('cancel-edit');
    }

    public function getErrors()
    {
        $errors = $this->getErrorBag()->getMessages();

        foreach ($errors as $key => $error) {
            session()->push('alert', ['error', Str::replace('clipboard.', 'Files Array ', $error)]);
        }
    }

    protected function parse()
    {
        return FormatterFacade::parse($this->request['content'] ?? '');
    }
}
