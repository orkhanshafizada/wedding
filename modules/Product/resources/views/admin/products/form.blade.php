@php
    use Modules\Product\Models\Menu\MenuProductFilter;

    $isEdit = isset($product) && $product;

    $locale = (string) app()->getLocale();
    $language = \App\Models\Language::query()->where('code', $locale)->first();
    $languageId = $language ? (int) $language->id : null;

    $oldCategoryIds = array_map('strval', (array) old('category_ids', $isEdit ? $product->categories->pluck('id')->all() : []));
    $oldLabelIds = array_map('strval', (array) old('label_ids', $isEdit ? $product->labels->pluck('id')->all() : []));

    $prefill = isset($prefillMainCategoryId) ? (string) $prefillMainCategoryId : '';

    $selectedMain = (string) old('main_category_id', $isEdit ? (string) $product->main_category_id : $prefill);
    $selectedStatus = (string) old('status', $isEdit ? (string) ($product->status ?? 'Active') : 'Active');

    $variationOld = old('variations');

    if (is_array($variationOld)) {
        $variationItems = array_values($variationOld);
    } else {
        $variationItems = $isEdit
            ? $product->variations->sortBy(fn ($v) => (int) ($v->sort_order ?? $v->id))->map(function ($v) use ($languages) {
                $t = [];
                foreach ($languages as $lang) {
                    $existing = $v->translations?->firstWhere('language_id', $lang->id);
                    $t[$lang->id] = [
                        'name' => $existing?->name ?? '',
                        'slug' => $existing?->slug ?? '',
                        'description' => $existing?->description ?? '',
                        'meta_title' => $existing?->meta_title ?? '',
                        'meta_description' => $existing?->meta_description ?? '',
                        'meta_keywords' => $existing?->meta_keywords ?? '',
                    ];
                }

                return [
                    'id' => $v->id,
                    'sku' => $v->sku ?? '',
                    'model' => $v->model ?? '',
                    'sort_order' => (int) ($v->sort_order ?? 1),
                    'price' => $v->price,
                    'old_price' => $v->old_price,
                    'discount_price' => $v->discount_price,
                    'stock' => $v->stock,
                    'media' => $v->media ?? collect(),
                    'filter_values' => [],
                    'translations' => $t,
                ];
            })->values()->all()
            : [];
    }

    if (! $isEdit && ! is_array($variationOld) && empty($variationItems)) {
        $seedTranslations = [];
        foreach ($languages as $lang) {
            $seedTranslations[$lang->id] = [
                'name' => '',
                'slug' => '',
                'description' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
            ];
        }

        $variationItems = [[
            'id' => null,
            'sku' => '',
            'model' => '',
            'sort_order' => 1,
            'price' => 0,
            'old_price' => null,
            'discount_price' => null,
            'stock' => 0,
            'media' => [],
            'filter_values' => [],
            'translations' => $seedTranslations,
        ]];
    }

    $menuFilters = collect();
    if ($selectedMain !== '') {
        $menuFilters = MenuProductFilter::query()
            ->where('menu_id', (int) $selectedMain)
            ->with([
                'filter.translations',
                'filter.values.translations',
            ])
            ->get();
    }

    if ($isEdit) {
        $product->variations->loadMissing(['filterValues', 'media', 'translations']);
    }

    $nameByLocale = function ($model) use ($locale) {
        return $model->translations->firstWhere('locale', $locale)?->name
            ?? $model->translations->first()?->name
            ?? ('#' . $model->id);
    };

    $categoryOptionLabel = function ($category, int $depth = 0, array $parents = []) use ($nameByLocale) {
        $currentName = $nameByLocale($category);

        if ($depth <= 0) {
            return $currentName;
        }

        $prefix = str_repeat('-', $depth) . ' ';
        $parentPath = implode(' → ', $parents);


        return $prefix . $currentName . ' (' . $parentPath . ')';
    };

    $labelNameByLang = function ($label) use ($languageId) {
        return $label->translations?->firstWhere('language_id', $languageId)?->name
            ?? $label->translations?->first()?->name
            ?? ('#' . $label->id);
    };

    $filterNameByLang = function ($filter) use ($languageId) {
        return $filter?->translations?->firstWhere('language_id', $languageId)?->name
            ?? $filter?->translations?->first()?->name
            ?? ('#' . (int) ($filter?->id ?? 0));
    };

    $filterValueNameByLang = function ($value) use ($languageId) {
        return $value->translations?->firstWhere('language_id', $languageId)?->name
            ?? $value->translations?->first()?->name
            ?? ('#' . (int) ($value?->id ?? 0));
    };
