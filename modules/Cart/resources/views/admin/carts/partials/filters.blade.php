<form method="GET" action="{{ route('admin.cart.carts.index') }}">
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title mb-0">{{ __('Filters') }}</h5>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-3-line me-1"></i>{{ __('Apply') }}
                    </button>

                    <a href="{{ route('admin.cart.carts.index') }}" class="btn btn-light">
                        <i class="ri-refresh-line me-1"></i>{{ __('Reset') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="{{ __('ID / Token / User / Promo / Product / Category') }}"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Status') }}</label>
                    @php($status = (string)($filters['status'] ?? ''))
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                        <option value="merged" @selected($status === 'merged')>{{ __('Merged') }}</option>
                        <option value="expired" @selected($status === 'expired')>{{ __('Expired') }}</option>
                        <option value="converted" @selected($status === 'converted')>{{ __('Converted') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('User') }}</label>
                    @php($hasUser = (string)($filters['has_user'] ?? ''))
                    <select name="has_user" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="1" @selected($hasUser === '1')>{{ __('Users only') }}</option>
                        <option value="0" @selected($hasUser === '0')>{{ __('Guests only') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Promo code') }}</label>
                    <input
                        type="text"
                        name="promo"
                        class="form-control"
                        value="{{ $filters['promo'] ?? '' }}"
                        placeholder="{{ __('Code') }}"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Product') }}</label>
                    <select name="product_id" id="cart-filter-product" class="form-select">
                        @if(!empty($selectedProduct))
                            <option value="{{ $selectedProduct['id'] }}" selected>{{ $selectedProduct['text'] }}</option>
                        @endif
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select name="category_id" id="cart-filter-category" class="form-select">
                        @if(!empty($selectedCategory))
                            <option value="{{ $selectedCategory['id'] }}" selected>{{ $selectedCategory['text'] }}</option>
                        @endif
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Variation ID') }}</label>
                    <input
                        type="number"
                        name="variation_id"
                        class="form-control"
                        value="{{ $filters['variation_id'] ?? '' }}"
                        placeholder="123"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Min total') }}</label>
                    <input
                        type="number"
                        step="0.01"
                        name="min_total"
                        class="form-control"
                        value="{{ $filters['min_total'] ?? '' }}"
                        placeholder="0.00"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Max total') }}</label>
                    <input
                        type="number"
                        step="0.01"
                        name="max_total"
                        class="form-control"
                        value="{{ $filters['max_total'] ?? '' }}"
                        placeholder="0.00"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Created from') }}</label>
                    <input
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="{{ $filters['date_from'] ?? '' }}"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Created to') }}</label>
                    <input
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="{{ $filters['date_to'] ?? '' }}"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('Per page') }}</label>
                    @php($perPage = (int)($filters['per_page'] ?? 20))
                    <select name="per_page" class="form-select">
                        <option value="20" @selected($perPage === 20)>20</option>
                        <option value="50" @selected($perPage === 50)>50</option>
                        <option value="100" @selected($perPage === 100)>100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            function initSelect2($el, url, placeholder) {
                if (!$el.length || typeof $el.select2 !== 'function') return;

                $el.select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: placeholder,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function (data) {
                            return data;
                        },
                        cache: true
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSelect2($('#cart-filter-product'), '{{ route('admin.cart.ajax.products') }}', '{{ __('Select product') }}');
                initSelect2($('#cart-filter-category'), '{{ route('admin.cart.ajax.categories') }}', '{{ __('Select category') }}');
            });
        })();
    </script>
@endpush
