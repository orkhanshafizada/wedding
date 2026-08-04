    (function () {
    const picker = document.getElementById('menuFaPicker');
    const control = document.getElementById('menuFaPickerControl');
    const dropdown = document.getElementById('menuFaPickerDropdown');
    const searchInput = document.getElementById('menuFaPickerSearch');
    const list = document.getElementById('menuFaPickerList');
    const empty = document.getElementById('menuFaPickerEmpty');
    const hiddenInput = document.getElementById('menuIconValue');
    const valueText = document.getElementById('menuFaPickerValue');
    const preview = document.getElementById('menuFaPickerPreview');
    const clearButton = document.getElementById('menuFaPickerClear');
    const selectedCardIcon = document.getElementById('menuFaSelectedCardIcon');
    const selectedCardValue = document.getElementById('menuFaSelectedCardValue');
    const versionSelect = document.getElementById('iconLibraryVersion');

    if (!picker || !control || !dropdown || !searchInput || !list || !hiddenInput || !valueText || !preview || !clearButton || !selectedCardIcon || !selectedCardValue || !versionSelect) {
    return;
}

    function openDropdown() {
    picker.classList.add('is-open');
    control.setAttribute('aria-expanded', 'true');

    setTimeout(function () {
    searchInput.focus();
}, 10);
}

    function closeDropdown() {
    picker.classList.remove('is-open');
    control.setAttribute('aria-expanded', 'false');
}

    function setSelectedIcon(iconClass) {
    const normalized = (iconClass || '').trim();

    hiddenInput.value = normalized;
    valueText.textContent = normalized || 'Select icon';
    selectedCardValue.textContent = normalized || 'No icon selected';

    if (normalized) {
    preview.innerHTML = '<i class="' + normalized + '"></i>';
    selectedCardIcon.innerHTML = '<i class="' + normalized + '"></i>';
} else {
    preview.innerHTML = '<i class="fa-solid fa-icons"></i>';
    selectedCardIcon.innerHTML = '<i class="fa-solid fa-icons"></i>';
}

    list.querySelectorAll('.fa-picker__item').forEach(function (item) {
    item.classList.toggle('is-active', item.dataset.icon === normalized);
});
}

    function filterItems() {
    const keyword = (searchInput.value || '').trim().toLowerCase();
    let visibleCount = 0;

    list.querySelectorAll('.fa-picker__item').forEach(function (item) {
    const iconClass = (item.dataset.icon || '').toLowerCase();
    const matched = keyword === '' || iconClass.indexOf(keyword) !== -1;

    item.classList.toggle('d-none', !matched);

    if (matched) {
    visibleCount++;
}
});

    empty.classList.toggle('d-none', visibleCount > 0);
    list.classList.toggle('d-none', visibleCount === 0);
}

    control.addEventListener('click', function () {
    if (picker.classList.contains('is-open')) {
    closeDropdown();
    return;
}

    openDropdown();
});

    document.addEventListener('click', function (event) {
    if (!picker.contains(event.target)) {
    closeDropdown();
}
});

    searchInput.addEventListener('input', filterItems);

    list.addEventListener('click', function (event) {
    const item = event.target.closest('.fa-picker__item');

    if (!item) {
    return;
}

    setSelectedIcon(item.dataset.icon || '');
    closeDropdown();
});

    clearButton.addEventListener('click', function () {
    setSelectedIcon('');
    searchInput.value = '';
    filterItems();
});

    document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
    closeDropdown();
}
});

    versionSelect.addEventListener('change', function () {
    const selectedVersion = this.value;

    fetch("{{ route('admin.menus.ajax.fontawesome-icons') }}?version=" + encodeURIComponent(selectedVersion), {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
}
})
    .then(function (response) {
    return response.json();
})
    .then(function (response) {
    const icons = Array.isArray(response.icons) ? response.icons : [];
    const currentSelectedIcon = hiddenInput.value || '';

    let html = '';

    icons.forEach(function (iconClass) {
    const isActive = currentSelectedIcon === iconClass ? ' is-active' : '';

    html += '<button type="button" class="fa-picker__item' + isActive + '" data-icon="' + iconClass + '">';
    html += '<span class="fa-picker__item-icon"><i class="' + iconClass + '"></i></span>';
    html += '<span class="fa-picker__item-text">' + iconClass + '</span>';
    html += '</button>';
});

    list.innerHTML = html;
    searchInput.value = '';
    filterItems();

    const stillExists = icons.indexOf(currentSelectedIcon) !== -1;

    if (!stillExists) {
    setSelectedIcon('');
}
});
});

    setSelectedIcon(hiddenInput.value || '');
    filterItems();
})();
