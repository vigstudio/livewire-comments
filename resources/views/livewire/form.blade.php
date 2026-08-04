@php
    $id = $this->formKey;
    $canCompose = $this->auth || $this->allow_guest;
@endphp
<div
     wire:ignore.self
     x-data="LivewireComments.form('.textarea-vgcomment-box', @js($id))"
     @insert-content-{{ $id }}.window="afterUpload($event)"
     @post-success-comments.window="cleanData()"
     @open-form.window="open = ($event.detail?.open_id == {{ $request['parent_id'] ?: 0 }})"
>
    <form
        class="vg-composer"
        wire:submit.prevent
        data-recaptcha="{{ Config::get('vgcomment.recaptcha') ? '1' : '0' }}"
        data-recaptcha-key="{{ Config::get('vgcomment.recaptcha_key') }}"
        data-submit-method="{{ $method }}"
    >
        @include('livewire-comments::livewire.form.guest')

        <div class="vg-composer__body @error('content') vgcomments_alert_required @enderror">
            @if ($showingPreview)
                <div class="vg-composer__preview" wire:key="preview-{{ $formKey }}">
                    @if ($previewHtml !== '' && $previewHtml !== null)
                        {!! $previewHtml !!}
                    @else
                        <p class="text-xs text-gray-400">{{ __('vgcomment::comment.placeholder_textarea') }}</p>
                    @endif
                </div>
            @else
                <textarea
                    x-ref="composer"
                    @class([
                        'textarea-vgcomment-box',
                        'vg-composer__textarea',
                        'is-disabled' => ! $canCompose,
                        'validate-error' => $errors->has('content'),
                    ])
                    wire:model="request.content"
                    x-on:input="draft = $event.target.value"
                    x-on:paste="pasteClipboard($event)"
                    x-on:drop.prevent="dropFile($event)"
                    rows="4"
                    maxlength="{{ config('vgcomment.max_length') }}"
                    placeholder="{{ __('vgcomment::comment.placeholder_textarea') }}"
                    @disabled(! $canCompose)
                ></textarea>
            @endif

            @error('content')
                <span class="vgcomments_alert_required_text px-3 pb-2 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="vg-composer__attachments" x-show="files.length" x-cloak>
            <template x-for="(file, index) in files" :key="file.uuid || file.file_name || index">
                <div class="vg-file vg-file--chip" x-bind:class="(file.mime === 'image' || (file.mime_type || '').startsWith('image/')) ? 'vg-file--image' : 'vg-file--doc'">
                    <template x-if="file.mime === 'image' || (file.mime_type || '').startsWith('image/')">
                        <img class="vg-file__thumb" x-bind:src="file.url_stream" x-bind:alt="file.file_name || file.name || 'image'" loading="lazy">
                    </template>
                    <template x-if="!(file.mime === 'image' || (file.mime_type || '').startsWith('image/'))">
                        <span class="vg-file__icon" aria-hidden="true">📄</span>
                    </template>
                    <span class="vg-file__meta">
                        <span class="vg-file__name" x-text="getFileName(file.file_name || file.name || 'file')"></span>
                    </span>
                    <button type="button" class="vg-file__remove" title="Remove" aria-label="Remove attachment" x-on:click.prevent="removeAttachment(index)">×</button>
                </div>
            </template>
        </div>

        <div class="vg-composer__toolbar">
            <div class="vg-composer__tools">
                @if ($showingPreview)
                    <button @disabled(! $canCompose) wire:click="closePreview" type="button" class="vg-icon-btn" title="{{ __('vgcomment::comment.edit') }}">
                        <x-heroicons::icon name="eye-slash-o" class="vg-icon" />
                        <span class="vg-icon-btn__label">{{ __('vgcomment::comment.edit') }}</span>
                    </button>
                @else
                    <button @disabled(! $canCompose) x-on:click="showPreview()" type="button" class="vg-icon-btn" title="{{ __('vgcomment::comment.preview') }}">
                        <x-heroicons::icon name="eye-o" class="vg-icon" />
                        <span class="vg-icon-btn__label">{{ __('vgcomment::comment.preview') }}</span>
                    </button>

                    <div class="vg-composer__formats" role="toolbar" aria-label="Formatting">
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Bold" x-on:mousedown.prevent="wrapSelection('**', '**', 'bold')">
                            <span class="vg-fmt vg-fmt--bold">B</span>
                        </button>
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Italic" x-on:mousedown.prevent="wrapSelection('*', '*', 'italic')">
                            <span class="vg-fmt vg-fmt--italic">I</span>
                        </button>
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Strikethrough" x-on:mousedown.prevent="wrapSelection('~~', '~~', 'strike')">
                            <span class="vg-fmt vg-fmt--strike">S</span>
                        </button>
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Code" x-on:mousedown.prevent="wrapSelection('`', '`', 'code')">
                            <x-heroicons::icon name="code-bracket-o" class="vg-icon" />
                        </button>
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Quote" x-on:mousedown.prevent="wrapSelection('> ', '', 'quote')">
                            <x-heroicons::icon name="chat-bubble-bottom-center-text-o" class="vg-icon" />
                        </button>
                        <button @disabled(! $canCompose) type="button" class="vg-icon-btn" title="Link" x-on:mousedown.prevent="insertLink()">
                            <x-heroicons::icon name="link-o" class="vg-icon" />
                        </button>
                    </div>
                @endif

                <span class="vg-composer__sep" aria-hidden="true"></span>

                <div class="vg-composer__media">
                    <input x-ref="uploadFiles" x-on:change="uploadFile($event)" type="file" class="sr-only" multiple="true">
                    <input x-ref="uploadImages" x-on:change="uploadInlineImage($event)" type="file" class="sr-only" accept="image/*">
                    <button @disabled(! $canCompose) type="button" class="vg-icon-btn emoji-button" title="Emoji">
                        <x-heroicons::icon name="face-smile-o" class="vg-icon" />
                    </button>
                    <button @disabled(! $canCompose) type="button" class="vg-icon-btn" x-on:click="$refs.uploadImages.click()" title="Insert image">
                        <x-heroicons::icon name="photo-o" class="vg-icon" />
                    </button>
                    <button @disabled(! $canCompose) type="button" class="vg-icon-btn" x-on:click="$refs.uploadFiles.click()" title="Attach file">
                        <x-heroicons::icon name="paper-clip-s" class="vg-icon" />
                    </button>
                </div>

                <x-livewire-comments::loading wire:loading />
                <span class="text-xs text-gray-500" x-text="'Uploading… ' + progress + '%'" x-show="loading"></span>
            </div>

            <div class="inline-flex items-center gap-2">
                @if ($this->editId)
                    <button type="button" wire:click="cancel" class="vg-text-btn" wire:loading.attr="disabled">
                        {{ __('vgcomment::comment.cancel') }}
                    </button>
                @endif
                <button
                    type="button"
                    x-on:click="submit()"
                    @disabled(! $canCompose)
                    class="vg-btn vg-btn--primary"
                    wire:loading.attr="disabled"
                >
                    {{ __('vgcomment::comment.submit') }}
                </button>
            </div>
        </div>
    </form>
</div>
