<script>
    (function () {
        const ctx = window.__productForm = window.__productForm || {};

        ctx.initVariationSlugGenerate = function () {
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-generate-variation-slug');
                if (!btn) return;

                const v = btn.getAttribute('data-variation');
                const lang = btn.getAttribute('data-lang');
                if (v === null || lang === null) return;

                const nameEl = document.querySelector('[data-variation-name="' + v + '-' + lang + '"]');
                const slugEl = document.querySelector('[data-variation-slug="' + v + '-' + lang + '"]');
                if (!nameEl || !slugEl) return;

                const base = (nameEl.value || '').trim();
                if (!base) return;

                if (!ctx.slugify) return;

                slugEl.value = ctx.slugify(base);
                slugEl.dispatchEvent(new Event('input', { bubbles: true }));
            });
        };

        ctx.buildVariationHtml = function (index, filters) {
            const langs = Array.isArray(window.__productLanguages) ? window.__productLanguages : [];

            const generalLangTabs = langs.map(function (l, i) {
                return `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${i === 0 ? 'active' : ''}"
                                data-bs-toggle="tab"
                                data-bs-target="#v${index}-general-lang-${l.id}"
                                type="button"
                                role="tab">
                            ${l.name}
                        </button>
                    </li>
                `;
            }).join('');

            const generalLangPanes = langs.map(function (l, i) {
                return `
                    <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="v${index}-general-lang-${l.id}" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input type="text"
                                       class="form-control"
                                       name="variations[${index}][translations][${l.id}][name]"
                                       value=""
                                       data-variation-name="${index}-${l.id}">
                            </div>

                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0">{{ __('Slug') }}</label>
                                    <button type="button"
                                            class="btn btn-sm btn-soft-primary js-generate-variation-slug"
                                            data-variation="${index}"
                                            data-lang="${l.id}">
                                        <i class="ri-magic-line align-bottom me-1"></i>{{ __('Generate') }}
                </button>
            </div>
            <input type="text"
                   class="form-control mt-2"
                   name="variations[${index}][translations][${l.id}][slug]"
                                       value=""
                                       data-variation-slug="${index}-${l.id}">
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea rows="4"
                                          class="form-control js-editor"
                                          name="variations[${index}][translations][${l.id}][description]"></textarea>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            const seoLangTabs = langs.map(function (l, i) {
                return `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${i === 0 ? 'active' : ''}"
                                data-bs-toggle="tab"
                                data-bs-target="#v${index}-seo-lang-${l.id}"
                                type="button"
                                role="tab">
                            ${l.name}
                        </button>
                    </li>
                `;
            }).join('');

            const seoLangPanes = langs.map(function (l, i) {
                const hiddenId = `v-meta-keywords-hidden-${index}-${l.id}`;
                const wrapId = `v-meta-keywords-wrap-${index}-${l.id}`;
                const inputId = `v-meta-keywords-input-${index}-${l.id}`;

                return `
                    <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="v${index}-seo-lang-${l.id}" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">{{ __('Meta title') }}</label>
                                <input type="text"
                                       class="form-control"
                                       name="variations[${index}][translations][${l.id}][meta_title]"
                                       value="">
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">{{ __('Meta keywords') }}</label>

                                <input type="hidden"
                                       id="${hiddenId}"
                                       name="variations[${index}][translations][${l.id}][meta_keywords]"
                                       value="">

                                <div id="${wrapId}" class="d-flex flex-wrap gap-2 mb-2"></div>

                                <input type="text"
                                       id="${inputId}"
                                       class="form-control js-meta-keyword-input"
                                       data-hidden-id="${hiddenId}"
                                       data-wrap-id="${wrapId}"
                                       placeholder="{{ __('Type keyword and press Enter') }}"
                                       autocomplete="off">

                                <div class="form-text">{{ __('Keywords will be saved as comma-separated.') }}</div>
                            </div>

                            <div class="col-lg-12">
                                <label class="form-label">{{ __('Meta description') }}</label>
                                <textarea rows="2"
                                          class="form-control"
                                          name="variations[${index}][translations][${l.id}][meta_description]"></textarea>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            const html = `
                <div class="border rounded p-3 mb-3 variation-item" data-variation-index="${index}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold js-variation-title">{{ __('Variation') }} #${index + 1}</div>
                        <button type="button" class="btn btn-sm btn-soft-danger js-remove-variation">
                            <i class="ri-close-line align-bottom"></i>
                        </button>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#v${index}-tab-general" type="button" role="tab">
                                {{ __('General') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v${index}-tab-seo" type="button" role="tab">
                                {{ __('SEO') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v${index}-tab-filter" type="button" role="tab">
                                {{ __('Filter') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v${index}-tab-media" type="button" role="tab">
                                {{ __('Media') }}
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="v${index}-tab-general" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('SKU') }}</label>
                                    <input type="text"
                                           name="variations[${index}][sku]"
                                           value=""
                                           class="form-control"
                                           data-field="sku">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('Model') }}</label>
                                    <input type="text"
                                           name="variations[${index}][model]"
                                           value=""
                                           class="form-control"
                                           data-field="model">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('Sort order') }}</label>
                                    <input type="number"
                                           min="1"
                                           step="1"
                                           name="variations[${index}][sort_order]"
                                           value="${index + 1}"
                                           class="form-control js-variation-sort-order"
                                           data-field="sort_order">
                                </div>
                            </div>

                            <div class="row g-3 mt-0">
                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Price') }}</label>
                                    <input type="number" step="0.01"
                                           name="variations[${index}][price]"
                                           value="0"
                                           class="form-control"
                                           data-field="price"
                                           required>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Old price') }}</label>
                                    <input type="number" step="0.01"
                                           name="variations[${index}][old_price]"
                                           value=""
                                           class="form-control"
                                           data-field="old_price">
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Discount price') }}</label>
                                    <input type="number" step="0.01"
                                           name="variations[${index}][discount_price]"
                                           value=""
                                           class="form-control"
                                           data-field="discount_price">
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Stock') }}</label>
                                    <input type="number" step="1"
                                           name="variations[${index}][stock]"
                                           value="0"
                                           class="form-control"
                                           data-field="stock"
                                           required>
                                </div>
                            </div>

                            <div class="mt-3 p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Content') }}</div>
                                    <span class="text-muted fs-12">{{ __('Slug must be globally unique (across all products and variations).') }}</span>
                                </div>

                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    ${generalLangTabs}
                                </ul>

                                <div class="tab-content border border-top-0 p-3">
                                    ${generalLangPanes}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v${index}-tab-seo" role="tabpanel">
                            <div class="p-3 border rounded">
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    ${seoLangTabs}
                                </ul>

                                <div class="tab-content border border-top-0 p-3">
                                    ${seoLangPanes}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v${index}-tab-filter" role="tabpanel">
                            <div class="p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Variation Filters') }}</div>
                                    <div class="text-muted fs-12">{{ __('Filters are based on main category.') }}</div>
                                </div>

                                <div class="js-variation-filters-box"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v${index}-tab-media" role="tabpanel">
                            <div class="p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Variation Gallery') }}</div>
                                    <div class="text-muted fs-12">{{ __('Drag to reorder. Select one main image.') }}</div>
                                </div>

                                <input type="file"
                                       class="form-control js-media-input"
                                       name="variations[${index}][media_files][]"
                                       accept="image/*"
                                       multiple
                                       data-variation="${index}">

                                <div class="mt-3 row g-3 variation-media-list" data-variation-media-list="${index}"></div>
                                <div class="mt-3 row g-3 js-new-media-list" data-new-media-list="${index}"></div>

                                <div class="form-text mt-2">
                                    {{ __('Deleted images will be removed after saving.') }}
            </div>
        </div>
    </div>
</div>
</div>
`;

            const wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            const el = wrap.firstElementChild;

            const box = el.querySelector('.js-variation-filters-box');
            if (box) {
                const mainId = (document.querySelector('select[name="main_category_id"]')?.value || '').trim();

                box.innerHTML = '';
                if (!mainId) {
                    if (ctx.ensureFiltersBoxDefault) ctx.ensureFiltersBoxDefault(box);
                } else if (!filters || filters.length === 0) {
                    if (ctx.ensureFiltersBoxDefault) ctx.ensureFiltersBoxDefault(box);
                } else if (ctx.renderFilterInputs) {
                    box.appendChild(ctx.renderFilterInputs(filters, index, {}));
                }
            }

            if (ctx.initAllChoices) ctx.initAllChoices(el);
            if (ctx.initMetaKeywordsUI) ctx.initMetaKeywordsUI();
            if (ctx.initExistingDragDrop) ctx.initExistingDragDrop(el);
            if (ctx.initExistingMainToggles) ctx.initExistingMainToggles(el);
            if (ctx.initExistingDeleteButtons) ctx.initExistingDeleteButtons(el);
            if (ctx.initNewMediaManager) ctx.initNewMediaManager(el);

            return el;
        };

        ctx.initVariationsRepeater = function () {
            const wrap = document.getElementById('variations-wrap');
            if (!wrap) return;

            if (wrap.dataset.repeaterInited === '1') return;
            wrap.dataset.repeaterInited = '1';

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-remove-variation');
                if (!btn) return;

                const item = btn.closest('.variation-item');
                if (!item) return;

                if (window.__productDestroyCkEditors) {
                    window.__productDestroyCkEditors(item);
                }

                item.remove();
            });

            document.addEventListener('click', function (e) {
                const addBtn = e.target.closest('#add-variation');
                if (!addBtn) return;

                e.preventDefault();

                const indices = Array.from(wrap.querySelectorAll('.variation-item'))
                    .map(el => parseInt(el.dataset.variationIndex || '0', 10))
                    .filter(n => Number.isFinite(n));

                const nextIndex = indices.length ? (Math.max.apply(null, indices) + 1) : 0;

                const mainCategory = (document.querySelector('select[name="main_category_id"]')?.value || '').trim();

                if (!mainCategory) {
                    const el = ctx.buildVariationHtml(nextIndex, []);
                    wrap.appendChild(el);
                    setTimeout(function () {
                        if (typeof window.__adminInitCkEditors === 'function') {
                            window.__adminInitCkEditors();
                        }
                    }, 0);
                    if (ctx.initAllChoices) ctx.initAllChoices(el);
                    if (ctx.initMetaKeywordsUI) ctx.initMetaKeywordsUI();
                    if (ctx.initExistingDragDrop) ctx.initExistingDragDrop(el);
                    if (ctx.initExistingMainToggles) ctx.initExistingMainToggles(el);
                    if (ctx.initExistingDeleteButtons) ctx.initExistingDeleteButtons(el);
                    if (ctx.initNewMediaManager) ctx.initNewMediaManager(el);

                    const box = el.querySelector('.js-variation-filters-box');
                    if (ctx.ensureFiltersBoxDefault) ctx.ensureFiltersBoxDefault(box);

                    return;
                }

                if (!ctx.fetchMenuFilters) {
                    const el = ctx.buildVariationHtml(nextIndex, []);
                    wrap.appendChild(el);
                    setTimeout(function () {
                        if (typeof window.__adminInitCkEditors === 'function') {
                            window.__adminInitCkEditors();
                        }
                    }, 0);
                    const box = el.querySelector('.js-variation-filters-box');
                    if (ctx.ensureFiltersBoxDefault) ctx.ensureFiltersBoxDefault(box);
                    return;
                }

                ctx.fetchMenuFilters(mainCategory)
                    .then(function (payload) {
                        const filters = (payload && payload.filters) ? payload.filters : [];
                        const el = ctx.buildVariationHtml(nextIndex, filters);
                        wrap.appendChild(el);
                        setTimeout(function () {
                            if (typeof window.__adminInitCkEditors === 'function') {
                                window.__adminInitCkEditors();
                            }
                        }, 0);
                        if (ctx.initAllChoices) ctx.initAllChoices(el);
                        if (ctx.initMetaKeywordsUI) ctx.initMetaKeywordsUI();
                        if (ctx.initExistingDragDrop) ctx.initExistingDragDrop(el);
                        if (ctx.initExistingMainToggles) ctx.initExistingMainToggles(el);
                        if (ctx.initExistingDeleteButtons) ctx.initExistingDeleteButtons(el);
                        if (ctx.initNewMediaManager) ctx.initNewMediaManager(el);
                    })
                    .catch(function () {
                        const el = ctx.buildVariationHtml(nextIndex, []);
                        wrap.appendChild(el);
                        setTimeout(function () {
                            if (typeof window.__adminInitCkEditors === 'function') {
                                window.__adminInitCkEditors();
                            }
                        }, 0);

                        if (ctx.initAllChoices) ctx.initAllChoices(el);
                        if (ctx.initMetaKeywordsUI) ctx.initMetaKeywordsUI();
                        if (ctx.initExistingDragDrop) ctx.initExistingDragDrop(el);
                        if (ctx.initExistingMainToggles) ctx.initExistingMainToggles(el);
                        if (ctx.initExistingDeleteButtons) ctx.initExistingDeleteButtons(el);
                        if (ctx.initNewMediaManager) ctx.initNewMediaManager(el);

                        const box = el.querySelector('.js-variation-filters-box');
                        if (ctx.ensureFiltersBoxDefault) ctx.ensureFiltersBoxDefault(box);
                    });
            });

            wrap.querySelectorAll('.variation-item').forEach(function (v) {
                if (ctx.initNewMediaManager) ctx.initNewMediaManager(v);
            });
        };
    })();
</script>
