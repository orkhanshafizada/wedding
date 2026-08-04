@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Favorites') }}</h4>
                        <a href="{{ route('admin.favorites.stats') }}" class="btn btn-primary">
                            <i class="ri-bar-chart-2-line me-1"></i> {{ __('Statistics') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Total') }}</p>
                            <h4 class="mb-0">{{ $summary['total'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Guests') }}</p>
                            <h4 class="mb-0">{{ $summary['guest_total'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Unique customers') }}</p>
                            <h4 class="mb-0">{{ $summary['unique_customers'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Unique variations') }}</p>
                            <h4 class="mb-0">{{ $summary['unique_variations'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1">{{ __('Today') }}</p>
                            <h4 class="mb-0">{{ $summary['today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form class="row g-3" method="GET" action="{{ route('admin.favorites.index') }}">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="{{ $filters['search'] }}"
                                placeholder="{{ __('Favorite id, variation id, customer name/email, sku, variation name') }}"
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('Customer ID') }}</label>
                            <input type="number" name="customer_id" class="form-control" value="{{ $filters['customer_id'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('Customer type') }}</label>
                            <select name="customer_type" class="form-select">
                                <option value="">{{ __('All') }}</option>
                                <option value="customer" @selected($filters['customer_type'] === 'customer')>{{ __('Registered') }}</option>
                                <option value="guest" @selected($filters['customer_type'] === 'guest')>{{ __('Guest') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('Product ID') }}</label>
                            <input type="number" name="product_id" class="form-control" value="{{ $filters['product_id'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('SKU') }}</label>
                            <input type="text" name="sku" class="form-control" value="{{ $filters['sku'] }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('Variation name') }}</label>
                            <input
                                type="text"
                                name="variation_name"
                                class="form-control"
                                value="{{ $filters['variation_name'] }}"
                                placeholder="{{ __('e.g. iPhone 15 Pro 256GB') }}"
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('Per page') }}</label>
                            <select name="per_page" class="form-select">
                                @foreach ([20, 50, 100] as $perPage)
                                    <option value="{{ $perPage }}" @selected((int) request('per_page', 20) === $perPage)>{{ $perPage }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Date from') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Date to') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line me-1"></i> {{ __('Filter') }}
                            </button>
                            <a class="btn btn-light" href="{{ route('admin.favorites.index') }}">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Variation') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Created at') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($favorites as $favorite)
                                @php
                                    $variation = $favorite->variation;
                                    $product = $variation?->product;

                                    $variationTranslation = $variation?->translations?->firstWhere('language_id', $languageId)
                                        ?? $variation?->translations?->first();

                                    $variationName = $variationTranslation?->name ?: ('#' . $favorite->product_variation_id);
                                    $productId = $product?->uuid;
                                    $editUrl = $productId ? route('admin.product.products.edit', $productId) : null;
                                @endphp

                                <tr>
                                    <td>{{ $favorite->id }}</td>

                                    <td>
                                        @if ($favorite->customer)
                                            <div class="fw-semibold">
                                                {{ $favorite->customer->name }} {{ $favorite->customer->surname }}
                                            </div>
                                            <div class="text-muted">{{ $favorite->customer->email }}</div>
                                            <div class="text-muted">ID: {{ $favorite->customer->id }}</div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Guest') }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($editUrl)
                                            <a href="{{ $editUrl }}" class="fw-semibold">{{ $variationName }}</a>
                                        @else
                                            <span class="fw-semibold">{{ $variationName }}</span>
                                        @endif

                                        @if ($variation)
                                            <div class="text-muted">
                                                @if ($variation->sku)
                                                    SKU: {{ $variation->sku }} ·
                                                @endif
                                                {{ __('Price') }}: {{ $variation->price }}
                                                @if ($variation->discount_price)
                                                    / {{ __('Discount') }}: {{ $variation->discount_price }}
                                                @endif
                                            </div>
                                            <div class="text-muted">ID: {{ $variation->id }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($productId)
                                            @if ($editUrl)
                                                <a href="{{ $editUrl }}" class="fw-semibold">#{{ $productId }}</a>
                                            @else
                                                <span class="fw-semibold">#{{ $productId }}</span>
                                            @endif
                                            <div class="text-muted">{{ __('Status') }}: {{ $product?->status }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>{{ $favorite->created_at?->format('Y-m-d H:i') }}</td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.favorites.show', $favorite) }}" class="btn btn-sm btn-soft-primary">
                                            {{ __('View') }}
                                        </a>

                                        @can('favorites.delete')
                                            <form action="{{ route('admin.favorites.destroy', $favorite) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-soft-danger" type="submit">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">{{ __('No favorites found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $favorites->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
