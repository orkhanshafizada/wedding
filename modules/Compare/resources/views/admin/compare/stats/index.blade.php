@extends('admin.layouts.app')

@section('title')
    {{ __('Compare Statistics') }}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row align-items-center mb-3">
                <div class="col-sm-6">
                    <h4 class="mb-0">{{ __('Compare Statistics') }}</h4>
                    <div class="text-muted">{{ __('Admin paneldə compare üzrə statistika') }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-sm-end gap-2">
                        <input type="date" class="form-control" id="date_from" value="{{ $filters['from']->toDateString() }}">
                        <input type="date" class="form-control" id="date_to" value="{{ $filters['to']->toDateString() }}">
                        <input type="hidden" id="language_id" value="{{ (int) ($filters['language_id'] ?? 1) }}">
                        <select class="form-select" id="limit">
                            @foreach([10,20,50,100] as $itemLimit)
                                <option value="{{ $itemLimit }}" @selected($filters['limit'] === $itemLimit)>{{ $itemLimit }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="button" id="btnLoad">{{ __('Load') }}</button>
                    </div>
                </div>
            </div>

            <div class="row" id="overviewCards">
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Total Items') }}</div><h4 class="mb-0" id="ov_total_items">0</h4></div></div></div>
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Customer Items') }}</div><h4 class="mb-0" id="ov_customer_items">0</h4></div></div></div>
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Guest Items') }}</div><h4 class="mb-0" id="ov_guest_items">0</h4></div></div></div>
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Unique Customers') }}</div><h4 class="mb-0" id="ov_unique_customers">0</h4></div></div></div>
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Unique Guest Tokens') }}</div><h4 class="mb-0" id="ov_unique_tokens">0</h4></div></div></div>
                <div class="col-xl-2 col-md-4"><div class="card"><div class="card-body"><div class="text-muted">{{ __('Unique Variations') }}</div><h4 class="mb-0" id="ov_unique_variations">0</h4></div></div></div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">{{ __('Top Variations') }}</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px">#</th>
                                        <th>{{ __('Variation') }}</th>
                                        <th style="width: 120px" class="text-end">{{ __('Count') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="topVariationsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">{{ __('Top Products') }}</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px">#</th>
                                        <th>{{ __('Product') }}</th>
                                        <th style="width: 120px" class="text-end">{{ __('Count') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="topProductsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const routeData = @json(route('admin.compare.stats.data'));
        const productEditBase = @json(\Illuminate\Support\Facades\Route::has('admin.product.products.edit') ? route('admin.product.products.edit', ['product' => '__ID__']) : null);

        function esc(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value).replace(/[&<>"']/g, function (match) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[match];
            });
        }

        function productEditUrl(productId) {
            if (!productEditBase || !productId) {
                return null;
            }

            return productEditBase.replace('__ID__', String(productId));
        }

        function pickTranslationName(translations) {
            if (!Array.isArray(translations) || translations.length === 0) {
                return '';
            }

            return translations[0].name || translations[0].title || '';
        }

        function productName(rowProduct, rowVariation) {
            if (rowVariation) {
                const variationLabel = pickTranslationName(rowVariation.translations);

                if (variationLabel) {
                    return variationLabel;
                }

                if (rowVariation.id) {
                    return `#${rowVariation.id}`;
                }
            }

            if (rowProduct && rowProduct.id) {
                return `#${rowProduct.id}`;
            }

            return '';
        }

        function variationName(variation) {
            if (!variation) {
                return '';
            }

            const variationLabel = pickTranslationName(variation.translations);

            if (variationLabel) {
                return variationLabel;
            }

            return variation.id ? `#${variation.id}` : '';
        }

        function categoryName(categoryTranslations) {
            if (!Array.isArray(categoryTranslations) || categoryTranslations.length === 0) {
                return '';
            }

            return categoryTranslations[0].name || categoryTranslations[0].title || '';
        }

        function linkOrText(label, url) {
            if (!url) {
                return `<div class="fw-medium">${esc(label)}</div>`;
            }

            return `<a class="fw-medium text-decoration-none" href="${esc(url)}">${esc(label)}</a>`;
        }

        async function loadStats() {
            const params = new URLSearchParams({
                date_from: document.getElementById('date_from').value,
                date_to: document.getElementById('date_to').value,
                limit: document.getElementById('limit').value,
                language_id: document.getElementById('language_id').value
            });

            const response = await fetch(`${routeData}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await response.json();

            if (!response.ok || json.error) {
                alert(json.message || 'Error');
                return;
            }

            const overview = json.data.overview || {};

            document.getElementById('ov_total_items').textContent = overview.total_items ?? 0;
            document.getElementById('ov_customer_items').textContent = overview.customer_items ?? 0;
            document.getElementById('ov_guest_items').textContent = overview.guest_items ?? 0;
            document.getElementById('ov_unique_customers').textContent = overview.unique_customers ?? 0;
            document.getElementById('ov_unique_tokens').textContent = overview.unique_guest_tokens ?? 0;
            document.getElementById('ov_unique_variations').textContent = overview.unique_variations ?? 0;

            const topVariationsBody = document.getElementById('topVariationsBody');
            topVariationsBody.innerHTML = '';

            (json.data.top_variations || []).forEach(function (row, index) {
                const variation = row.variation;
                const variationLabel = variationName(variation);
                const product = variation?.product || null;
                const productUrl = product ? productEditUrl(product.id) : null;

                topVariationsBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            ${linkOrText(variationLabel, productUrl)}
                        </td>
                        <td class="text-end fw-semibold">${esc(row.total)}</td>
                    </tr>
                `);
            });

            const topProductsBody = document.getElementById('topProductsBody');
            topProductsBody.innerHTML = '';

            (json.data.top_products || []).forEach(function (row, index) {
                const product = row.product || null;
                const variation = row.variation || null;
                const label = productName(product, variation);
                const url = product ? productEditUrl(product.id) : null;
                const currentCategoryName = categoryName(row.category_translations || []);

                topProductsBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            ${linkOrText(label, url)}
                            ${currentCategoryName ? `<div class="text-muted small">${esc(currentCategoryName)}</div>` : ``}
                        </td>
                        <td class="text-end fw-semibold">${esc(row.total)}</td>
                    </tr>
                `);
            });
        }

        document.getElementById('btnLoad').addEventListener('click', loadStats);
        loadStats();
    </script>
@endpush
