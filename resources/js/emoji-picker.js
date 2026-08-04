import compact from 'emojibase-data/en/compact.json';
import messages from 'emojibase-data/en/messages.json';

const SKIP_GROUPS = new Set(['component']);

function emojiGlyph(entry) {
    return entry.emoji || entry.unicode || '';
}

function buildIndex() {
    const groups = (messages.groups || [])
        .filter((group) => !SKIP_GROUPS.has(group.key))
        .sort((a, b) => a.order - b.order);

    const byGroup = new Map(groups.map((group) => [group.order, []]));

    compact.forEach((entry) => {
        const glyph = emojiGlyph(entry);
        if (!glyph || entry.group == null || !byGroup.has(entry.group)) {
            return;
        }

        byGroup.get(entry.group).push({
            glyph,
            label: entry.label || '',
            tags: entry.tags || [],
            group: entry.group,
        });
    });

    return { groups, byGroup };
}

const INDEX = buildIndex();

/**
 * Full local emoji picker (no CDN, no PicMo).
 * Returns { destroy } for cleanup.
 */
export function mountEmojiPicker(trigger, { onSelect } = {}) {
    if (!trigger) {
        return { destroy() {} };
    }

    let open = false;
    let activeGroup = INDEX.groups[0]?.order ?? 0;
    let query = '';

    const root = document.createElement('div');
    root.className = 'vg-emoji-picker';
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    root.innerHTML = `
        <div class="vg-emoji-picker__header">
            <div class="vg-emoji-picker__search">
                <input type="search" class="vg-emoji-picker__input" placeholder="Search emoji..." />
            </div>
            <div class="vg-emoji-picker__tabs" role="tablist"></div>
        </div>
        <div class="vg-emoji-picker__grid" role="listbox"></div>
    `;

    const searchInput = root.querySelector('.vg-emoji-picker__input');
    const tabsEl = root.querySelector('.vg-emoji-picker__tabs');
    const gridEl = root.querySelector('.vg-emoji-picker__grid');

    INDEX.groups.forEach((group) => {
        const tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'vg-emoji-picker__tab';
        tab.dataset.group = String(group.order);
        // aria-label only — native title tooltips float oddly over the grid
        tab.setAttribute('aria-label', group.message || 'Emoji category');
        tab.textContent = previewForGroup(group.order);
        tabsEl.appendChild(tab);
    });

    function previewForGroup(order) {
        const first = (INDEX.byGroup.get(order) || [])[0];
        return first?.glyph || '•';
    }

    function visibleEmojis() {
        const q = query.trim().toLowerCase();
        let list = INDEX.byGroup.get(activeGroup) || [];

        if (q) {
            list = [];
            INDEX.byGroup.forEach((entries) => {
                entries.forEach((entry) => {
                    if (
                        entry.label.toLowerCase().includes(q)
                        || entry.tags.some((tag) => String(tag).toLowerCase().includes(q))
                        || entry.glyph.includes(q)
                    ) {
                        list.push(entry);
                    }
                });
            });
        }

        return list;
    }

    function renderGrid() {
        const items = visibleEmojis();
        gridEl.innerHTML = items
            .slice(0, qLimit())
            .map((entry) => `<button type="button" class="vg-emoji-picker__item" aria-label="${escapeAttr(entry.label)}" data-emoji="${escapeAttr(entry.glyph)}">${entry.glyph}</button>`)
            .join('');

        tabsEl.querySelectorAll('.vg-emoji-picker__tab').forEach((tab) => {
            tab.classList.toggle('is-active', Number(tab.dataset.group) === activeGroup && !query.trim());
        });
    }

    function qLimit() {
        return query.trim() ? 300 : 400;
    }

    function escapeAttr(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function setOpen(next) {
        open = next;
        root.hidden = !open;
        root.classList.toggle('is-open', open);
        root.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            renderGrid();
            searchInput.focus();
        }
    }

    function onTriggerClick(event) {
        event.preventDefault();
        event.stopPropagation();
        setOpen(!open);
    }

    function onRootClick(event) {
        const item = event.target.closest('[data-emoji]');
        if (item) {
            event.preventDefault();
            event.stopPropagation();
            onSelect?.(item.dataset.emoji || item.textContent || '');
            setOpen(false);
            return;
        }

        const tab = event.target.closest('.vg-emoji-picker__tab');
        if (tab) {
            event.preventDefault();
            activeGroup = Number(tab.dataset.group);
            query = '';
            searchInput.value = '';
            renderGrid();
        }
    }

    function onDocClick(event) {
        if (!open) return;
        if (root.contains(event.target) || trigger.contains(event.target)) return;
        setOpen(false);
    }

    function onSearchInput() {
        query = searchInput.value || '';
        renderGrid();
    }

    trigger.addEventListener('click', onTriggerClick);
    root.addEventListener('click', onRootClick);
    searchInput.addEventListener('input', onSearchInput);
    document.addEventListener('click', onDocClick);

    const host = trigger.closest('.vg-reaction-add')
        || trigger.closest('.vg-composer__media, .vcomments__header__group, .header__group, .vcomments__emoji')
        || trigger.parentElement
        || trigger;
    if (getComputedStyle(host).position === 'static') {
        host.style.position = 'relative';
    }
    // Open upward for reactions and composer toolbar (avoids covering the thread /
    // getting clipped by nearby overflow containers).
    if (
        host.classList?.contains('vg-reaction-add')
        || host.closest('.vg-composer')
        || host.classList?.contains('vg-composer__media')
        || host.classList?.contains('vcomments__header__group')
        || host.classList?.contains('header__group')
    ) {
        root.classList.add('vg-emoji-picker--above');
    }
    host.appendChild(root);
    renderGrid();

    return {
        destroy() {
            trigger.removeEventListener('click', onTriggerClick);
            document.removeEventListener('click', onDocClick);
            root.remove();
        },
    };
}
