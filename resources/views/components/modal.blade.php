<div x-data="{ open: false, src: '' }"
     class="vgcomment_modal"
     aria-labelledby="vg-lightbox-title"
     role="dialog"
     aria-modal="true"
     x-cloak>

    <div @lightbox-modal.window="open = true; src = ($event.detail && $event.detail.src) ? $event.detail.src : ($event.detail || '').src || '';"
         @keydown.window.escape="open = false"
         x-show="open"
         style="display: none;">

        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal__background"
             @click="open = false"></div>

        <div class="modal__container">
            <div class="modal__card">
                <div x-show="open"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="card__body"
                     @click.stop>

                    <div class="vgcomment_text-right">
                        <button x-on:click="open = false"
                                type="button"
                                class="vcomments__btn none vg-lightbox__close"
                                aria-label="Close">
                            <x-heroicons::icon name="x-mark-o" />
                        </button>
                    </div>

                    <img class="image vg-lightbox__image"
                         :src="src"
                         alt="">
                </div>
            </div>
        </div>
    </div>
</div>
