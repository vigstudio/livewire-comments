<div aria-live="assertive"
     class="vgcomments_alert"
     x-data="{ alert: false, type: 'success', title: '', message: '' }"
     @keydown.window.escape="alert = false"
     @alert-js.window="
        alert = true;
        type = $event.detail.type;
        title = $event.detail.title;
        message = $event.detail.message;
        setTimeout(() => alert = false, 5000);
     ">

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
            <div class="vgcomment_p-4">
                <div class="vg__content">
                    <x-heroicons::icon x-show="type == 'success'"
                                       name="check-circle-s"
                                       class="vg_icon_success" />


                    <x-heroicons::icon x-show="type == 'alert'"
                                       name="exclamation-triangle-s"
                                       class="vg_icon_warning" />

                    <x-heroicons::icon x-show="type == 'error'"
                                       name="x-circle-s"
                                       class="vg_icon_danger" />

                    <div class="vg__message">
                        <p class="vg__message_p_cap"
                           x-text="title + ' !'"></p>
                        <p class="vg__message_p_mess"
                           x-text="message"></p>
                    </div>

                    <div class="vg_toolkit">
                        <button type="button"
                                x-on:click="show = false"
                                class="vcomments__btn none">
                            <x-heroicons::icon name="x-mark-s"
                                               class="vgcomemnt_icon-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
