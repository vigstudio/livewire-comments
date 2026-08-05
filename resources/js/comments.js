import {mountEmojiPicker} from './emoji-picker';
import Plyr from 'plyr';
import hljs from 'highlight.js';

window.LivewireComments = {};

const emojiPickers = new WeakMap();
const reactionEmojiPickers = new WeakMap();

window.LivewireComments.form = function (cl, id) {
    return {
        open: false,
        tab: 1,
        textarea: null,
        loading: false,
        files: [],
        textarea_length: 0,
        progress: 0,
        draft: '',
        init() {
            try {
                this.bindTextarea();
            } catch (error) {
                console.warn('[vgcomment] bindTextarea failed', error);
            }

            // Defer past Alpine/Livewire ref hydration ($nextTick can race in nested LW forms).
            setTimeout(() => this.mountEmojiPicker(), 50);

            if (window.Livewire) {
                Livewire.hook('morph.updated', ({el}) => {
                    if (this.$el && (this.$el === el || this.$el.contains(el))) {
                        try {
                            this.bindTextarea();
                            this.mountEmojiPicker();
                        } catch (error) {
                            // ignore morph races
                        }
                    }
                });
            }
        },
        wire() {
            return this.$wire;
        },
        mountEmojiPicker() {
            try {
                const trigger = this.$el.querySelector('.emoji-button');
                if (!trigger || trigger.disabled) {
                    return;
                }

                const existing = emojiPickers.get(this.$el);
                if (existing && existing.trigger === trigger) {
                    return;
                }

                if (existing) {
                    try { existing.api.destroy(); } catch (e) {}
                    emojiPickers.delete(this.$el);
                }

                const api = mountEmojiPicker(trigger, {
                    onSelect: (emoji) => this.insertContent(emoji),
                });
                emojiPickers.set(this.$el, { trigger, api });
            } catch (error) {
                console.warn('[vgcomment] emoji picker mount failed', error);
            }
        },
        bindTextarea() {
            const el = (this.$refs && this.$refs.composer)
                || this.$el.querySelector(cl)
                || this.$el.querySelector('textarea.textarea-vgcomment-box')
                || this.$el.querySelector('textarea');

            // Keep previous reference if Livewire morph briefly removes the node.
            if (!el) {
                return;
            }

            this.textarea = el;

            if (this.textarea.dataset.vgBound) {
                if (!this.textarea.value && this.draft) {
                    this.textarea.value = this.draft;
                }
                this.syncComposerLock();
                return;
            }

            this.textarea.dataset.vgBound = '1';

            const self = this;

            this.textarea.addEventListener('input', function () {
                self.draft = this.value;
                self.setTextAreaLength(this.value.length);
            });

            this.draft = this.textarea.value || '';
            this.setTextAreaLength((this.textarea.value || '').length);
            this.syncComposerLock();
        },
        /**
         * Keep textarea disabled only while uploading or when the server
         * marked the composer locked (guest login required → .is-disabled).
         * Previously bindTextarea set disabled during upload but never cleared
         * it after loading=false, leaving the field unfocusable with chips shown.
         */
        syncComposerLock() {
            if (!this.textarea) {
                return;
            }

            const lockedByAuth = this.textarea.classList.contains('is-disabled');
            if (lockedByAuth || this.loading) {
                this.textarea.setAttribute('disabled', true);
            } else {
                this.textarea.removeAttribute('disabled');
            }
        },
        setUploading(next) {
            this.loading = Boolean(next);
            if (!next) {
                this.progress = 0;
            }
            this.syncComposerLock();
        },
        currentContent() {
            this.bindTextarea();
            return (this.textarea && this.textarea.value) || this.draft || '';
        },
        syncContent() {
            const value = this.currentContent();
            if (!this.wire()) {
                return value;
            }

            this.draft = value;
            this.wire().set('request.content', value);

            return value;
        },
        showPreview() {
            const value = this.currentContent();
            this.draft = value;
            if (this.wire()) {
                this.wire().preview(value);
            }
        },
        hidePreview() {
            this.wire()?.closePreview();
        },
        async submit() {
            const wire = this.wire();
            if (!wire) {
                return;
            }

            const value = this.currentContent();
            this.draft = value;
            await wire.set('request.content', value);

            const form = this.$el.querySelector('form');
            const useRecaptcha = form?.dataset.recaptcha === '1';
            const method = form?.dataset.submitMethod || 'submit';

            if (useRecaptcha) {
                if (typeof grecaptcha === 'undefined') {
                    return;
                }
                const token = await grecaptcha.execute(form.dataset.recaptchaKey, {action: method});
                await wire.set('request.recaptcha_token', token);
            }

            await wire[method]();
        },
        wrapSelection(before, after = before, placeholder = '') {
            this.bindTextarea();
            if (!this.textarea || this.textarea.disabled) {
                return;
            }

            const start = this.textarea.selectionStart;
            const end = this.textarea.selectionEnd;
            const value = this.textarea.value;
            let selected = value.substring(start, end);
            let lead = '';
            let trail = '';

            // Keep surrounding whitespace outside emphasis markers so `**bold **` still works.
            if (selected && before && before === after && ['**', '*', '~~', '`'].includes(before)) {
                const match = selected.match(/^(\s*)([\s\S]*?)(\s*)$/);
                if (match) {
                    lead = match[1];
                    selected = match[2];
                    trail = match[3];
                }
            }

            if (!selected) {
                selected = placeholder;
            }

            const replacement = lead + before + selected + after + trail;
            this.textarea.value = value.substring(0, start) + replacement + value.substring(end);
            this.draft = this.textarea.value;

            const selStart = start + lead.length + before.length;
            const selEnd = selStart + selected.length;
            this.textarea.focus();
            this.textarea.setSelectionRange(selStart, selEnd);

            this.setTextAreaLength(this.textarea.value.length);
            this.wire()?.set('request.content', this.textarea.value);
        },
        insertLink() {
            const url = window.prompt('URL');
            if (!url) {
                return;
            }

            this.wrapSelection('[', '](' + url + ')', 'link text');
        },
        setTextAreaLength(length) {
            this.textarea_length = length;
        },
        isImageFile(file) {
            if (!file) return false;
            if (typeof File !== 'undefined' && file instanceof File) {
                return String(file.type || '').startsWith('image/');
            }
            return file.mime === 'image' || String(file.mime_type || '').startsWith('image/');
        },
        markdownImage(file) {
            const alt = String(file.file_name || file.name || 'image').replace(/[[\]]/g, '');
            const url = String(file.url_stream || file.url || '');
            return `![${alt}](${url})`;
        },
        pasteClipboard(e) {
            const files = Array.from(e.clipboardData?.files || []);
            const images = files.filter((file) => this.isImageFile(file));
            if (!images.length) {
                return;
            }

            // Paste images → insert into content (GitHub-like). Ignore non-images on paste.
            e.preventDefault();
            this.setUploading(true);
            this.beginUpload(images, { insertIntoContent: true });
        },
        dropFile(e) {
            const files = Array.from(e.dataTransfer?.files || []);
            if (!files.length) {
                return;
            }

            // All-images drop → insert into content. Mixed/non-image → attachment chips.
            const insertIntoContent = files.every((file) => this.isImageFile(file));
            this.setUploading(true);
            this.beginUpload(files, { insertIntoContent });
        },
        uploadFile(e) {
            this.setUploading(true);
            this.beginUpload(e.target.files, { insertIntoContent: false });
            e.target.value = '';
        },
        uploadInlineImage(e) {
            this.setUploading(true);
            this.beginUpload(e.target.files, { insertIntoContent: true });
            e.target.value = '';
        },
        beginUpload(files, { insertIntoContent = false } = {}) {
            const wire = this.wire();
            if (!wire || !files || files.length === 0) {
                this.setUploading(false);
                return;
            }

            this._uploadInsertIntoContent = Boolean(insertIntoContent);

            wire.uploadMultiple('clipboard', files, () => {
                wire.uploadFile(id);
            }, () => {
                this.setUploading(false);
                this._uploadInsertIntoContent = false;
                wire.getErrors();
            }, (event) => {
                this.progress = event.detail.progress;
            });
        },
        afterUpload(e) {
            const detail = e?.detail;
            // Livewire puts dispatch params in event.detail:
            //   named `files:` → { files: [...] }
            //   positional ['files'=>...] → [{ files: [...] }]  (legacy bug)
            //   legacy per-file / failure → [file] or ['false']
            let files = [];
            if (detail && !Array.isArray(detail) && Array.isArray(detail.files)) {
                files = detail.files;
            } else if (Array.isArray(detail)) {
                if (detail.length === 1 && detail[0] && typeof detail[0] === 'object' && Array.isArray(detail[0].files)) {
                    files = detail[0].files;
                } else {
                    files = detail;
                }
            } else if (detail) {
                files = [detail];
            }

            if (files.length === 1 && files[0] === 'false') {
                this.setUploading(false);
                this._uploadInsertIntoContent = false;
                return;
            }

            const insertIntoContent = Boolean(this._uploadInsertIntoContent);

            files.forEach((value) => {
                if (!value || typeof value !== 'object' || Array.isArray(value)) {
                    return;
                }
                // Skip wrapper objects that lack file metadata
                if (!value.uuid && !value.file_name && !value.url_stream && !value.mime && !value.mime_type) {
                    return;
                }

                if (insertIntoContent && this.isImageFile(value)) {
                    this.insertContent(`${this.markdownImage(value)}\n`);
                    return;
                }

                // Attachment chips only — do not also insert ![alt](url) into the
                // textarea (that duplicates the image in the posted comment body).
                this.files.push(value);
            });

            if (this.wire()) {
                this.wire().attachments = this.files;
            }
            this.setUploading(false);
            this._uploadInsertIntoContent = false;
        },
        removeAttachment(index) {
            const i = Number(index);
            if (Number.isNaN(i) || i < 0 || i >= this.files.length) {
                return;
            }
            this.files.splice(i, 1);
            if (this.wire()) {
                this.wire().attachments = this.files;
            }
        },
        start: 0,
        end: 0,
        cursor: 0,
        tmp: '',
        insertContent(content) {
            this.bindTextarea();
            if (!this.textarea) {
                console.warn('[vgcomment] insertContent: textarea not found');
                return;
            }

            this.start = this.textarea.selectionStart ?? this.textarea.value.length;
            this.end = this.textarea.selectionEnd ?? this.textarea.value.length;
            this.cursor = this.start;
            this.tmp = this.textarea.value;

            this.textarea.value = this.tmp.substring(0, this.start) + content + this.tmp.substring(this.end, this.tmp.length);
            this.draft = this.textarea.value;
            this.textarea.dispatchEvent(new Event('input', { bubbles: true }));

            setTimeout(() => {
                if (!this.textarea) {
                    return;
                }
                this.cursor += content.length;
                this.textarea.selectionStart = this.textarea.selectionEnd = this.cursor;
                this.textarea.focus();
            }, 10);

            this.setTextAreaLength(this.textarea.value.length);
            if (this.wire()) {
                this.wire().set('request.content', this.textarea.value);
            }
        },
        cleanData() {
            this.bindTextarea();
            if (this.textarea) {
                this.textarea.value = '';
            }
            this.draft = '';
            this.start = 0;
            this.end = 0;
            this.cursor = 0;
            this.tmp = '';
            this.open = false;
            this.tab = 1;
            this.files = [];
            this.textarea_length = 0;
        },
        getFileName(str) {
            const value = String(str || 'file');
            if (value.length > 12) {
                return value.substr(0, 11) + '...' + value.substr(-11);
            }

            return value;
        },
    };
};

