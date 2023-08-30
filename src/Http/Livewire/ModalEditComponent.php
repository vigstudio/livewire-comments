<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Livewire\Component;
use Livewire\WithFileUploads;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Services\CommentService;

class ModalEditComponent extends Component
{
    use Actions\Alert;
    use WithFileUploads;
    use CommentValidator;

    protected $config;

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
    ];

    //Clipboard File Data
    public $clipboard = [];

    public $attachment = [];

    public $commentable;

    public $pageId = null;

    public $preview;

    public function mount()
    {
        $this->request['page_id'] = $this->pageId;
        $this->request['commentable_id'] = ! empty($this->commentable) ? $this->commentable->id : null;
        $this->request['commentable_type'] = ! empty($this->commentable) ? get_class($this->commentable) : null;
    }

    public function render()
    {
        return view('livewire-comments::livewire.form');
    }

    public function submit(CommentService $commentService)
    {
        $this->config = Config::get('vgcomment');

        $this->storeCommentValidator(new Request($this->request))->validate();

        $result = $commentService->store($this->request);

        $this->request['content'] = '';

        $this->reset('preview');
        $this->dispatch('post-success-comments');
        // $this->dispatchBrowserEvent('post-success-comments');
    }

    public function uploadFile(CommentService $commentService)
    {
        $files = $commentService->upload($this->clipboard, $this->request['uuid']);

        $this->reset('clipboard');

        if ($files) {
            foreach ($files as $file) {
                $this->dispatch('insert-content', $file);
            }
        } else {
            $this->dispatch('insert-content', 'false');

            return false;
        }
    }

    public function preview()
    {
        $render = FormatterFacade::render($this->parse());
        $this->preview = $render;
    }

    protected function parse()
    {
        return FormatterFacade::parse($this->request['content'] ?? '');
    }
}
