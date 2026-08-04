(function () {
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const routes = window.menuAdminRoutes || {};
    const reorderUrl = routes.reorder || null;

    if (!csrfTokenElement || !reorderUrl) {
        return;
    }

    const token = csrfTokenElement.getAttribute('content');
    let activeType = 'all';

    function successText(key, fallback) {
        return routes[key] || fallback;
    }

    function showSuccess(message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message || successText('savedText', 'Saved'),
            showConfirmButton: false,
            timer: 1200
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: successText('errorText', 'Error'),
            text: message || ''
        });
    }

    function initSortables(scope = document) {
        scope.querySelectorAll('.menu-children').forEach(function (element) {
            if (element.dataset.sortableInit === '1') {
                return;
            }

            element.dataset.sortableInit = '1';

            new Sortable(element, {
                group: 'menus',
                handle: '.menu-admin-drag',
                animation: 150,
                ghostClass: 'bg-light',
                chosenClass: 'border-primary',
                onEnd: function (event) {
                    sendOrder(event.to);

                    if (event.from && event.from !== event.to) {
                        sendOrder(event.from);
                    }
                }
            });
        });
    }

    function sendOrder(container) {
        if (!container) {
            return;
        }

        const parentAttr = container.getAttribute('data-parent');
        const parentId = parentAttr === '' ? null : parseInt(parentAttr, 10);
        const items = [];

        Array.from(container.children).forEach(function (card, index) {
            const id = card.dataset.id ? parseInt(card.dataset.id, 10) : null;

            if (!id) {
                return;
            }

            items.push({
                id: id,
                parent_id: parentId,
                sort_order: index
            });
        });

        if (items.length === 0) {
            return;
        }

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: items })
        })
            .then(async function (response) {
                if (response.ok) {
                    showSuccess(successText('orderUpdatedText', 'Order updated'));
                    return;
                }

                const json = await response.json().catch(function () {
                    return {};
                });

                showError(json.message || successText('errorText', 'Error'));
            })
            .catch(function () {
                showError(successText('errorText', 'Error'));
            });
    }

    function bindToggles(scope = document) {
        scope.querySelectorAll('.toggle').forEach(function (element) {
            if (element.dataset.bound === '1') {
                return;
            }

            element.dataset.bound = '1';

            element.addEventListener('change', function () {
                const input = this;
                const checked = input.checked;
                const url = input.dataset.url;

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        field: input.dataset.field,
                        value: checked ? 1 : 0
                    })
                })
                    .then(async function (response) {
                        if (response.ok) {
                            showSuccess(successText('savedText', 'Saved'));
                            return;
                        }

                        input.checked = !checked;

                        const json = await response.json().catch(function () {
                            return {};
                        });

                        showError(json.message || successText('errorText', 'Error'));
                    })
                    .catch(function () {
                        input.checked = !checked;
                        showError(successText('errorText', 'Error'));
                    });
            });
        });
    }

    function clearDropdownStates(exceptItem = null) {
        document.querySelectorAll('.menu-admin-item.is-dropdown-open').forEach(function (item) {
            if (exceptItem && item === exceptItem) {
                return;
            }

            item.classList.remove('is-dropdown-open');
        });
    }

    function bindDropdownStates(scope = document) {
        scope.querySelectorAll('.menu-admin-actions-cell .dropdown').forEach(function (dropdown) {
            if (dropdown.dataset.bound === '1') {
                return;
            }

            dropdown.dataset.bound = '1';

            dropdown.addEventListener('show.bs.dropdown', function () {
                const item = dropdown.closest('.menu-admin-item');

                clearDropdownStates(item);

                if (item) {
                    item.classList.add('is-dropdown-open');
                }
            });

            dropdown.addEventListener('hidden.bs.dropdown', function () {
                const item = dropdown.closest('.menu-admin-item');

                if (item) {
                    item.classList.remove('is-dropdown-open');
                }
            });
        });
    }

    function bindTreeToggles(scope = document) {
        scope.querySelectorAll('.js-tree-toggle').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }

            button.dataset.bound = '1';

            button.addEventListener('click', function () {
                const card = this.closest('[data-menu-card="1"]');

                if (!card) {
                    return;
                }

                const childrenWrap = card.querySelector(':scope > .menu-admin-children');

                if (!childrenWrap) {
                    return;
                }

                const collapsed = childrenWrap.classList.toggle('is-collapsed');

                this.classList.toggle('is-collapsed', collapsed);
                this.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            });
        });
    }

    function expandParents(card) {
        let current = card;

        while (current) {
            const parentId = current.dataset.parentId || '';

            if (parentId === '') {
                break;
            }

            const parentCard = document.querySelector('[data-menu-card="1"][data-id="' + parentId + '"]');

            if (!parentCard) {
                break;
            }

            const parentChildrenWrap = parentCard.querySelector(':scope > .menu-admin-children');
            const parentToggle = parentCard.querySelector(':scope > .menu-admin-item-row .js-tree-toggle');

            if (parentChildrenWrap) {
                parentChildrenWrap.classList.remove('is-collapsed');
            }

            if (parentToggle) {
                parentToggle.classList.remove('is-collapsed');
                parentToggle.setAttribute('aria-expanded', 'true');
            }

            current = parentCard;
        }
    }

    function normalize(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function matchesSearch(card, query) {
        const searchText = normalize(card.dataset.search || '');

        return query === '' || searchText.includes(query);
    }

    function matchesType(card) {
        const type = normalize(card.dataset.menuType || '');

        return activeType === 'all' || type === activeType;
    }

    function hasAncestorMatchingType(card) {
        let parentId = card.dataset.parentId || '';

        while (parentId !== '') {
            const parentCard = document.querySelector('[data-menu-card="1"][data-id="' + parentId + '"]');

            if (!parentCard) {
                break;
            }

            if (matchesType(parentCard)) {
                return true;
            }

            parentId = parentCard.dataset.parentId || '';
        }

        return false;
    }

    function hasDescendantMatchingFilters(card, query) {
        const descendants = card.querySelectorAll('[data-menu-card="1"]');

        for (const descendant of descendants) {
            if (matchesSearch(descendant, query) && matchesType(descendant)) {
                return true;
            }
        }

        return false;
    }

    function shouldShowCard(card, query) {
        const selfMatch = matchesSearch(card, query) && matchesType(card);
        const descendantMatch = hasDescendantMatchingFilters(card, query);
        const ancestorTypeMatch = hasAncestorMatchingType(card) && matchesSearch(card, query);

        return selfMatch || descendantMatch || ancestorTypeMatch;
    }

    function filterCards() {
        const input = document.getElementById('menu-search');
        const countBadge = document.getElementById('menu-search-count');

        if (!input || !countBadge) {
            return;
        }

        const query = normalize(input.value || '');
        const cards = Array.from(document.querySelectorAll('[data-menu-card="1"]'));

        cards.forEach(function (card) {
            card.classList.add('d-none');
        });

        cards.forEach(function (card) {
            if (shouldShowCard(card, query)) {
                card.classList.remove('d-none');
                expandParents(card);
            }
        });

        const visible = cards.filter(function (card) {
            return !card.classList.contains('d-none');
        }).length;

        if (query === '' && activeType === 'all') {
            countBadge.style.display = 'none';
            countBadge.textContent = '';
            return;
        }

        countBadge.textContent = successText('foundText', 'Found') + ': ' + visible;
        countBadge.style.display = 'inline-flex';
    }

    function bindSearch() {
        const input = document.getElementById('menu-search');

        if (!input) {
            return;
        }

        input.addEventListener('input', filterCards);
    }

    function bindTypeTabs() {
        document.querySelectorAll('.js-menu-type-tab').forEach(function (button) {
            button.addEventListener('click', function () {
                activeType = (this.dataset.type || 'all').toLowerCase();

                document.querySelectorAll('.js-menu-type-tab').forEach(function (item) {
                    item.classList.remove('active');
                });

                this.classList.add('active');

                filterCards();
            });
        });
    }

    initSortables();
    bindToggles();
    bindDropdownStates();
    bindTreeToggles();
    bindSearch();
    bindTypeTabs();
    filterCards();
})();
