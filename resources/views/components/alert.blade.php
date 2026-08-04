<div aria-live="assertive"
     class="vg-toast"
     x-data="{ alert: false, type: 'success', title: '', message: '' }"
     @keydown.window.escape="alert = false"
     @alert-js.window="
        alert = true;
        type = $event.detail.type || 'success';
        title = ($event.detail.title || type || '').toString().replace(/!+\s*$/, '');
        message = ($event.detail.message || '').toString().replace(/!+\s*$/, '');
        setTimeout(() => alert = false, 5000);
     ">
    <div class="vg-toast__stack">
        <div
            x-show="alert"
            x-cloak
            x-transition:enter="vg-toast__enter"
            x-transition:enter-start="vg-toast__enter-start"
            x-transition:enter-end="vg-toast__enter-end"
            x-transition:leave="vg-toast__leave"
            x-transition:leave-start="vg-toast__leave-start"
            x-transition:leave-end="vg-toast__leave-end"
            class="vg-toast__card"
            role="alert"
            style="display: none;"
        >
            <div class="vg-toast__body">
                <div class="vg-toast__icon"
                     :class="{
                        'vg-toast__icon--success': type === 'success',
                        'vg-toast__icon--warning': type === 'alert',
                        'vg-toast__icon--danger': type === 'error'
                     }"
                     aria-hidden="true">
                    <template x-if="type === 'success'">
                        <span>
                            <x-heroicons::icon name="check-circle-s" class="h-6 w-6" />
                        </span>
                    </template>
                    <template x-if="type === 'alert'">
                        <span>
                            <x-heroicons::icon name="exclamation-triangle-s" class="h-6 w-6" />
                        </span>
                    </template>
                    <template x-if="type === 'error'">
                        <span>
                            <x-heroicons::icon name="x-circle-s" class="h-6 w-6" />
                        </span>
                    </template>
                </div>

                <div class="vg-toast__text">
                    <p class="vg-toast__title" x-text="title || type"></p>
                    <p class="vg-toast__message" x-show="message" x-text="message"></p>
                </div>

                <button type="button"
                        class="vg-toast__close"
                        x-on:click="alert = false"
                        aria-label="Dismiss">
                    <x-heroicons::icon name="x-mark-s" class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</div>
