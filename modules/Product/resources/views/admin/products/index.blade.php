@extends('admin.layouts.app')

@section('title', __('Products'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Products') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('Products') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $addParams = [];
                if (!empty($filters['main_category_id'])) {
                    $addParams['main_category_id'] = $filters['main_category_id'];
                }

                $locale = (string) app()->getLocale();
                $language = \App\Models\Language::query()->where('code', $locale)->first();
                $languageId = $language ? (int) $language->id : null;
            @endphp

            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        @include('product::admin.products.partials.filters', [
                            'filters' => $filters,
                            'categoriesFlat' => $categoriesFlat,
                            'labels' => $labels,
                        ])

                        @can('product.create')
                            <a href="{{ route('admin.product.products.create', $addParams) }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i>{{ __('Add Product') }}
                            </a>
                        @endcan
                    </div>

                    @if($selectedCategory)
                        @php
                            $selectedName = $selectedCategory->translations->firstWhere('locale', $locale)?->name
                                ?? $selectedCategory->translations->first()?->name
                                ?? ('#' . $selectedCategory->id);
                        @endphp
                        <div class="mt-3 alert alert-info mb-0">
                            {{ __('Filtered by category:') }} <strong>{{ $selectedName }}</strong>
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
                                <th style="width:90px;">{{ __('Image') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th style="width:220px;">{{ __('Main category') }}</th>
                                <th style="width:120px;">{{ __('Stock') }}</th>
                                <th style="width:120px;">{{ __('Status') }}</th>
                                <th style="width:220px;">{{ __('Published at') }}</th>
                                <th class="text-end" style="width:140px;">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($products as $product)
                                @php
                                    $defaultVariation = $product->variations->sortBy('id')->first();

                                    $t = $defaultVariation?->translations?->firstWhere('language_id', $languageId)
                                        ?? $defaultVariation?->translations?->first();

                                    $name = $t?->name ?: ('#' . $product->id);
                                    $stock = $defaultVariation?->stock ?: '-';
                                    $slug = (string) ($t?->slug ?? '');

                                    $mainImage = $defaultVariation?->media?->firstWhere('is_main', true)
                                        ?? $defaultVariation?->media?->sortBy('sort_order')->first();

                                    $imageUrl = (string) ($mainImage?->url ?? '');

                                    $cat = $product->mainCategory;
                                    $catName = $cat?->translations?->firstWhere('locale', $locale)?->name
                                        ?? $cat?->translations?->first()?->name
                                        ?? '-';
                                @endphp
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        @if($imageUrl !== '')
                                            <img
                                                src="{{ $imageUrl }}"
                                                alt="{{ $name }}"
                                                class="rounded border object-fit-cover"
                                                style="width:56px; height:56px;"
                                            >
                                        @else
                                            <div class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted"
                                                 style="width:56px; height:56px;">
                                                <i class="ri-image-line fs-18"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $name }}</div>
                                        <div class="text-muted fs-12">{{ $slug }}</div>
                                    </td>
                                    <td>{{ $catName }}</td>
                                    <td>{{ $stock }}</td>

                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($product->status) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($product->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $product->published_at ? $product->published_at->format('Y-m-d H:i') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('product.edit')
                                                <a href="{{ route('admin.product.products.edit', $product) }}" class="btn btn-sm btn-primary">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            @endcan

                                            @can('product.delete')
                                                <form action="{{ route('admin.product.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
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
                                        {{ __('No products found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
