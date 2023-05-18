<div x-data="{ open: false, src: '' }"
     class="vgcomment_modal"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">

    <div @lightbox-modal.window="open = true; src = $event.detail.src;"
         @keydown.window.escape="open = false"
         x-show="open"
         style="display: none;">

        <div x-show="open"
             x-transition:enter="enter"
             x-transition:enter-start="enter_start"
             x-transition:enter-end="enter_end"
             x-transition:leave="leave"
             x-transition:leave-start="leave_start"
             x-transition:leave-end="leave_end"
             class="modal__background"></div>

        <div class="modal__container">
            <div class="modal__card">

                <div x-show="open"
                     x-transition:enter="enter"
                     x-transition:enter-start="enter_start"
                     x-transition:enter-end="enter_end"
                     x-transition:leave="leave"
                     x-transition:leave-start="leave_start"
                     x-transition:leave-end="leave_end"
                     class="card__body"
                     @click.away="open = false">

                    <div class="vgcomment_text-right">
                        <button x-on:click="open = false" type="button" class="vcomments__btn none">
                            <x-heroicons::icon name="x-mark-o" />
                        </button>
                    </div>

                    <img class="image" :src="src">
                </div>
            </div>
        </div>
    </div>
</div>
