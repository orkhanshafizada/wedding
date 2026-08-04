<script>
    (function () {
        const ctx = window.__productForm = window.__productForm || {};

        ctx.renderFilterInputs = function (filters, variationIndex, selectedMap) {
            const container = document.createElement('div');
            container.className = 'row g-3';

            (filters || []).forEach(function (f) {
                const col = document.createElement('div');
                col.className = 'col-lg-6';

                const label = document.createElement('label');
                label.className = 'form-label';
                label.textContent = f.name || ('#' + f.id);
                col.appendChild(label);

                const rawType = (f.input_type || 'single').toString().toLowerCase();
                const isMulti = ['multi', 'multiple', 'checkbox', 'multiselect', 'multi_select'].includes(rawType);

                const selected = (selectedMap[f.id] || []).map(Number);

                if (isMulti) {
                    const select = document.createElement('select');
                    select.className = 'form-control';
                    select.name = 'variations[' + variationIndex + '][filter_values][' + f.id + '][]';
                    select.multiple = true;
                    select.setAttribute('data-choices', '');
                    select.setAttribute('data-choices-removeitem', '');

                    (f.values || []).forEach(function (v) {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = v.name || ('#' + v.id);
                        if (selected.includes(Number(v.id))) opt.selected = true;
                        select.appendChild(opt);
                    });

                    col.appendChild(select);
                    container.appendChild(col);
                    return;
                }

                const select = document.createElement('select');
                select.className = 'form-control';
                select.name = 'variations[' + variationIndex + '][filter_values][' + f.id + ']';
                select.setAttribute('data-choices', '');

                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '{{ __('Select') }}';
                select.appendChild(empty);

                (f.values || []).forEach(function (v) {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.name || ('#' + v.id);
                    if (selected.includes(Number(v.id))) opt.selected = true;
                    select.appendChild(opt);
                });

                col.appendChild(select);
                container.appendChild(col);
            });

            return container;
        };

        ctx.getSelectedFilterValuesFromVariation = function (variationEl) {
            const map = {};
            variationEl.querySelectorAll('[name*="[filter_values]"]').forEach(function (el) {
                const name = el.getAttribute('name') || '';
                const m = name.match(/\[filter_values\]\[(\d+)\]/);
                if (!m) return;

                const filterId = Number(m[1]);
                if (!map[filterId]) map[filterId] = [];

                if (el.tagName === 'SELECT' && el.multiple) {
                    Array.from(el.selectedOptions).forEach(function (o) {
                        map[filterId].push(Number(o.value));
                    });
                    map[filterId] = Array.from(new Set(map[filterId]));
                    return;
                }

                if ((el.value || '') !== '') {
                    map[filterId] = [Number(el.value)];
                }
            });

            return map;
        };

        ctx.ensureFiltersBoxDefault = function (boxEl) {
            if (!boxEl) return;

            const mainId = (document.querySelector('select[name="main_category_id"]')?.value || '').trim();

            boxEl.innerHTML = '';
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning mb-0';
            alert.textContent = mainId ? '{{ __('No filters configured for this category.') }}' : '{{ __('Select main category to see variation filters.') }}';
            boxEl.appendChild(alert);
        };

        ctx.updateVariationFiltersUI = function (filters) {
            document.querySelectorAll('.variation-item').forEach(function (variationEl) {
                const idx = Number(variationEl.dataset.variationIndex || 0);
                const box = variationEl.querySelector('.js-variation-filters-box');
                if (!box) return;

                const selectedMap = ctx.getSelectedFilterValuesFromVariation(variationEl);
                box.innerHTML = '';

                const mainId = (document.querySelector('select[name="main_category_id"]')?.value || '').trim();
                if (!mainId) {
                    ctx.ensureFiltersBoxDefault(box);
                    return;
                }

                if (!filters || filters.length === 0) {
                    ctx.ensureFiltersBoxDefault(box);
                    return;
                }

                box.appendChild(ctx.renderFilterInputs(filters, idx, selectedMap));
                if (ctx.initAllChoices) ctx.initAllChoices(box);
            });
        };

        ctx.fetchMenuFilters = function (menuId) {
            const tpl = "{{ route('admin.product.ajax.menu-filters', ['menu' => '___MENU___']) }}";
            const url = tpl.replace('___MENU___', String(menuId || ''));

            return fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(function (r) {
                if (!r.ok) throw new Error('Request failed');
                return r.json();
            }).then(function (data) {
                return { filters: Array.isArray(data?.filters) ? data.filters : [] };
            });
        };

        ctx.initMainCategoryFilterLoading = function () {
            const select = document.querySelector('select[name="main_category_id"]');
            if (!select) return;

            function load(menuId) {
                const id = (menuId || '').trim();
                if (!id) {
                    document.querySelectorAll('.js-variation-filters-box').forEach(function (box) {
                        ctx.ensureFiltersBoxDefault(box);
                    });
                    return;
                }

                ctx.fetchMenuFilters(id)
                    .then(function (payload) {
                        ctx.updateVariationFiltersUI(payload.filters || []);
                    })
                    .catch(function () {});
            }

            select.addEventListener('change', function () {
                load(select.value || '');
            });

            load(select.value || '');
        };
    })();
</script>
