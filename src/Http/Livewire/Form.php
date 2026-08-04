<?php

namespace Vigstudio\LivewireComments\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Http\Traits\CommentValidator;

class Form extends Component
{
    use Actions\Alert;
    use CommentValidator;
    use WithFileUploads;

    public string $method = 'submit';

    public array $request = [
        'content' => '',
        'author_name' => null,
        'author_email' => null,
        'author_url' => null,
        'root_id' => null,
        'parent_id' => null,
        'recaptcha_token' => null,
    ];

    public $clipboard = [];

    public array $attachments = [];

    #[Locked]
    public $commentable = null;

    #[Locked]
    public $pageId = null;

    public $previewHtml = '';

    public bool $showingPreview = false;

    public $editId;

    public string $formKey = '';

    public function mount(): void
    {
        $this->formKey = $this->formKey !== '' ? $this->formKey : (string) Str::uuid();

        // Nested reply forms pass only root_id/parent_id — merge so content/author keys stay intact.
        $this->request = array_merge([
            'content' => '',
            'author_name' => null,
            'author_email' => null,
            'author_url' => null,
            'root_id' => null,
            'parent_id' => null,
            'recaptcha_token' => null,
        ], $this->request ?? []);

        if ($this->editId) {
            $this->method = 'edit';
            $this->request['content'] = CommentServiceFacade::findById((int) $this->editId)?->content;
        }

        if (session()->has('author')) {
            $this->request['author_name'] = session('author.name');
            $this->request['author_email'] = session('author.email');
            $this->request['author_url'] = session('author.url');
        }
    }

    public function getAuthProperty()
    {
        return CommentServiceFacade::getAuth();
    }

    public function getAllowGuestProperty()
    {
        return (bool) vgcomment_config()['allow_guests'];
    }

    public function render()
    {
        return view('livewire-comments::livewire.form');
    }

    public function submit(): void
    {
        $payload = $this->payload();

        $this->storeCommentValidator(new Request($payload))->validate();

        $result = CommentServiceFacade::store($payload);

        if (! $result) {
            return;
        }

        CommentServiceFacade::registerFilesForComment($result, $this->attachments);

        $this->request['content'] = '';
        $this->request['recaptcha_token'] = '';
        $this->showingPreview = false;
        $this->reset('previewHtml', 'attachments');

        $this->dispatch('post-success-comments');
    }

    public function edit(): void
    {
        $this->updateCommentValidator(new Request($this->request))->validate();

        $comment = CommentServiceFacade::findById((int) $this->editId);

        if (! $comment || ! vgcomment_policy($comment->id, 'update')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return;
        }

        CommentServiceFacade::update($this->request, $comment->uuid);

        $this->request['content'] = '';
        $this->showingPreview = false;
        $this->reset('previewHtml', 'editId');

        $this->dispatch('post-success-comments');
    }

    public function uploadFile(string $id)
    {
        $files = CommentServiceFacade::upload($this->clipboard);

        $this->reset('clipboard');

        if (! $files) {
            $this->dispatch('insert-content-'.$id, 'false');

            return false;
        }

        // CommentService::upload returns a FileResource collection — resolve to
        // plain arrays for the Alpine afterUpload handler / attachments binding.
        $payload = array_values($files->resolve());

        // Single event with the full batch so the Alpine form can keep
        // insert-vs-attach mode consistent across every uploaded file.
        $this->dispatch('insert-content-'.$id, ['files' => $payload]);

        return $payload;
    }

    public function preview(?string $content = null): void
    {
        if ($content !== null) {
            $this->request['content'] = $content;
        }

        $parsed = FormatterFacade::parse((string) ($this->request['content'] ?? ''));
        $this->previewHtml = FormatterFacade::render($parsed) ?: '';
        $this->showingPreview = true;
    }

    public function closePreview(): void
    {
        $this->showingPreview = false;
    }

    public function cancel(): void
    {
        $this->reset('editId');
        $this->dispatch('cancel-edit');
    }

    public function getErrors(): void
    {
        foreach ($this->getErrorBag()->getMessages() as $error) {
            session()->push('alert', ['error', Str::replace('clipboard.', 'Files Array ', $error)]);
        }
    }

    protected function payload(): array
    {
        return array_merge($this->request, [
            'page_id' => $this->pageId,
            'commentable_id' => ! empty($this->commentable) ? $this->commentable->getKey() : null,
            'commentable_type' => ! empty($this->commentable) ? get_class($this->commentable) : null,
        ]);
    }

    protected function parse()
    {
        return FormatterFacade::parse($this->request['content'] ?? '');
    }
}
