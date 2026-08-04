@extends('admin.layouts.app')
@section('title', $filter->name . ' - ' . __('Filter Values'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ $filter->name }} - {{ __('Filter Values') }}</h4>
                <div>
                    <a href="{{ route('admin.product.filters.index') }}" class="btn btn-soft-secondary me-2">
                        <i class="ri-arrow-go-back-line align-bottom me-1"></i> {{ __('Back to Filters') }}
                    </a>
                    <a href="{{ route('admin.product.filters.values.create', $filter) }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> {{ __('New Value') }}
                    </a>
                </div>
            </div>

            <div id="filter-value-alert-container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5 col-lg-4">
                            <div class="search-box">
                                <input type="text"
                                       id="filter-value-search"
                                       class="form-control search"
                                       placeholder="{{ __('Search filter values...') }}"
                                       autocomplete="off">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-8 text-md-end">
                            <span class="text-muted" id="filter-value-count">
                                {{ $filter->values->count() }} {{ __('records') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <i class="ri-drag-move-line"></i>
                                </th>
                                <th>ID</th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Name') }}</th>
                                @if($filter->is_color_filter)
                                    <th>{{ __('Color') }}</th>
                                @endif
                                <th>{{ __('Show on Main Page') }}</th>
                                <th>{{ __('Show on Menu Detail') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-filter-values">
                            @forelse($filter->values as $value)
                                @php
                                    $translation = $value->translations->firstWhere('language.code', $adminLang)
                                                ?? $value->translations->first();

                                    $searchText = collect([
                                        $value->id,
                                        $translation?->name,
                                        $translation?->slug,
                                        $value->color,
                                        $value->show_on_main ? 'visible shown active enabled' : 'hidden inactive disabled',
                                    ])->filter()->implode(' ');
                                @endphp
                                <tr data-id="{{ $value->id }}"
                                    data-search="{{ Str::lower($searchText) }}"
                                    class="filter-value-row"
                                    style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $value->id }}</td>
                                    <td>
                                        @if($value->image)
                                            <img src="{{ asset('storage/' . $value->image) }}"
                                                 alt="{{ $translation?->name ?? 'Value image' }}"
                                                 style="width: 100px; object-fit: contain; border-radius: 6px; border: 1px solid #e9ebec;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $translation?->name ?? 'No translation' }}</td>

                                    @if($filter->is_color_filter)
                                        <td>
                                            @if($value->color)
                                                <span style="display: inline-block; width: 30px; height: 30px; background-color: {{ $value->color }}; border: 1px solid #ddd; border-radius: 4px;"></span>
                                                <small class="ms-1">{{ $value->color }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        <div class="form-check form-switch form-switch-md mb-0">
                                            <input type="checkbox"
                                                   class="form-check-input js-show-on-page-switch"
                                                   role="switch"
                                                   aria-label="{{ __('Show on Main Page') }}"
                                                   data-update-url="{{ route('admin.product.filters.values.show-on-page', [$filter, $value]) }}"
                                                @checked($value->show_on_main)>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="form-check form-switch form-switch-md mb-0">
                                            <input type="checkbox"
                                                   class="form-check-input js-show-on-menu_detail-switch"
                                                   role="switch"
                                                   aria-label="{{ __('Show on Menu Detail') }}"
                                                   data-update-url="{{ route('admin.product.filters.values.show-on-menu-detail', [$filter, $value]) }}"
                                                @checked($value->show_on_menu_detail)>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.product.filters.values.edit', [$filter, $value]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.product.filters.values.destroy', [$filter, $value]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete this value?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="filter-values-empty-row">
                                    <td colspan="{{ $filter->is_color_filter ? 7 : 6 }}" class="text-center text-muted py-4">
                                        {{ __('No values found') }}
                                    </td>
                                </tr>
                            @endforelse

                            @if($filter->values->isNotEmpty())
                                <tr id="filter-value-no-results-row" class="d-none">
                                    <td colspan="{{ $filter->is_color_filter ? 7 : 6 }}" class="text-center text-muted py-4">
                                        {{ __('No matching values found') }}
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableList = document.getElementById('sortable-filter-values');
            const searchInput = document.getElementById('filter-value-search');
            const countElement = document.getElementById('filter-value-count');
            const noResultsRow = document.getElementById('filter-value-no-results-row');
            const alertContainer = document.getElementById('filter-value-alert-container');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function getRows() {
                return Array.from(document.querySelectorAll('.filter-value-row'));
            }

            function showAlert(message, type) {
                if (!alertContainer) {
                    return;
                }

                alertContainer.innerHTML = [
                    '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">',
                    message,
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>',
                    '</div>'
                ].join('');
            }

            function updateVisibleCount() {
                if (!countElement) {
                    return;
                }

                const visibleCount = getRows().filter(function(row) {
                    return !row.classList.contains('d-none');
                }).length;

                countElement.textContent = visibleCount + ' {{ __('records') }}';
            }

            function applySearch() {
                const query = (searchInput?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                getRows().forEach(function(row) {
                    const searchText = row.getAttribute('data-search') || '';
                    const isVisible = query === '' || searchText.includes(query);

                    row.classList.toggle('d-none', !isVisible);

                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (noResultsRow) {
                    noResultsRow.classList.toggle('d-none', visibleCount !== 0);
                }

                updateVisibleCount();
            }

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }

            document.querySelectorAll('.js-show-on-page-switch').forEach(function(switchElement) {
                switchElement.addEventListener('change', function() {
                    const checkbox = this;
                    const previousValue = !checkbox.checked;
                    const updateUrl = checkbox.getAttribute('data-update-url');

                    checkbox.disabled = true;

                    fetch(updateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            show_on_main: checkbox.checked ? 1 : 0
                        })
                    })
                        .then(async function(response) {
                            const data = await response.json();

                            if (!response.ok || !data.success) {
                                throw data;
                            }

                            return data;
                        })
                        .then(function(data) {
                            showAlert(data.message || 'Filter value visibility was updated successfully.', 'success');
                        })
                        .catch(function(error) {
                            checkbox.checked = previousValue;
                            showAlert(error.message || 'Filter value visibility could not be updated.', 'danger');
                        })
                        .finally(function() {
                            checkbox.disabled = false;
                        });
                });
            });

            document.querySelectorAll('.js-show-on-menu_detail-switch').forEach(function(switchElement) {
                switchElement.addEventListener('change', function() {
                    const checkbox = this;
                    const previousValue = !checkbox.checked;
                    const updateUrl = checkbox.getAttribute('data-update-url');

                    checkbox.disabled = true;

                    fetch(updateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            show_on_menu_detail: checkbox.checked ? 1 : 0
                        })
                    })
                        .then(async function(response) {
                            const data = await response.json();

                            if (!response.ok || !data.success) {
                                throw data;
                            }

                            return data;
                        })
                        .then(function(data) {
                            showAlert(data.message || 'Filter value visibility was updated successfully.', 'success');
                        })
                        .catch(function(error) {
                            checkbox.checked = previousValue;
                            showAlert(error.message || 'Filter value visibility could not be updated.', 'danger');
                        })
                        .finally(function() {
                            checkbox.disabled = false;
                        });
                });
            });

            if (sortableList && typeof Sortable !== 'undefined') {
                new Sortable(sortableList, {
                    handle: '.drag-handle',
                    animation: 150,
                    draggable: '.filter-value-row',
                    onEnd: function() {
                        const rows = sortableList.querySelectorAll('.filter-value-row[data-id]');
                        const order = Array.from(rows).map(function(row, index) {
                            return {
                                id: row.getAttribute('data-id'),
                                sort_order: index
                            };
                        });

                        fetch('{{ route("admin.product.filters.values.update-order", $filter) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ order: order })
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

            updateVisibleCount();
        });
    </script>
@endpush
