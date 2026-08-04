@extends('admin.layouts.app')

@section('title', 'Product Filters')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-semibold">{{ __('Product Filters') }}</h5>

                        <div>
                            <a href="{{ route('admin.product.filters.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> {{ __('New Filter') }}
                            </a>
                        </div>
                    </div>

                    <div class="row mt-3 align-items-center">
                        <div class="col-md-5 col-lg-4">
                            <form action="{{ route('admin.product.filters.index') }}"
                                  method="GET"
                                  id="product-filter-search-form">
                                <div class="search-box">
                                    <input type="text"
                                           name="q"
                                           id="product-filter-search"
                                           class="form-control search"
                                           value="{{ $q ?? request('q') }}"
                                           placeholder="{{ __('Search filters...') }}"
                                           autocomplete="off">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-7 col-lg-8 d-flex align-items-center justify-content-md-end mt-2 mt-md-0">
                            <span class="text-muted">
                                {{ $filters->total() }} {{ __('records') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <i class="ri-drag-move-line"></i>
                                </th>
                                <th>ID</th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Color') }}</th>
                                <th>{{ __('Sidebar') }}</th>
                                <th>{{ __('Required') }}</th>
                                <th>{{ __('Clickable') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-filters">
                            @forelse ($filters as $filter)
                                @php
                                    $translation = $filter->translations->firstWhere('language.code', $adminLang)
                                        ?? $filter->translations->first();
                                @endphp

                                <tr data-id="{{ $filter->id }}" class="product-filter-row" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>

                                    <td>{{ $filter->id }}</td>

                                    <td>
                                        @if($filter->image)
                                            <img src="{{ asset('storage/' . $filter->image) }}"
                                                 alt="{{ $translation?->name ?? 'Filter image' }}"
                                                 style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ebec;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-medium">{{ $translation?->name ?? 'No translation' }}</div>

                                        @if($filter->tag)
                                            <small class="text-muted">{{ __('Tag') }}: {{ $filter->tag }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-info">{{ ucfirst((string) $filter->input_type) }}</span>
                                    </td>

                                    <td>
                                        @if($filter->is_color_filter)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($filter->show_in_sidebar)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($filter->is_required)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($filter->is_clickable)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.product.filters.values.index', $filter) }}"
                                           class="btn btn-sm btn-info"
                                           title="{{ __('Filter Values') }}">
                                            <i class="ri-list-check"></i>
                                        </a>

                                        <a href="{{ route('admin.product.filters.edit', $filter) }}"
                                           class="btn btn-sm btn-warning"
                                           title="{{ __('Edit') }}">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <form action="{{ route('admin.product.filters.destroy', $filter) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Delete this filter?') }}')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="{{ __('Delete') }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        {{ __('No filters found') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($filters->hasPages())
                        <div class="mt-3">
                            {{ $filters->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableList = document.getElementById('sortable-filters');
            const searchInput = document.getElementById('product-filter-search');
            const searchForm = document.getElementById('product-filter-search-form');
            let searchTimeout = null;

            if (searchInput && searchForm) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    searchTimeout = setTimeout(function() {
                        searchForm.submit();
                    }, 450);
                });
            }

            if (sortableList && typeof Sortable !== 'undefined') {
                new Sortable(sortableList, {
                    handle: '.drag-handle',
                    animation: 150,
                    draggable: '.product-filter-row',
                    onEnd: function() {
                        const rows = sortableList.querySelectorAll('.product-filter-row[data-id]');
                        const order = Array.from(rows).map(function(row, index) {
                            return {
                                id: row.getAttribute('data-id'),
                                sort_order: index
                            };
                        });

                        fetch('{{ route("admin.product.filters.update-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                order: order
                            })
                        })
                            .then(async function(response) {
                                const data = await response.json();

                                if (!response.ok || !data.success) {
                                    throw data;
                                }

                                return data;
                            })
                            .catch(function(error) {
                                console.error('Error updating order:', error);
                                window.location.reload();
                            });
                    }
                });
            }
        });
    </script>
@endpush
