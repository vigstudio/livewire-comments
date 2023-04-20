<div x-data="{ open: false, action: 'delete', message: '', id: 0 }"
     @keydown.window.escape="open = false"
     x-show="open"
     aria-labelledby="modal-title"
     x-ref="dialog"
     aria-modal="true"
     @confirm-action.window="open = true; action = $event.detail.action; message = $event.detail.message; id = $event.detail.id;"
     class="vgcomments_confirm"
     style="display: none;">

    <div x-show="open" class="vgcomments_confirm-over">
    </div>


    <div class="vgcomments_confirm-container">
        <div class="vgcomments_confirm-container-content">

            <div x-show="open"
                 class="vgcomments_confirm-container-content-div"
                 @click.away="open = false">

                <div class="vgcomments_confirm-container-content-div-row">
                    <div class="vgcomments_confirm-container-content-div-row-icon">
                        <svg class="vgcomments_confirm-icon-danger" x-description="Heroicon name: outline/exclamation-triangle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v3.75m-9.303 3.376C1.83 19.126 2.914 21 4.645 21h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 4.88c-.866-1.501-3.032-1.501-3.898 0L2.697 17.626zM12 17.25h.007v.008H12v-.008z"></path>
                        </svg>
                    </div>
                    <div class="vgcomments_confirm-container-content-div-row-text">
                        <h3 class="vgcomments_confirm-container-content-div-row-text-title" id="modal-title" x-text="message"></h3>
                    </div>
                </div>

                <div class="vgcomments_confirm-container-content-div-footer">
                    <button type="button" x-on:click="$wire.emit('confirm-submit', id, action)" class="vgcomments_confirm-container-content-div-footer-button" @click="open = false"> {{ __('vgcomment::comment.submit') }}</button>
                    <button type="button" class="vgcomments_confirm-container-content-div-footer-button-cancel" @click="open = false"> {{ __('vgcomment::comment.cancel') }}</button>
                </div>
            </div>

        </div>
    </div>
</div>
