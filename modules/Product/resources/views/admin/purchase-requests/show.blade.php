@extends('admin.layouts.app')

@section('title', __('Purchase Request Details'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Purchase Request Details') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.product.purchase_requests.index') }}">{{ __('Purchase Requests') }}</a>
                                </li>
                                <li class="breadcrumb-item active">#{{ $purchaseRequest->id }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $locale = (string) app()->getLocale();
                $language = \App\Models\Language::query()->where('code', $locale)->first();
                $languageId = $language ? (int) $language->id : null;

                $variation = $purchaseRequest->variation;
                $translation = $variation?->translations?->firstWhere('language_id', $languageId)
                    ?? $variation?->translations?->first();

                $productName = $translation?->name ?: ('#' . $purchaseRequest->product_variation_id);
                $statusEnum = $purchaseRequest->statusEnum();

                $mainMedia = $variation?->media?->firstWhere('is_main', true)
                    ?? $variation?->media?->sortBy('sort_order')->first();

                $imageUrl = (string) ($mainMedia?->url ?? '');

                $customerName = $purchaseRequest->customer
                    ? trim((string) $purchaseRequest->customer->name . ' ' . (string) $purchaseRequest->customer->surname)
                    : __('Guest');

                $category = $purchaseRequest->product?->mainCategory;
                $categoryName = $category?->translations?->firstWhere('locale', $locale)?->name
                    ?? $category?->translations?->first()?->name
                    ?? '-';
            @endphp

            @include('admin.shared.alerts')

            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('admin.product.purchase_requests.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>{{ __('Back') }}
                </a>

                <span class="badge fs-13 {{ $statusEnum->badgeClass() }}">{{ $statusEnum->label() }}</span>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Request Information') }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Full name') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->fullname }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Phone') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->phone }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Customer') }}</div>
                                    <div class="fw-semibold">{{ $customerName }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Guest token') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->guest_token ?: '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Quantity') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->quantity }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Created at') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('Y-m-d H:i') : '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Reviewed by') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->reviewer?->name ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted">{{ __('Reviewed at') }}</div>
                                    <div class="fw-semibold">{{ $purchaseRequest->reviewed_at ? $purchaseRequest->reviewed_at->format('Y-m-d H:i') : '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Product Information') }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-start">
                                @if($imageUrl !== '')
                                    <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="rounded border object-fit-cover" style="width:90px; height:90px;">
                                @else
                                    <div class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted" style="width:90px; height:90px;">
                                        <i class="ri-image-line fs-24"></i>
                                    </div>
                                @endif

                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $productName }}</h5>
                                    <div class="text-muted mb-2">{{ $translation?->slug ?: '-' }}</div>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('SKU') }}</div>
                                            <div class="fw-semibold">{{ $variation?->sku ?: '-' }}</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('Model') }}</div>
                                            <div class="fw-semibold">{{ $variation?->model ?: '-' }}</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('Main category') }}</div>
                                            <div class="fw-semibold">{{ $categoryName }}</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('Price') }}</div>
                                            <div class="fw-semibold">{{ $variation?->price ?? '-' }}</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('Discount price') }}</div>
                                            <div class="fw-semibold">{{ $variation?->discount_price ?? '-' }}</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="text-muted">{{ __('Stock') }}</div>
                                            <div class="fw-semibold">{{ $variation?->stock ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($variation?->filterValues?->isNotEmpty())
                                <hr>

                                <div class="row g-2">
                                    @foreach($variation->filterValues as $filterValue)
                                        @php
                                            $filterValueName = $filterValue->translations?->firstWhere('language_id', $languageId)?->name
                                                ?? $filterValue->translations?->first()?->name
                                                ?? ('#' . $filterValue->id);
                                        @endphp
                                        <div class="col-md-4">
                                            <span class="badge bg-light text-dark">{{ $filterValueName }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Admin Review') }}</h5>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.product.purchase_requests.update_status', $purchaseRequest) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror" data-choices>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $purchaseRequest->status) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Admin note') }}</label>
                                    <textarea name="admin_note" rows="6" class="form-control @error('admin_note') is-invalid @enderror">{{ old('admin_note', $purchaseRequest->admin_note) }}</textarea>
                                    @error('admin_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @can('product.purchase_request.edit')
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save Changes') }}
                                    </button>
                                @endcan
                            </form>
                        </div>
                    </div>

                    @can('product.purchase_request.delete')
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('admin.product.purchase_requests.destroy', $purchaseRequest) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="ri-delete-bin-line align-bottom me-1"></i>{{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>

        </div>
    </div>
@endsection