window.LivewireComments.comment = function (uuid) {
    return {
        reply: false,
        open_id: null,
        remove_mode: false,
        init() {
            this.$nextTick(() => this.mountReactionPicker());
            setTimeout(() => this.mountReactionPicker(), 50);
            setTimeout(() => this.mountReactionPicker(), 300);

            if (window.Livewire) {
                Livewire.hook('morph.updated', ({el}) => {
                    if (this.$el && (this.$el === el || this.$el.contains(el))) {
                        try {
                            this.mountReactionPicker();
                        } catch (error) {
                            // ignore morph races
                        }
                    }
                });
            }
        },
        mountReactionPicker() {
            try {
                const trigger = this.$el.querySelector('[data-reaction-toggle]');
                if (!trigger) {
                    return;
                }

                const host = trigger.closest('.vg-reaction-add') || trigger.parentElement;
                const existing = reactionEmojiPickers.get(this.$el);
                const pickerStillMounted = Boolean(host?.querySelector(':scope > .vg-emoji-picker'));

                if (existing && existing.trigger === trigger && pickerStillMounted) {
                    return;
                }

                if (existing) {
                    try { existing.api.destroy(); } catch (e) {}
                    reactionEmojiPickers.delete(this.$el);
                }

                host?.querySelectorAll(':scope > .vg-emoji-picker').forEach((el) => el.remove());

                const api = mountEmojiPicker(trigger, {
                    onSelect: (emoji) => {
                        if (!emoji) return;
                        this.$wire.toggleReact(uuid, emoji);
                    },
                });
                reactionEmojiPickers.set(this.$el, { trigger, api });
            } catch (error) {
                console.warn('[vgcomment] reaction emoji picker mount failed', error);
            }
        },
    };
};

