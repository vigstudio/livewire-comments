<div aria-live="assertive"
     class="vgcomments_alert"
     x-data="{ alert: false, type: 'success', title: '', message: '' }"
     @keydown.window.escape="alert = false"
     @alert.window="alert = true; type = $event.detail.type; title = $event.detail.title; message = $event.detail.message; setTimeout(() => alert = false, 5000)">

    <div class="vg__container">

        <div x-show="alert"
             x-transition:enter="enter"
             x-transition:enter-start="enter_start"
             x-transition:enter-end="enter_end"
             x-transition:leave="leave"
             x-transition:leave-start="start"
             x-transition:leave-end="end"
             class="vg__body"
             style="display: none;">
            <div class="p-4">
                <div class="vg__content">
                    <x-heroicons::icon x-show="type == 'success'"
                                       name="check-circle-s"
                                       class="h-6 w-6 text-green-400" />


                    <x-heroicons::icon x-show="type == 'alert'"
                                       name="exclamation-triangle-s"
                                       class="h-6 w-6 text-orange-300" />

                    <x-heroicons::icon x-show="type == 'error'"
                                       name="x-circle-s"
                                       class="h-6 w-6 text-red-600" />

                    <div class="vg__message">
                        <p class="text-sm font-medium text-gray-900 capitalize"
                           x-text="title + ' !'"></p>
                        <p class="mt-1 text-sm text-gray-500"
                           x-text="message"></p>
                    </div>

                    <div class="vg_toolkit">
                        <button type="button"
                                x-on:click="show = false"
                                class="vcomments__btn none">
                            <x-heroicons::icon name="x-mark-s"
                                               class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
