<fieldset disabled
          wire:ignore.self
          x-data="LivewireComments.form('.textarea-vgcomment-box', $wire)"
          x-init="emojiPicker('.emoji-button')"
          @insert-content.window="afterUpload($event)"
          @post-success-comments.window="cleanData()"
          @open-form.window="$event.detail.open_id == {{ $request['parent_id'] ?: 0 }} ? open = true : open = false">
    <form wire:submit.prevent="{{ $method }}">

        <div class="vcomments__form">
            <div class="vcomments__form__header">

                <div class="vcomments__header__tooltips-group">

                    <button x-show="tab == 2"
                            x-on:click="tab = 1"
                            type="button"
                            class="vcomments__btn secondary">
                        <x-heroicons::icon name="eye-slash-o"
                                           class="h-5 w-5" />
                        <span class="pl-2 hidden sm:flex"></span>
                    </button>

                    <button x-show="tab == 1"
                            x-on:click="tab = 2"
                            type="button"
                            class="vcomments__btn secondary"
                            wire:click="preview">
                        <x-heroicons::icon name="eye-o"
                                           class="h-5 w-5" />
                        <span class="pl-2 hidden sm:flex">{{ __('vgcomment::comment.preview') }}</span>
                    </button>

                </div>

                <div>
                    <x-livewire-comments::loading wire:loading />
                    <span x-text="'Uploading....' + progress + '%'"
                          x-show="loading"></span>
                </div>

                <div class="vcomments__header__tooltips-group">

                    <div class="vcomments__header__group">

                        <button type="button"
                                class="emoji-button vcomments__btn secondary"
                                data-popover-target="emoji">
                            <x-heroicons::icon name="face-smile-s"
                                               class="w-4 h-4" />
                            <span class="sr-only">Emoji</span>

                        </button>

                        <input x-ref="uploadFiles"
                               x-on:change="uploadFile($event)"
                               type="file"
                               class="sr-only"
                               multiple="true">

                        <button type="button"
                                class="vcomments__btn secondary"
                                x-on:click="$refs.uploadFiles.click()">
                            <x-heroicons::icon name="paper-clip-s"
                                               class="w-4 h-4" />
                            <span class="sr-only">Upload</span>
                        </button>
                    </div>

                </div>

            </div>

            <div class="vcomments__form__body @error('content') border border-red-600 @enderror">


                <x-livewire-comments::forms.preview x-show="tab == 2" :preview="$preview" />

                <x-livewire-comments::forms.textarea @class([
                    'textarea-vgcomment-box',
                    'disabled' => !$this->auth,
                    'validate-error' => $errors->has('content'),
                ])
                                                     wire:model.defer="request.content"
                                                     x-show="tab == 1"
                                                     x-on:paste="pasteClipboard($event)"
                                                     x-on:drop.prevent="dropFile($event)"
                                                     rows="4"
                                                     maxlength="{{ config('vgcomment.max_length') }}"
                                                     placeholder="{{ __('vgcomment::comment.placeholder_textarea') }}" />
                @error('content')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="form__attachments">
                <template x-for="file in files">

                    <a x-show="!file.mime_type.includes('image')"
                       x-bind:href="file.url_stream"
                       target="_blank"
                       class="file">
                        <x-heroicons::icon name="paper-clip-s"
                                           class="w-4 h-4" />
                        <span class="w-full"
                              x-text="getFileName(file.file_name) + ' - ' + (file.size / 1000).toFixed(2) + ' KB'"></span>

                    </a>
                </template>
            </div>

            <div class="vcomments__form__footer">
                <button @disabled(!$this->auth)
                        type="submit"
                        @class([
                            'vcomments__btn',
                            'disabled' => !$this->auth,
                            'primary' => $this->auth,
                        ])
                        wire:loading.attr="disabled">
                    {{ __('vgcomment::comment.submit') }}
                </button>

                <div class="vcomments__footer__tooltips-group">
                    <span x-show="loading">{{ __('vgcomment::comment.uploading') }}</span>


                    <a class="vcomments__footer__tooltips">
                        <span x-show="tab == 1"
                              class="text-xs text-end"
                              x-text="textarea_length + '/{{ config('vgcomment.max_length') }}'"></span>
                    </a>
                </div>

            </div>
        </div>
    </form>
</fieldset>
