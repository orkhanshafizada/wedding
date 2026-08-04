document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-menu-content');
    if (!btn) return;
    const url = btn.dataset.url;
    if (url) window.location.href = url;
});