@endphp
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('General') }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">{{ __('Main category') }}</label>
                <select name="main_category_id"
                        id="main_category_select"
                        class="form-control @error('main_category_id') is-invalid @enderror"
                        data-choices
                        data-choices-search-enabled="true"
                        required>
                    <option value="">{{ __('Select') }}</option>

                    @foreach($categories as $category)
                        @php
                            $catName = $nameByLocale($category);
                            $children1 = collect($category->children ?? [])->sortBy('sort_order');
                        @endphp

                        <option value="{{ $category->id }}" @selected($selectedMain === (string) $category->id)>
                            {{ $categoryOptionLabel($category, 0) }}
                        </option>

                        @foreach($children1 as $child1)
                            @php
                                $child1Name = $nameByLocale($child1);
                                $children2 = collect($child1->children ?? [])->sortBy('sort_order');
                            @endphp

                            <option value="{{ $child1->id }}" @selected($selectedMain === (string) $child1->id)>
                                {{ $categoryOptionLabel($child1, 1, [$catName]) }}
                            </option>

                            @foreach($children2 as $child2)
                                <option value="{{ $child2->id }}" @selected($selectedMain === (string) $child2->id)>
                                    {{ $categoryOptionLabel($child2, 2, [$catName, $child1Name]) }}
                                </option>
                            @endforeach
                        @endforeach
                    @endforeach
                </select>
                @error('main_category_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6">
                <label class="form-label">{{ __('Related categories') }}</label>
                <select name="category_ids[]"
                        id="related_categories_select"
                        class="form-control @error('category_ids') is-invalid @enderror"
                        data-choices
                        data-choices-removeitem
                        data-choices-search-enabled="true"
                        multiple>
                    @foreach($categories as $category)
                        @php
                            $catName = $nameByLocale($category);
                            $children1 = collect($category->children ?? [])->sortBy('sort_order');
                        @endphp

                        <option value="{{ $category->id }}" @selected(in_array((string) $category->id, $oldCategoryIds, true))>
                            {{ $categoryOptionLabel($category, 0) }}
                        </option>

                        @foreach($children1 as $child1)
                            @php
                                $child1Name = $nameByLocale($child1);
                                $children2 = collect($child1->children ?? [])->sortBy('sort_order');
                            @endphp

                            <option value="{{ $child1->id }}" @selected(in_array((string) $child1->id, $oldCategoryIds, true))>
                                {{ $categoryOptionLabel($child1, 1, [$catName]) }}
                            </option>

                            @foreach($children2 as $child2)
                                <option value="{{ $child2->id }}" @selected(in_array((string) $child2->id, $oldCategoryIds, true))>
                                    {{ $categoryOptionLabel($child2, 2, [$catName, $child1Name]) }}
                                </option>
                            @endforeach
                        @endforeach
                    @endforeach
                </select>
                @error('category_ids')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6">
                <label class="form-label">{{ __('Labels') }}</label>
                <select name="label_ids[]" class="form-control @error('label_ids') is-invalid @enderror" data-choices
                        data-choices-removeitem multiple>
                    @foreach($labels as $label)
                        <option
                            value="{{ $label->id }}" @selected(in_array((string) $label->id, $oldLabelIds, true))>{{ $labelNameByLang($label) }}</option>
                    @endforeach
                </select>
                @error('label_ids')
                <div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-3">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" data-choices>
                    @foreach(\App\Enums\StatusEnum::getOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus == $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-3">
                <label class="form-label">{{ __('Published at') }}</label>
                @php
                    $publishedAt = old(
                        'published_at',
                        $isEdit && $product->published_at ? $product->published_at->format('Y-m-d\TH:i') : ''
                    );
                @endphp
                <input type="datetime-local" name="published_at" value="{{ $publishedAt }}"
                       class="form-control @error('published_at') is-invalid @enderror">
                @error('published_at')
                <div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Variations') }}</h5>
        <button type="button" class="btn btn-sm btn-primary" id="add-variation">
            <i class="ri-add-line align-bottom me-1"></i>{{ __('Add variation') }}
        </button>
    </div>
    <div class="card-body">
        @error('variations')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div id="variations-wrap">
            @foreach($variationItems as $index => $v)
                @php
                    $vid = $v['id'] ?? null;

                    $sku = (string) ($v['sku'] ?? '');
                    $model = (string) ($v['model'] ?? '');
                    $sortOrder = (int) ($v['sort_order'] ?? ($index + 1));

                    $price = $v['price'] ?? 0;
                    $oldPrice = $v['old_price'] ?? '';
                    $discountPrice = $v['discount_price'] ?? '';
                    $stock = $v['stock'] ?? 0;

                    $variationIdInt = (int) ($vid ?? 0);

                    $variationModel = null;
                    if ($isEdit && $variationIdInt > 0) {
                        $variationModel = $product->variations->firstWhere('id', $variationIdInt);
                    }

                    $dbMedia = collect($variationModel?->media ?? [])
                        ->sortBy(fn ($m) => (int) ($m->sort_order ?? 0))
                        ->values();

                    $oldExisting = old("variations.$index.media_existing");

                    $mediaItems = collect();

                    if (is_array($oldExisting) && $oldExisting !== []) {
                        $dbById = $dbMedia->keyBy(fn ($m) => (int) ($m->id ?? 0));

                        foreach (array_values($oldExisting) as $mIndex => $row) {
                            $row = is_array($row) ? $row : [];
                            $mid = (int) ($row['id'] ?? 0);
                            if ($mid <= 0) {
                                continue;
                            }

                            $m = $dbById->get($mid);
                            if (!$m) {
                                continue;
                            }

                            $mediaItems->push((object) [
                                'id' => $mid,
                                'url' => (string) ($m->url ?? ''),
                                'sort_order' => (int) ($row['sort_order'] ?? ($mIndex)),
                                'is_main' => (int) ($row['is_main'] ?? 0),
                                '_delete' => (int) ($row['_delete'] ?? 0),
                            ]);
                        }

                        $seen = $mediaItems->pluck('id')->map(fn ($x) => (int) $x)->all();
                        $seenMap = array_fill_keys($seen, true);

                        foreach ($dbMedia as $m) {
                            $mid = (int) ($m->id ?? 0);
                            if ($mid <= 0 || isset($seenMap[$mid])) {
                                continue;
                            }

                            $mediaItems->push((object) [
                                'id' => $mid,
                                'url' => (string) ($m->url ?? ''),
                                'sort_order' => (int) ($m->sort_order ?? 0),
                                'is_main' => (int) ($m->is_main ?? 0),
                                '_delete' => 0,
                            ]);
                        }

                        $mediaItems = $mediaItems
                            ->sortBy(fn ($x) => (int) ($x->sort_order ?? 0))
                            ->values();
                    } else {
                        $mediaItems = $dbMedia;
                    }

                    $selectedByFilterId = [];
                    $oldFV = old("variations.$index.filter_values");

                    if (is_array($oldFV)) {
                        foreach ($oldFV as $fid => $val) {
                            $fid = (int) $fid;
                            $vals = is_array($val) ? $val : [$val];
                            $selectedByFilterId[$fid] = array_values(array_unique(array_map('intval', $vals)));
                        }
                    } elseif ($isEdit && $vid) {
                        $variationModel = $product->variations->firstWhere('id', (int) $vid);
                        if ($variationModel) {
                            $selectedByFilterId = $variationModel->filterValues
                                ->groupBy('product_filter_id')
                                ->map(fn ($g) => $g->pluck('id')->map(fn ($x) => (int) $x)->values()->all())
                                ->toArray();
                        }
                    }

                    $tOld = old("variations.$index.translations", $v['translations'] ?? []);
                @endphp

                <div class="border rounded p-3 mb-3 variation-item" data-variation-index="{{ $index }}">
                    @if($isEdit && !empty($vid))
                        <input type="hidden" data-field="id" name="variations[{{ $index }}][id]" value="{{ $vid }}">
                    @endif

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold js-variation-title">{{ __('Variation') }} #{{ $index + 1 }}</div>
                        <button type="button" class="btn btn-sm btn-soft-danger js-remove-variation">
                            <i class="ri-close-line align-bottom"></i>
                        </button>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#v{{ $index }}-tab-general" type="button" role="tab">
                                {{ __('General') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v{{ $index }}-tab-seo"
                                    type="button" role="tab">
                                {{ __('SEO') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v{{ $index }}-tab-filter"
                                    type="button" role="tab">
                                {{ __('Filter') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v{{ $index }}-tab-media"
                                    type="button" role="tab">
                                {{ __('Media') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="v{{ $index }}-tab-general" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('SKU') }}</label>
                                    <input type="text"
                                           name="variations[{{ $index }}][sku]"
                                           value="{{ $sku }}"
                                           class="form-control @error('variations.' . $index . '.sku') is-invalid @enderror"
                                           data-field="sku">
                                    @error('variations.' . $index . '.sku')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('Model') }}</label>
                                    <input type="text"
                                           name="variations[{{ $index }}][model]"
                                           value="{{ $model }}"
                                           class="form-control @error('variations.' . $index . '.model') is-invalid @enderror"
                                           data-field="model">
                                    @error('variations.' . $index . '.model')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">{{ __('Sort order') }}</label>
                                    <input type="number"
                                           min="1"
                                           step="1"
                                           name="variations[{{ $index }}][sort_order]"
                                           value="{{ $sortOrder }}"
                                           class="form-control js-variation-sort-order @error('variations.' . $index . '.sort_order') is-invalid @enderror"
                                           data-field="sort_order">
                                    @error('variations.' . $index . '.sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-0">
                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Price') }}</label>
                                    <input type="number" step="0.01" name="variations[{{ $index }}][price]"
                                           value="{{ $price }}"
                                           class="form-control @error('variations.' . $index . '.price') is-invalid @enderror"
                                           data-field="price" required>
                                    @error('variations.' . $index . '.price')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Old price') }}</label>
                                    <input type="number" step="0.01" name="variations[{{ $index }}][old_price]"
                                           value="{{ $oldPrice }}"
                                           class="form-control @error('variations.' . $index . '.old_price') is-invalid @enderror"
                                           data-field="old_price">
                                    @error('variations.' . $index . '.old_price')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Discount price') }}</label>
                                    <input type="number" step="0.01" name="variations[{{ $index }}][discount_price]"
                                           value="{{ $discountPrice }}"
                                           class="form-control @error('variations.' . $index . '.discount_price') is-invalid @enderror"
                                           data-field="discount_price">
                                    @error('variations.' . $index . '.discount_price')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">{{ __('Stock') }}</label>
                                    <input type="number" step="1" name="variations[{{ $index }}][stock]"
                                           value="{{ $stock }}"
                                           class="form-control @error('variations.' . $index . '.stock') is-invalid @enderror"
                                           data-field="stock" required>
                                    @error('variations.' . $index . '.stock')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-3 p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Content') }}</div>
                                    <span
                                        class="text-muted fs-12">{{ __('Slug must be globally unique (across all products and variations).') }}</span>
                                </div>

                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    @foreach($languages as $li => $language)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link @if($li === 0) active @endif"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#v{{ $index }}-general-lang-{{ $language->id }}"
                                                    type="button"
                                                    role="tab">
                                                {{ $language->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content border border-top-0 p-3">
                                    @foreach($languages as $li => $language)
                                        @php
                                            $row = $tOld[$language->id] ?? ['name' => '', 'slug' => '', 'description' => '', 'meta_title' => '', 'meta_description' => '', 'meta_keywords' => ''];
                                        @endphp
                                        <div class="tab-pane fade @if($li === 0) show active @endif"
                                             id="v{{ $index }}-general-lang-{{ $language->id }}" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <label class="form-label">{{ __('Name') }}</label>
                                                    <input type="text"
                                                           class="form-control @error('variations.' . $index . '.translations.' . $language->id . '.name') is-invalid @enderror"
                                                           name="variations[{{ $index }}][translations][{{ $language->id }}][name]"
                                                           value="{{ $row['name'] ?? '' }}"
                                                           data-variation-name="{{ $index }}-{{ $language->id }}">
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.name')
                                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <label class="form-label mb-0">{{ __('Slug') }}</label>
                                                        <button type="button"
                                                                class="btn btn-sm btn-soft-primary js-generate-variation-slug"
                                                                data-variation="{{ $index }}"
                                                                data-lang="{{ $language->id }}">
                                                            <i class="ri-magic-line align-bottom me-1"></i>{{ __('Generate') }}
                                                        </button>
                                                    </div>
                                                    <input type="text"
                                                           class="form-control mt-2 @error('variations.' . $index . '.translations.' . $language->id . '.slug') is-invalid @enderror"
                                                           name="variations[{{ $index }}][translations][{{ $language->id }}][slug]"
                                                           value="{{ $row['slug'] ?? '' }}"
                                                           data-variation-slug="{{ $index }}-{{ $language->id }}">
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.slug')
                                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="col-lg-12">
                                                    <label class="form-label">{{ __('Description') }}</label>
                                                    <textarea rows="4"
                                                              class="form-control js-editor @error('variations.' . $index . '.translations.' . $language->id . '.description') is-invalid @enderror"
                                                              name="variations[{{ $index }}][translations][{{ $language->id }}][description]">{{ $row['description'] ?? '' }}</textarea>
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.description')
                                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v{{ $index }}-tab-seo" role="tabpanel">
                            <div class="p-3 border rounded">
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    @foreach($languages as $li => $language)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link @if($li === 0) active @endif"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#v{{ $index }}-seo-lang-{{ $language->id }}"
                                                    type="button"
                                                    role="tab">
                                                {{ $language->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content border border-top-0 p-3">
                                    @foreach($languages as $li => $language)
                                        @php
                                            $row = $tOld[$language->id] ?? ['meta_title' => '', 'meta_description' => '', 'meta_keywords' => ''];
                                            $vKeywordsHiddenId = "v-meta-keywords-hidden-{$index}-{$language->id}";
                                            $vKeywordsWrapId = "v-meta-keywords-wrap-{$index}-{$language->id}";
                                            $vKeywordsInputId = "v-meta-keywords-input-{$index}-{$language->id}";
                                        @endphp
                                        <div class="tab-pane fade @if($li === 0) show active @endif"
                                             id="v{{ $index }}-seo-lang-{{ $language->id }}" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <label class="form-label">{{ __('Meta title') }}</label>
                                                    <input type="text"
                                                           class="form-control @error('variations.' . $index . '.translations.' . $language->id . '.meta_title') is-invalid @enderror"
                                                           name="variations[{{ $index }}][translations][{{ $language->id }}][meta_title]"
                                                           value="{{ $row['meta_title'] ?? '' }}">
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.meta_title')
                                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="col-lg-6">
                                                    <label class="form-label">{{ __('Meta keywords') }}</label>

                                                    <input type="hidden"
                                                           id="{{ $vKeywordsHiddenId }}"
                                                           name="variations[{{ $index }}][translations][{{ $language->id }}][meta_keywords]"
                                                           value="{{ $row['meta_keywords'] ?? '' }}">

                                                    <div id="{{ $vKeywordsWrapId }}"
                                                         class="d-flex flex-wrap gap-2 mb-2"></div>

                                                    <input type="text"
                                                           id="{{ $vKeywordsInputId }}"
                                                           class="form-control js-meta-keyword-input"
                                                           data-hidden-id="{{ $vKeywordsHiddenId }}"
                                                           data-wrap-id="{{ $vKeywordsWrapId }}"
                                                           placeholder="{{ __('Type keyword and press Enter') }}"
                                                           autocomplete="off">

                                                    <div
                                                        class="form-text">{{ __('Keywords will be saved as comma-separated.') }}</div>
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.meta_keywords')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="col-lg-12">
                                                    <label class="form-label">{{ __('Meta description') }}</label>
                                                    <textarea rows="2"
                                                              class="form-control @error('variations.' . $index . '.translations.' . $language->id . '.meta_description') is-invalid @enderror"
                                                              name="variations[{{ $index }}][translations][{{ $language->id }}][meta_description]">{{ $row['meta_description'] ?? '' }}</textarea>
                                                    @error('variations.' . $index . '.translations.' . $language->id . '.meta_description')
                                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v{{ $index }}-tab-filter" role="tabpanel">
                            <div class="p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Variation Filters') }}</div>
                                    <div class="text-muted fs-12">{{ __('Filters are based on main category.') }}</div>
                                </div>

                                <div class="js-variation-filters-box">
                                    @if($selectedMain === '')
                                        <div
                                            class="alert alert-warning mb-0">{{ __('Select main category to see variation filters.') }}</div>
                                    @elseif($menuFilters->isEmpty())
                                        <div
                                            class="alert alert-warning mb-0">{{ __('No filters configured for this category.') }}</div>
                                    @else
                                        <div class="row g-3">
                                            @foreach($menuFilters as $mf)
                                                @php
                                                    $filter = $mf->filter;
                                                    $filterId = (int) ($mf->product_filter_id ?? 0);
                                                    $inputType = (string) ($filter?->input_type ?? 'single');

                                                    $filterName = $filterNameByLang($filter);
                                                    $selectedIds = array_map('intval', (array) ($selectedByFilterId[$filterId] ?? []));
                                                @endphp

                                                <div class="col-lg-6">
                                                    <label class="form-label">{{ $filterName }}</label>

                                                    @if($inputType === 'multi')
                                                        <select class="form-control"
                                                                name="variations[{{ $index }}][filter_values][{{ $filterId }}][]"
                                                                multiple
                                                                data-choices
                                                                data-choices-removeitem>
                                                            @foreach(($filter?->values ?? collect()) as $value)
                                                                <option
                                                                    value="{{ $value->id }}" @selected(in_array((int) $value->id, $selectedIds, true))>{{ $filterValueNameByLang($value) }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <select class="form-control"
                                                                name="variations[{{ $index }}][filter_values][{{ $filterId }}]"
                                                                data-choices>
                                                            <option value="">{{ __('Select') }}</option>
                                                            @foreach(($filter?->values ?? collect()) as $value)
                                                                <option
                                                                    value="{{ $value->id }}" @selected(in_array((int) $value->id, $selectedIds, true))>{{ $filterValueNameByLang($value) }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v{{ $index }}-tab-media" role="tabpanel">
                            <div class="p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">{{ __('Variation Gallery') }}</div>
                                    <div
                                        class="text-muted fs-12">{{ __('Drag to reorder. Select one main image.') }}</div>
                                </div>

                                <input type="file"
                                       class="form-control js-media-input"
                                       name="variations[{{ $index }}][media_files][]"
                                       accept="image/*"
                                       multiple
                                       data-variation="{{ $index }}">

                                <div class="mt-3 row g-3 variation-media-list" data-variation-media-list="{{ $index }}">
                                    @foreach($mediaItems as $mIndex => $media)
                                        @php
                                            $mediaId = (int) ($media->id ?? 0);
                                            $img = (string) ($media->url ?? '');
                                            $isMainImg = (int) ($media->is_main ?? 0) === 1;
                                            $sortOrderM = (int) ($media->sort_order ?? $mIndex);
                                            $isDeleted = (int) ($media->_delete ?? 0) === 1;
                                        @endphp

                                        <div
                                            class="col-6 col-sm-4 col-md-3 col-xl-2 variation-media-item {{ $isDeleted ? 'd-none' : '' }}"
                                            draggable="true">
                                            <div
                                                class="border rounded p-2 h-100 {{ $isMainImg ? 'border-primary' : '' }}">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <span class="badge bg-light text-dark"
                                                                  style="cursor: grab;">
                                                                <i class="ri-drag-move-2-line"></i>
                                                            </span>

                                                    <button type="button"
                                                            class="btn btn-sm btn-soft-danger js-media-delete"
                                                            title="{{ __('Delete') }}">
                                                        <i class="ri-delete-bin-6-line"></i>
                                                    </button>
                                                </div>

                                                <img src="{{ $img }}" alt="" class="img-fluid rounded"
                                                     style="width: 100%; height: 90px; object-fit: cover;">

                                                <input type="hidden"
                                                       name="variations[{{ $index }}][media_existing][{{ $mIndex }}][id]"
                                                       value="{{ $mediaId }}">
                                                <input type="hidden" class="js-media-sort"
                                                       name="variations[{{ $index }}][media_existing][{{ $mIndex }}][sort_order]"
                                                       value="{{ $sortOrderM }}">
                                                <input type="hidden" class="js-media-is-main"
                                                       name="variations[{{ $index }}][media_existing][{{ $mIndex }}][is_main]"
                                                       value="{{ $isMainImg ? 1 : 0 }}">
                                                <input type="hidden" class="js-media-delete-flag"
                                                       name="variations[{{ $index }}][media_existing][{{ $mIndex }}][_delete]"
                                                       value="{{ $isDeleted ? 1 : 0 }}">

                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input js-media-main"
                                                               type="radio"
                                                               name="variation_main_radio_{{ $index }}"
                                                            @checked($isMainImg)>
                                                        <label
                                                            class="form-check-label">{{ __('Make main image') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 row g-3 js-new-media-list" data-new-media-list="{{ $index }}"></div>

                                <div class="form-text mt-2">
                                    {{ __('Deleted images will be removed after saving.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="alert alert-info mt-3 mb-0">
            {{ __('Variations are required. Add at least one variation before saving.') }}
        </div>
    </div>
</div>
