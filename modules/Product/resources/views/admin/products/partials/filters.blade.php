<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Filters') }}</h5>

        <a href="{{ route('admin.product.products.index') }}" class="btn btn-sm btn-soft-secondary">
            <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Reset') }}
        </a>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.product.products.index') }}">
            <div class="row g-3">

                <div class="col-xl-4 col-lg-6">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select name="main_category_id"
                            class="form-control"
                            data-choices
                            data-choices-search-enabled="true">
                        <option value="">{{ __('All categories') }}</option>

                        @php
                            $allowedCategoryIds = collect($filters['allowed_category_ids'] ?? [])
                                ->map(fn ($id): int => (int) $id)
                                ->filter(fn (int $id): bool => $id > 0)
                                ->unique()
                                ->values()
                                ->all();
                        @endphp

                        @foreach($categoriesFlat as $row)
                            @php
                                $category = $row['model'];
                                $categoryId = (int) $category->id;

                                if ($allowedCategoryIds !== [] && ! in_array($categoryId, $allowedCategoryIds, true)) {
                                    continue;
                                }

                                $depth = (int) $row['depth'];
                                $ancestors = collect($row['ancestors'] ?? []);

                                $catName = $category->translations->firstWhere('locale', app()->getLocale())?->name
                                    ?? $category->translations->first()?->name
                                    ?? ('#' . $category->id);

                                $ancestorNames = $ancestors
                                    ->filter(function ($ancestor) use ($allowedCategoryIds): bool {
                                        if ($allowedCategoryIds === []) {
                                            return true;
                                        }

                                        return in_array((int) $ancestor->id, $allowedCategoryIds, true);
                                    })
                                    ->map(function ($ancestor) {
                                        return $ancestor->translations->firstWhere('locale', app()->getLocale())?->name
                                            ?? $ancestor->translations->first()?->name
                                            ?? ('#' . $ancestor->id);
                                    })
                                    ->values()
                                    ->all();

                                if ($depth <= 0 || $ancestorNames === []) {
                                    $optionLabel = $catName;
                                } else {
                                    $optionLabel = str_repeat('-', $depth) . ' ' . $catName . ' (' . implode('->', $ancestorNames) . ')';
                                }
                            @endphp

                            <option value="{{ $category->id }}" @selected((string) ($filters['main_category_id'] ?? '') === (string) $category->id)>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-4 col-lg-6">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text"
                           name="q"
                           value="{{ $filters['q'] ?? '' }}"
                           class="form-control"
                           placeholder="{{ __('Search by everything') }}">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-control" data-choices>
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="1" @selected(($filters['status'] ?? '') == 1)>{{ \App\Enums\StatusEnum::ACTIVE }}</option>
                        <option value="0" @selected(($filters['status'] ?? '') == 0)>{{ \App\Enums\StatusEnum::INACTIVE }}</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Label') }}</label>
                    <select name="label_id" class="form-control" data-choices>
                        <option value="">{{ __('All labels') }}</option>
                        @foreach($labels as $label)
                            <option value="{{ $label->id }}" @selected((string) ($filters['label_id'] ?? '') === (string) $label->id)>
                                {{ $label->name ?? ('#' . $label->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Discount') }}</label>
                    <select name="has_discount" class="form-control" data-choices>
                        <option value="">{{ __('Any') }}</option>
                        <option value="1" @selected(($filters['has_discount'] ?? '') === '1')>{{ __('Has discount') }}</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Stock') }}</label>
                    <select name="in_stock" class="form-control" data-choices>
                        <option value="">{{ __('Any') }}</option>
                        <option value="1" @selected(($filters['in_stock'] ?? '') === '1')>{{ __('In stock') }}</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Images') }}</label>
                    <select name="has_images" class="form-control" data-choices>
                        <option value="">{{ __('Any') }}</option>
                        <option value="1" @selected(($filters['has_images'] ?? '') === '1')>{{ __('Has images') }}</option>
                        <option value="0" @selected(($filters['has_images'] ?? '') === '0')>{{ __('No images') }}</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Stock count') }}</label>
                    <input type="text"
                           name="stock"
                           value="{{ $filters['stock'] ?? null }}"
                           class="form-control">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Min price') }}</label>
                    <input type="number"
                           step="0.01"
                           name="min_price"
                           value="{{ $filters['min_price'] ?? '' }}"
                           class="form-control"
                           placeholder="0.00">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Max price') }}</label>
                    <input type="number"
                           step="0.01"
                           name="max_price"
                           value="{{ $filters['max_price'] ?? '' }}"
                           class="form-control"
                           placeholder="0.00">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Published from') }}</label>
                    <input type="date"
                           name="published_from"
                           value="{{ $filters['published_from'] ?? '' }}"
                           class="form-control">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Published to') }}</label>
                    <input type="date"
                           name="published_to"
                           value="{{ $filters['published_to'] ?? '' }}"
                           class="form-control">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Sort') }}</label>
                    <select name="sort" class="form-control" data-choices>
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('Newest') }}</option>
                        <option value="published_newest" @selected(($filters['sort'] ?? '') === 'published_newest')>{{ __('Published: Newest') }}</option>
                        <option value="published_oldest" @selected(($filters['sort'] ?? '') === 'published_oldest')>{{ __('Published: Oldest') }}</option>
                        <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>{{ __('Name: A-Z') }}</option>
                        <option value="name_desc" @selected(($filters['sort'] ?? '') === 'name_desc')>{{ __('Name: Z-A') }}</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label class="form-label">{{ __('Per page') }}</label>
                    <select name="per_page" class="form-control" data-choices>
                        @foreach([20, 50, 100] as $perPage)
                            <option value="{{ $perPage }}" @selected((string) ($filters['per_page'] ?? '20') === (string) $perPage)>{{ $perPage }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-3-line align-bottom me-1"></i>{{ __('Apply') }}
                    </button>

                    <a href="{{ route('admin.product.products.index') }}" class="btn btn-light">
                        <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Reset') }}
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>
