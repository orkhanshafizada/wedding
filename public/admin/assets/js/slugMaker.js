    (function () {
    function slugifySmart(text) {
        const map = {
            'Ə': 'E', 'ə': 'e', 'Ş': 'S', 'ş': 's', 'Ç': 'C', 'ç': 'c', 'Ğ': 'G', 'ğ': 'g',
            'Ö': 'O', 'ö': 'o', 'Ü': 'U', 'ü': 'u', 'İ': 'I', 'i': 'i', 'I': 'I', 'ı': 'i',

            'А':'A','а':'a','Б':'B','б':'b','В':'V','в':'v','Г':'G','г':'g','Д':'D','д':'d',
            'Е':'E','е':'e','Ё':'E','ё':'e','Ж':'Zh','ж':'zh','З':'Z','з':'z','И':'I','и':'i',
            'Й':'Y','й':'y','К':'K','к':'k','Л':'L','л':'l','М':'M','м':'m','Н':'N','н':'n',
            'О':'O','о':'o','П':'P','п':'p','Р':'R','р':'r','С':'S','с':'s','Т':'T','т':'t',
            'У':'U','у':'u','Ф':'F','ф':'f','Х':'Kh','х':'kh','Ц':'Ts','ц':'ts','Ч':'Ch','ч':'ch',
            'Ш':'Sh','ш':'sh','Щ':'Sch','щ':'sch','Ъ':'','ъ':'','Ы':'Y','ы':'y','Ь':'','ь':'',
            'Э':'E','э':'e','Ю':'Yu','ю':'yu','Я':'Ya','я':'ya'
        };

        const raw = (text || '').toString();
        const hasLeadingSlash = /^\s*[\/\\]/.test(raw);

        const base = raw
            .replace(/[–—−]/g, '-')
            .replace(/[\\]+/g, '/')
            .split('')
            .map(ch => map[ch] ?? ch)
            .join('')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

        const segments = base.split('/').map(seg => {
            return seg
                .replace(/[\s\_]+/g, '-')
                .replace(/[^a-z0-9\-#]+/g, '')
                .replace(/\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }).filter(Boolean);

        const joined = segments.join('/');
        return hasLeadingSlash ? ('/' + joined) : joined;
    }

    function setValue(el, val) {
    if (!el) return;
    el.value = val;
    el.dispatchEvent(new Event('input', { bubbles: true }));
}

    function normalizeSlugField(slugEl) {
    const raw = slugEl.value || '';
    let val = slugifySmart(raw);

    const keepTail =
    (/[–—−-]$/.test(raw) ? '-' : '') ||
    (/\/$/.test(raw) ? '/' : '') ||
    (/#$/.test(raw) ? '#' : '');

    if (keepTail && val && !val.endsWith(keepTail)) {
    val = val + keepTail;
}

    if (raw !== val) {
    slugEl.value = val;
    slugEl.dispatchEvent(new Event('change', { bubbles: true }));
}
}

    // manual slug edit: always sanitize, allow trailing '-' or '/'
    document.addEventListener('input', function (e) {
    const slugEl = e.target.closest('[data-menu-link],[data-lang-slug],[data-variation-slug]');
    if (!slugEl) return;
    normalizeSlugField(slugEl);
}, true);

    // MENU: data-menu-name -> data-menu-link
    document.addEventListener('input', function (e) {
    const nameEl = e.target.closest('[data-menu-name]');
    if (!nameEl) return;

    const code = nameEl.getAttribute('data-menu-name');
    const linkEl = document.querySelector('[data-menu-link="' + code + '"]');
    if (!linkEl) return;

    setValue(linkEl, slugifySmart(nameEl.value || ''));
}, true);

    // PRODUCT MAIN: data-lang-name -> data-lang-slug
    document.addEventListener('input', function (e) {
    const nameEl = e.target.closest('[data-lang-name]');
    if (!nameEl) return;

    const langId = nameEl.getAttribute('data-lang-name');
    const slugEl = document.querySelector('[data-lang-slug="' + langId + '"]');
    if (!slugEl) return;

    setValue(slugEl, slugifySmart(nameEl.value || ''));
}, true);

    // PRODUCT VARIATION: data-variation-name -> data-variation-slug
    document.addEventListener('input', function (e) {
    const nameEl = e.target.closest('[data-variation-name]');
    if (!nameEl) return;

    const key = nameEl.getAttribute('data-variation-name'); // "idx-langId"
    const slugEl = document.querySelector('[data-variation-slug="' + key + '"]');
    if (!slugEl) return;

    setValue(slugEl, slugifySmart(nameEl.value || ''));
}, true);

    // Generate buttons (main)
    document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-generate-slug');
    if (!btn) return;

    const langId = btn.dataset.lang;
    const nameEl = document.querySelector('[data-lang-name="' + langId + '"]');
    const slugEl = document.querySelector('[data-lang-slug="' + langId + '"]');
    if (!slugEl) return;

    const base = nameEl ? nameEl.value : slugEl.value;
    setValue(slugEl, slugifySmart(base || ''));
});

    // Generate buttons (variation)
    document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-generate-variation-slug');
    if (!btn) return;

    const vIdx = btn.dataset.variation;
    const langId = btn.dataset.lang;

    const nameEl = document.querySelector('[data-variation-name="' + vIdx + '-' + langId + '"]');
    const slugEl = document.querySelector('[data-variation-slug="' + vIdx + '-' + langId + '"]');
    if (!slugEl) return;

    const base = nameEl ? nameEl.value : slugEl.value;
    setValue(slugEl, slugifySmart(base || ''));
});
})();
