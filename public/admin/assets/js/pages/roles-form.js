(function () {
    const qs = (s, root = document) => root.querySelector(s);
    const qsa = (s, root = document) => Array.from(root.querySelectorAll(s));

    function setIndeterminate(input, indeterminate) {
        if (!input) return;
        input.indeterminate = !!indeterminate;
    }

    function updateGroupHeader(groupCard) {
        const group = groupCard.dataset.group;
        const checkboxes = qsa(`.js-perm[data-group="${group}"]`, groupCard);
        const selected = checkboxes.filter(cb => cb.checked).length;
        const total = checkboxes.length;

        // sayğacı yenilə
        const counter = qs('.js-group-counter .js-selected', groupCard);
        if (counter) counter.textContent = selected.toString();

        // master switch state
        const master = qs('.js-group-toggle', groupCard);
        if (master) {
            if (selected === 0) {
                master.checked = false;
                setIndeterminate(master, false);
            } else if (selected === total) {
                master.checked = true;
                setIndeterminate(master, false);
            } else {
                master.checked = false;
                setIndeterminate(master, true);
            }
        }
    }

    function initGroup(groupCard) {
        // İlk renderdə indeterminate lazım ola bilər
        const master = qs('.js-group-toggle', groupCard);
        if (master && master.dataset.indeterminate === '1') {
            setIndeterminate(master, true);
        }

        // Master toggle
        master && master.addEventListener('change', () => {
            const group = groupCard.dataset.group;
            const checkboxes = qsa(`.js-perm[data-group="${group}"]`, groupCard);
            checkboxes.forEach(cb => cb.checked = master.checked);
            setIndeterminate(master, false);
            updateGroupHeader(groupCard);
        });

        // Tək permission dəyişəndə başlığı yenilə
        groupCard.addEventListener('change', (e) => {
            if (e.target && e.target.classList.contains('js-perm')) {
                updateGroupHeader(groupCard);
            }
        });
    }

    function selectAll() {
        qsa('.js-perm').forEach(cb => cb.checked = true);
        qsa('.group-card').forEach(updateGroupHeader);
    }

    function clearAll() {
        qsa('.js-perm').forEach(cb => cb.checked = false);
        qsa('.group-card').forEach(updateGroupHeader);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Hər qrupu initialize et
        qsa('.group-card').forEach(initGroup);

        // Qlobal düymələr
        const btnAll = qs('#permSelectAll');
        const btnClr = qs('#permClearAll');
        btnAll && btnAll.addEventListener('click', selectAll);
        btnClr && btnClr.addEventListener('click', clearAll);
    });
})();
