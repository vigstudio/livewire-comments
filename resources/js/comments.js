import {createPopup} from '@picmo/popup-picker';
import Plyr from 'plyr';
import hljs from 'highlight.js';

window.LivewireComments = {},
    window.LivewireComments.form = function (id, $wire) {
        return {
            open: false,
            tab: 1,
            textarea: null,
            loading: false,
            files: [],
            textarea_length: 0,
            progress: 0,
            init() {
                this.textarea = this.$el.querySelector(id);
                if (this.loading) {
                    this.textarea.setAttribute('disabled', true);
                }

                let self = this;

                this.textarea.addEventListener("input", function (event) {
                    self.setTextAreaLength(this.value.length);
                });
            },
            setTextAreaLength: function (length) {
                this.textarea_length = length;
            },
            emojiPicker(triggerClass) {
                const trigger = this.$el.querySelector(triggerClass);
                const picker = createPopup({
                    animate: true,
                    autoFocus: 'emojis',
                    emojiSize: "1.5em",
                    emojisPerRow: 10,
                    visibleRows: 3,
                    showPreview: false,
                    showVariants: false,
                    showSearch: true,
                    showCategoryTabs: false,
                    showRecents: false
                }, {
                    referenceElement: trigger,
                    triggerElement: trigger,
                    hideOnEmojiSelect: false,
                    position: 'bottom-end'
                });

                trigger.addEventListener('click', () => {
                    picker.toggle();
                });

                picker.addEventListener('emoji:select', (selection) => {
                    this.insertContent(selection.emoji);
                });
            },
            pasteClipboard(e) {
                this.loading = true;
                let files = e.clipboardData.files;

                this.beginUpload(files);

            },
            dropFile(e) {
                this.loading = true;
                let files = e.dataTransfer.files;

                this.beginUpload(files);
            },
            uploadFile(e) {
                this.loading = true;
                let files = e.target.files;
                this.beginUpload(files);

            },
            beginUpload(files) {
                if (files.length > 0) {

                    $wire.uploadMultiple('clipboard', files, () => {
                        $wire.uploadFile();
                    }, () => {
                        this.loading = false;
                        $wire.getErrors();
                    }, (event) => {
                        this.progress = event.detail.progress;
                    });

                } else {
                    this.loading = false;
                    return;
                };
            },
            afterUpload(e) {
                if (e.detail == 'false') {
                    this.loading = false;
                    return;
                }

                if (e.detail.mime !== 'image') {
                    this.files.push(e.detail);
                }

                if (e.detail.mime == 'image') {
                    let markdown = "![" + e.detail.name + "](" + e.detail.url_stream + ")"
                    this.insertContent(markdown + '\n ');
                }

                $wire.attachments = this.files;
                this.loading = false;
            },
            start: 0,
            end: 0,
            cursor: 0,
            tmp: '',
            insertContent(content) {
                this.start = this.textarea.selectionStart;
                this.end = this.textarea.selectionEnd;
                this.cursor = this.start;
                this.tmp = this.textarea.value;

                this.textarea.value = this.tmp.substring(0, this.start) + content + this.tmp.substring(this.end, this.tmp.length);

                setTimeout(() => {
                    this.cursor += content.length;
                    this.textarea.selectionStart = this.textarea.selectionEnd = this.cursor;
                }, 10);

                this.setTextAreaLength(this.textarea.value.length);

                $wire.set('request.content', this.textarea.value);
            },
            cleanData() {
                this.textarea.value = '';
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
                if (str.length > 12) {
                    return str.substr(0, 11) + '...' + str.substr(-11)
                }

                return str
            },
        }

    },
    window.LivewireComments.comment = function (uuid, $wire) {
        return {
            reply: false,
            open_id: null,
            remove_mode: false,
            init() {
                this.$el.querySelector('.comment-reply').addEventListener('click', () => {
                    this.reply = !this.reply;
                    this.open_id = uuid;
                });
            },
            emojiPicker(triggerClass) {
                const trigger = this.$el.querySelector(triggerClass);
                const picker = createPopup({
                    animate: true,
                    autoFocus: 'emojis',
                    emojiSize: "1.5em",
                    emojisPerRow: 10,
                    visibleRows: 3,
                    showPreview: false,
                    showVariants: false,
                    showSearch: true,
                    showCategoryTabs: false,
                    showRecents: false
                }, {
                    referenceElement: trigger,
                    triggerElement: trigger,
                    hideOnEmojiSelect: true,
                    position: 'left-end'
                });

                trigger.addEventListener('click', () => {
                    picker.toggle();
                });

                picker.addEventListener('emoji:select', (selection) => {
                    $wire.react(uuid, selection.emoji);
                });
            },
        }
    },
    window.LivewireComments.content = function () {
        return {
            init() {

                this.$el.querySelectorAll("img").forEach((function (img) {
                    img.addEventListener("click", function (e) {
                        window.dispatchEvent(new CustomEvent('lightbox-modal', {detail: this}));
                    });
                }))

                this.$el.querySelectorAll(".vgcomments-player").forEach((function (video) {
                    new Plyr(video, {});
                }))

                this.$el.querySelectorAll('pre code').forEach((el) => {
                    hljs.highlightElement(el);
                });

            }
        }
    },
    window.LivewireComments.menu = function (e = {open: !1}) {
        let t = useTrackedPointer();
        return {
            init() {
                this.items = Array.from(this.$el.querySelectorAll('[role="menuitem"]')), this.$watch("open", (() => {
                    this.open && (this.activeIndex = -1)
                }))
            },
            activeDescendant: null,
            activeIndex: null,
            items: null,
            open: e.open,
            onButtonClick() {
                this.open = !this.open, this.open && this.$nextTick((() => {
                    this.$refs["menu-items"].focus();
                    this.$refs["menu-items"].setAttribute("x-show", this.open);
                }));


            },
            onButtonEnter() {
                this.open = !this.open, this.open && (this.activeIndex = 0, this.activeDescendant = this.items[this.activeIndex].id, this.$nextTick((() => {
                    this.$refs["menu-items"].focus()
                })))
            },
            onArrowUp() {
                if (!this.open) return this.open = !0, this.activeIndex = this.items.length - 1, void (this.activeDescendant = this.items[this.activeIndex].id);
                0 !== this.activeIndex && (this.activeIndex = -1 === this.activeIndex ? this.items.length - 1 : this.activeIndex - 1, this.activeDescendant = this.items[this.activeIndex].id)
            },
            onArrowDown() {
                if (!this.open) return this.open = !0, this.activeIndex = 0, void (this.activeDescendant = this.items[this.activeIndex].id);
                this.activeIndex !== this.items.length - 1 && (this.activeIndex = this.activeIndex + 1, this.activeDescendant = this.items[this.activeIndex].id)
            },
            onClickAway(e) {
                if (this.open) {
                    const t = ["[contentEditable=true]", "[tabindex]", "a[href]", "area[href]", "button:not([disabled])", "iframe", "input:not([disabled])", "select:not([disabled])", "textarea:not([disabled])"].map((e => `${e}:not([tabindex='-1'])`)).join(",");
                    this.open = !1, e.target.closest(t);
                }
                this.$refs["menu-items"].setAttribute("x-show", this.open);
            },
            onMouseEnter(e) {
                t.update(e)
            },
            onMouseMove(e, n) {
                t.wasMoved(e) && (this.activeIndex = n)
            },
            onMouseLeave(e) {
                t.wasMoved(e) && (this.activeIndex = -1)
            }
        }
    };


window.livewire.on('alert', data => {
    const type = data[0].toString();
    const message = data[1];

    window.dispatchEvent(new CustomEvent('alert', {detail: {type: type, title: type, message: message}}));
});



function useTrackedPointer() {
    let e = [-1, -1];
    return {
        wasMoved(t) {
            let n = [t.screenX, t.screenY];
            return (e[0] !== n[0] || e[1] !== n[1]) && (e = n, !0)
        },
        update(t) {
            e = [t.screenX, t.screenY]
        }
    }
}
