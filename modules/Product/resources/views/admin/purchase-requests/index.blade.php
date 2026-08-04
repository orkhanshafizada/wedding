@extends('admin.layouts.app')

@section('title', __('Purchase Requests'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Purchase Requests') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ __('Purchase Requests') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $locale = (string) app()->getLocale();
                $language = \App\Models\Language::query()->where('code', $locale)->first();
                $languageId = $language ? (int) $language->id : null;

                $nameByLocale = function ($model) use ($locale) {
                    return $model?->translations?->firstWhere('locale', $locale)?->name
                        ?? $model?->translations?->first()?->name
                        ?? ($model ? ('#' . $model->id) : '-');
                };

                $categoryOptionLabel = function ($item) use ($nameByLocale) {
                    $category = $item['model'];
                    $depth = (int) $item['depth'];
                    $parents = collect($item['ancestors'] ?? [])->map(fn ($parent) => $nameByLocale($parent))->implode(' → ');
                    $prefix = $depth > 0 ? str_repeat('-', $depth) . ' ' : '';

                    return $parents !== '' ? $prefix . $nameByLocale($category) . ' (' . $parents . ')' : $nameByLocale($category);
                };
            @endphp

            <div class="card">
                <div class="card-header border-0">
                    <form method="GET" action="{{ route('admin.product.purchase_requests.index') }}" class="row g-2 align-items-end">
                        <div class="col-lg-3">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="{{ __('Search by customer, phone, product, SKU') }}">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-control" data-choices>
                                <option value="">{{ __('All') }}</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ __('Main category') }}</label>
                            <select name="main_category_id" class="form-control" data-choices data-choices-search-enabled="true">
                                <option value="">{{ __('All') }}</option>
                                @foreach($categoriesFlat as $categoryItem)
                                    <option value="{{ $categoryItem['id'] }}" @selected((string) ($filters['main_category_id'] ?? '') === (string) $categoryItem['id'])>
                                        {{ $categoryOptionLabel($categoryItem) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ __('Date from') }}</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ __('Date to') }}</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-lg-1">
                            <label class="form-label">{{ __('Limit') }}</label>
                            <select name="per_page" class="form-control">
                                @foreach([10, 20, 50, 100] as $perPage)
                                    <option value="{{ $perPage }}" @selected((string) ($filters['per_page'] ?? '20') === (string) $perPage)>{{ $perPage }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line align-bottom me-1"></i>{{ __('Filter') }}
                            </button>

                            <a href="{{ route('admin.product.purchase_requests.index') }}" class="btn btn-light">
                                <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Reset') }}
                            </a>
                        </div>
                    </form>

                    @if($selectedCategory)
                        <div class="mt-3 alert alert-info mb-0">
                            {{ __('Filtered by category:') }} <strong>{{ $nameByLocale($selectedCategory) }}</strong>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    @include('admin.shared.alerts')

                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width:80px;">#</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th style="width:120px;">{{ __('Quantity') }}</th>
                                <th style="width:130px;">{{ __('Status') }}</th>
                                <th style="width:170px;">{{ __('Created at') }}</th>
                                <th class="text-end" style="width:140px;">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($purchaseRequests as $purchaseRequest)
                                @php
                                    $variation = $purchaseRequest->variation;
                                    $translation = $variation?->translations?->firstWhere('language_id', $languageId)
                                        ?? $variation?->translations?->first();

                                    $productName = $translation?->name ?: ('#' . $purchaseRequest->product_variation_id);
                                    $productMeta = collect([$variation?->sku, $variation?->model])
                                        ->filter(fn ($value) => filled($value))
                                        ->implode(' / ');

                                    $statusEnum = $purchaseRequest->statusEnum();

                                    $customerName = $purchaseRequest->customer
                                        ? trim((string) $purchaseRequest->customer->name . ' ' . (string) $purchaseRequest->customer->surname)
                                        : __('Guest');
                                @endphp

                                <tr>
                                    <td>{{ $purchaseRequest->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $purchaseRequest->fullname }}</div>
                                        <div class="text-muted fs-12">{{ $customerName }}</div>
                                    </td>
                                    <td>{{ $purchaseRequest->phone }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $productName }}</div>
                                        <div class="text-muted fs-12">{{ $productMeta !== '' ? $productMeta : '-' }}</div>
                                    </td>
                                    <td>{{ $purchaseRequest->quantity }}</td>
                                    <td>
                                        <span class="badge {{ $statusEnum->badgeClass() }}">
                                            {{ $statusEnum->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('product.purchase_request.view')
                                                <a href="{{ route('admin.product.purchase_requests.show', $purchaseRequest) }}" class="btn btn-sm btn-primary">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            @endcan

                                            @can('product.purchase_request.delete')
                                                <form action="{{ route('admin.product.purchase_requests.destroy', $purchaseRequest) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        {{ __('No purchase requests found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $purchaseRequests->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
