const toast = (message) => {
    const box = document.getElementById('toast');
    box.textContent = message;
    box.classList.remove('hidden');
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => box.classList.add('hidden'), 5000);
};

const sidebar = document.getElementById('sidebar');
const opener = document.getElementById('menu-open');
const overlay = document.getElementById('nav-overlay');
const setMenu = (open) => {
    sidebar.classList.toggle('open', open);
    sidebar.inert = !open && !window.matchMedia('(min-width: 1024px)').matches;
    overlay.classList.toggle('hidden', !open);
    opener.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) document.getElementById('menu-close').focus();
    else opener.focus();
};
opener?.addEventListener('click', () => setMenu(true));
document.getElementById('menu-close')?.addEventListener('click', () => setMenu(false));
overlay?.addEventListener('click', () => setMenu(false));
const desktop = window.matchMedia('(min-width: 1024px)');
const syncSidebar = () => {
    sidebar.inert = !desktop.matches && !sidebar.classList.contains('open');
    if (desktop.matches && sidebar.classList.contains('open')) { sidebar.classList.remove('open'); overlay.classList.add('hidden'); document.body.style.overflow = ''; opener.setAttribute('aria-expanded','false'); }
};
new MutationObserver(syncSidebar).observe(sidebar, {attributes: true, attributeFilter:['class']});
desktop.addEventListener('change', syncSidebar);
syncSidebar();
document.addEventListener('keydown', (event) => {
    if (!sidebar.classList.contains('open')) return;
    if (event.key === 'Escape') setMenu(false);
    if (event.key === 'Tab') {
        const focusable = [...sidebar.querySelectorAll('a,button')].filter(el => el.offsetParent !== null);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
});

document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));

const editor = document.getElementById('post-editor');
let dirty = false;
editor?.addEventListener('input', () => {
    dirty = true;
    document.getElementById('unsaved-warn')?.classList.remove('hidden');
    document.getElementById('unsaved-warn-schedule')?.classList.remove('hidden');
});
document.querySelectorAll('[data-requires-saved]').forEach(form => form.addEventListener('submit', event => {
    if (dirty) { event.preventDefault(); toast('Save your content changes and review the saved version first.'); }
}));
document.querySelectorAll('[data-preview]').forEach(input => {
    const update = () => {
        const target = document.getElementById(`preview-${input.dataset.preview}`);
        if (target) target.textContent = input.value;
        if (input.dataset.preview === 'caption') document.getElementById('caption-count').textContent = [...input.value].length;
    };
    input.addEventListener('input', update);
    // Rehydrate the preview from old input after a validation error.
    update();
});
document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    const ids = button.dataset.copy === 'caption' ? ['hook','caption','cta'] : ['hashtags'];
    const text = ids.map(id => document.getElementById(`preview-${id}`)?.textContent.trim()).filter(Boolean).join('\n\n');
    if (!text) return toast('There is no text to copy yet.');
    try {
        if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(text);
        else {
            const field = document.createElement('textarea');
            field.value = text; field.style.position = 'fixed'; field.style.opacity = '0';
            document.body.append(field); field.select();
            const copied = document.execCommand('copy'); field.remove(); button.focus();
            if (!copied) throw new Error('Clipboard unavailable');
        }
        toast(button.dataset.copy === 'caption' ? 'Caption copied to clipboard.' : 'Hashtags copied to clipboard.');
    } catch { toast('Clipboard access is unavailable. Select and copy the preview text manually.'); }
}));

const campaignForm = document.getElementById('campaign-form');
if (campaignForm) {
    const duration = document.getElementById('duration');
    const start = document.getElementById('start-date');
    const end = document.getElementById('end-date');
    const dayDiff = Math.round((new Date(end.value) - new Date(start.value)) / 86400000) + 1;
    duration.value = ['3','7','14'].includes(String(dayDiff)) ? String(dayDiff) : 'custom';
    const updateEnd = () => {
        if (duration.value === 'custom' || !start.value) return;
        const date = new Date(`${start.value}T00:00:00Z`);
        date.setUTCDate(date.getUTCDate() + Number(duration.value) - 1);
        end.value = date.toISOString().slice(0,10);
        end.min = start.value;
    };
    duration.addEventListener('change', updateEnd);
    start.addEventListener('change', updateEnd);
    end.addEventListener('change', () => { duration.value = 'custom'; });
    let generating = false;
    campaignForm.addEventListener('submit', event => {
        if (generating) { event.preventDefault(); return; }
        if (!campaignForm.querySelector('input[name="platforms[]"]:checked')) { event.preventDefault(); toast('Choose at least one platform.'); return; }
        generating = true;
        const mode = event.submitter?.value ?? 'gemini';
        const hidden = document.createElement('input'); hidden.type='hidden'; hidden.name='mode'; hidden.value=mode; campaignForm.append(hidden);
        campaignForm.querySelectorAll('[data-generate]').forEach(button => {
            button.disabled = true;
            if (mode === button.value) {
                const spinner = document.createElement('span');
                spinner.className = 'btn-spinner';
                button.prepend(spinner);
            }
        });
        document.getElementById('generation-status').textContent = mode === 'sample' ? 'Creating your sample drafts…' : 'Generating your plan. This may take up to 90 seconds…';
    });
}
