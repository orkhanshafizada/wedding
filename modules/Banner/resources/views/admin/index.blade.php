@extends('admin.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">
                                    <i class="bx bx-home-alt"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Banners') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('admin.banner.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> {{ __('Add New Banner') }}
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" id="sortable-table">
                            <thead>
                            <tr>
                                <th width="50">{{ __('Sort') }}</th>
                                <th width="80">{{ __('Image') }}</th>
                                <th width="80">{{ __('Mobile') }}</th>
                                <th>{{ __('Position') }}</th>
                                <th>{{ __('Link') }}</th>
                                <th width="100">{{ __('Status') }}</th>
                                <th width="150">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-tbody">
                            @foreach($banners as $banner)
                                @php
                                    $translation = $banner->translation($adminLang);
                                @endphp
                                <tr data-id="{{ $banner->id }}">
                                    <td class="drag-handle" style="cursor: move;">
                                        <i class="bx bx-menu"></i>
                                    </td>
                                    <td>
                                        @if($translation?->image)
                                            <img src="{{ Storage::disk('public')->url($translation->image) }}" alt="Banner" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span class="text-muted">{{ __('No image') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($translation?->mobile_image)
                                            <img src="{{ Storage::disk('public')->url($translation->mobile_image) }}" alt="Banner Mobile" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span class="text-muted">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $banner->position_name }}</span>
                                    </td>
                                    <td>{{ Str::limit($translation?->link ?? '-', 50) }}</td>
                                    <td>
                                        @if($banner->is_active)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.banner.edit', $banner) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.banner.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $banners->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tbody = document.getElementById('sortable-tbody');

                if (!tbody) {
                    return;
                }

                Sortable.create(tbody, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function () {
                        const items = [];

                        tbody.querySelectorAll('tr').forEach((row, index) => {
                            items.push({
                                id: row.dataset.id,
                                sort_order: index
                            });
                        });

                        fetch('{{ route('admin.banner.update-order') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ items: items })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    toastr.success(data.message);
                                    return;
                                }

                                toastr.error('{{ __('Failed to update order') }}');
                            })
                            .catch(function () {
                                toastr.error('{{ __('Failed to update order') }}');
                            });
                    }
                });
            });
        </script>
    @endpush
@endsection
