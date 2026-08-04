@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ __('Favorites statistics') }}</h4>
                <a href="{{ route('admin.favorites.index') }}" class="btn btn-light">{{ __('Back') }}</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form class="row g-3" method="GET" action="{{ route('admin.favorites.stats') }}">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Days') }}</label>
                            <input type="number" class="form-control" name="days" value="{{ $days }}" min="7" max="365">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-refresh-line me-1"></i> {{ __('Refresh') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Total favorites') }}</p>
                            <h4 class="mb-0">{{ $total }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Guest favorites') }}</p>
                            <h4 class="mb-0">{{ $guestTotal }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Unique customers') }}</p>
                            <h4 class="mb-0">{{ $uniqueCustomers }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Unique variations') }}</p>
                            <h4 class="mb-0">{{ $uniqueVariations }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Top variations') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Variation') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-end">{{ __('Count') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($topVariations as $row)
                                        @php
                                            $variation = $row->variation;

                                            $variationTranslation = $variation?->translations?->firstWhere('language_id', $languageId)
                                                ?? $variation?->translations?->first();

                                            $variationName = $variationTranslation?->name ?: ('#' . $row->product_variation_id);
                                            $productId = $variation?->product_id;
                                            $editUrl = $productId ? route('admin.product.products.edit', $productId) : null;
                                        @endphp

                                        <tr>
                                            <td>
                                                @if ($editUrl)
                                                    <a href="{{ $editUrl }}" class="fw-semibold">{{ $variationName }}</a>
                                                @else
                                                    <span class="fw-semibold">{{ $variationName }}</span>
                                                @endif

                                                @if ($variation?->sku)
                                                    <div class="text-muted">SKU: {{ $variation->sku }}</div>
                                                @endif

                                                <div class="text-muted">ID: {{ $row->product_variation_id }}</div>
                                            </td>

                                            <td>
                                                @if ($productId)
                                                    @if ($editUrl)
                                                        <a href="{{ $editUrl }}" class="fw-semibold">#{{ $productId }}</a>
                                                    @else
                                                        <span class="fw-semibold">#{{ $productId }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td class="fw-semibold text-end">{{ $row->cnt }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">—</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Top customers') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Customer') }}</th>
                                        <th class="text-end">{{ __('Count') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($topCustomers as $row)
                                        <tr>
                                            <td>
                                                @if ($row->customer)
                                                    {{ $row->customer->name }} {{ $row->customer->surname }}
                                                    <div class="text-muted">{{ $row->customer->email }}</div>
                                                    <div class="text-muted">ID: {{ $row->customer->id }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="fw-semibold text-end">{{ $row->cnt }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">—</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Daily favorites trend') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>{{ __('Day') }}</th>
                                <th class="text-end">{{ __('Count') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($daily as $row)
                                <tr>
                                    <td>{{ $row->day }}</td>
                                    <td class="fw-semibold text-end">{{ $row->cnt }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">—</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