function lightboxSrcFromEventTarget(target) {
    if (!target || !target.closest) {
        return null;
    }

    // Composer chip thumbs are not lightbox targets.
    if (target.closest('.vg-file--chip, .vg-composer')) {
        return null;
    }

    if (target.closest('.vg-comment__avatar')) {
        return null;
    }

    const img = target.closest('.vg-comment__body img, a.vg-file--image img, .vg-file--image img');
    if (!img || img.classList.contains('vg-file__thumb')) {
        return null;
    }

    return img.currentSrc || img.src || null;
}

function openLightboxModal(src) {
    if (!src) {
        return;
    }

    window.dispatchEvent(new CustomEvent('lightbox-modal', {detail: {src}}));
}

// Capture phase so attachment <a target=_blank> links don't navigate away.
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-vgcomments-blade]')) {
        return;
    }

    const src = lightboxSrcFromEventTarget(event.target);
    if (!src) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    openLightboxModal(src);
}, true);

window.LivewireComments.content = function () {
    return {
        init() {
            this.$el.querySelectorAll('.vgcomments-player').forEach((video) => {
                new Plyr(video, {});
            });

            this.$el.querySelectorAll('pre code').forEach((el) => {
                hljs.highlightElement(el);
            });
        },
    };
};

window.LivewireComments.menu = function (e = {open: false}) {
    const t = useTrackedPointer();
    return {
        init() {
            this.items = Array.from(this.$el.querySelectorAll('[role="menuitem"]'));
            this.$watch('open', () => {
                if (this.open) {
                    this.activeIndex = -1;
                }
            });
        },
        activeDescendant: null,
        activeIndex: null,
        items: null,
        open: e.open,
        onButtonClick() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    const menu = this.$refs['menu-items'];
                    if (!menu) {
                        return;
                    }
                    menu.focus?.();
                });
            }
        },
        onClickAway() {
            if (!this.open) {
                return;
            }

            this.open = false;
        },
        onButtonEnter() {
            this.open = !this.open;
            if (this.open && this.items?.length) {
                this.activeIndex = 0;
                this.activeDescendant = this.items[this.activeIndex]?.id || null;
                this.$nextTick(() => {
                    this.$refs['menu-items']?.focus?.();
                });
            }
        },
        onArrowUp() {
            if (!this.items?.length) {
                return;
            }
            if (!this.open) {
                this.open = true;
                this.activeIndex = this.items.length - 1;
                this.activeDescendant = this.items[this.activeIndex]?.id || null;
                return;
            }
            if (this.activeIndex !== 0) {
                this.activeIndex = this.activeIndex === -1 ? this.items.length - 1 : this.activeIndex - 1;
                this.activeDescendant = this.items[this.activeIndex]?.id || null;
            }
        },
        onArrowDown() {
            if (!this.items?.length) {
                return;
            }
            if (!this.open) {
                this.open = true;
                this.activeIndex = 0;
                this.activeDescendant = this.items[this.activeIndex]?.id || null;
                return;
            }
            if (this.activeIndex !== this.items.length - 1) {
                this.activeIndex = this.activeIndex + 1;
                this.activeDescendant = this.items[this.activeIndex]?.id || null;
            }
        },
        onMouseEnter(e) {
            t.update(e);
        },
        onMouseMove(e, n) {
            if (t.wasMoved(e)) {
                this.activeIndex = n;
            }
        },
        onMouseLeave(e) {
            if (t.wasMoved(e)) {
                this.activeIndex = -1;
            }
        },
    };
};

document.addEventListener('livewire:init', () => {
    Livewire.on('alert', (event) => {
        const type = (event?.type ?? event?.[0] ?? 'alert').toString();
        const message = (event?.message ?? event?.[1] ?? '').toString();

        window.dispatchEvent(new CustomEvent('alert-js', {
            detail: {type, title: type, message},
        }));
    });
});

function useTrackedPointer() {
    let e = [-1, -1];
    return {
        wasMoved(t) {
            const n = [t.screenX, t.screenY];
            return (e[0] !== n[0] || e[1] !== n[1]) && (e = n, true);
        },
        update(t) {
            e = [t.screenX, t.screenY];
        },
    };
}
