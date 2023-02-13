<div wire:ignore.self x-data="LivewireComments.form('.textarea-vgcomment-box', $wire)"
     x-init="emojiPicker('.emoji-button')"
     @insert-content.window="afterUpload($event)"
     @post-success-comments.window="cleanData()"
     @open-form.window="$event.detail.open_id == {{ $request['parent_id'] ?: 0 }} ? open = true : open = false">

    <form wire:submit.prevent
          x-data="{
              request: @entangle('request').defer,
              async submit() {
                  @if (Config::get('vgcomment.recaptcha')) this.request.recaptcha_token = await grecaptcha.execute(@js(Config::get('vgcomment.recaptcha_key')), { action: '{{ $method }}' }); @endif
                  $wire.{{ $method }}();
              }
          }">

        <div class="vcomments__form">
            <div class="vcomments__form__header">

                <div class="vcomments__header__tooltips-group">

                    <button x-show="tab == 2" x-on:click="tab = 1" type="button" class="vcomments__btn secondary">
                        <x-heroicons::icon name="eye-slash-o" class="vgcomemnt_icon-5" />
                        <span class="vgcomments__header_title"></span>
                    </button>

                    <button x-show="tab == 1" x-on:click="tab = 2" type="button" class="vcomments__btn secondary" wire:click="preview">
                        <x-heroicons::icon name="eye-o" class="vgcomemnt_icon-5" />
                        <span class="vgcomments__header_title">{{ __('vgcomment::comment.preview') }}</span>
                    </button>

                </div>

                <div>
                    <x-livewire-comments::loading wire:loading />
                    <span x-text="'Uploading....' + progress + '%'" x-show="loading"></span>
                </div>

                <div class="vcomments__header__tooltips-group">

                    <div class="vcomments__header__group">

                        <input x-ref="uploadFiles" x-on:change="uploadFile($event)" type="file" class="sr-only" multiple="true">

                        <button type="button" class="emoji-button vcomments__btn secondary" data-popover-target="emoji">
                            <x-heroicons::icon name="face-smile-s" class="vgcomemnt_icon-4" />
                        </button>



                        <button type="button" class="vcomments__btn secondary" x-on:click="$refs.uploadFiles.click()">

                            <x-heroicons::icon name="paper-clip-s" class="vgcomemnt_icon-4" />

                        </button>
                    </div>

                </div>

            </div>

            <div class="vcomments__form__body @error('content') vgcomments_alert_required @enderror">

                <x-livewire-comments::forms.preview x-show="tab == 2" :preview="$preview" />

                <x-livewire-comments::forms.textarea
                                                     @class([
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
                    <span class="vgcomments_alert_required_text">{{ $message }}</span>
                @enderror
            </div>

            <div class="vcomments__form__attachments">
                <template x-for="file in files">

                    <a x-show="!file.mime_type.includes('image')" x-bind:href="file.url_stream" target="_blank" class="vcomments__file">
                        <x-heroicons::icon name="paper-clip-s" class="vgcomemnt_icon-4" />
                        <span class="w-full" x-text="getFileName(file.file_name) + ' - ' + (file.size / 1000).toFixed(2) + ' KB'"></span>

                    </a>
                </template>
            </div>

            <div class="vcomments__form__footer">
                <div>

                    <button
                            type="button"
                            x-on:click="submit()"
                            @disabled(!$this->auth)
                            @class([
                                'vcomments__btn',
                                'disabled' => !$this->auth,
                                'primary' => $this->auth,
                            ])
                            wire:loading.attr="disabled">

                        {{ __('vgcomment::comment.submit') }}

                        <svg
                             wire:loading
                             class="vgcomment__svg_loading"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24">
                            <circle
                                    class="vgcomment_opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path
                                  class="vgcomment_opacity-75"
                                  fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                    </button>

                    @if ($this->editId)
                        <button type="button" wire:click="cancel" class="vcomments__btn secondary vgcomment_ml-2" wire:loading.attr="disabled">
                            {{ __('vgcomment::comment.cancel') }}
                        </button>
                    @endif

                </div>

                <div class="vcomments__footer__tooltips-group">
                    <span x-show="loading">{{ __('vgcomment::comment.uploading') }}</span>

                    <a class="vcomments__footer__tooltips">
                        <span x-show="tab == 1" class="text-xs text-end" x-text="textarea_length + '/{{ config('vgcomment.max_length') }}'"></span>
                    </a>
                </div>

            </div>
        </div>
    </form>


</div>
