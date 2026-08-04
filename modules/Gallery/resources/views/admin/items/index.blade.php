@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="{{ route('admin.gallery.index', $menu) }}" class="btn btn-soft-secondary btn-sm">
                            <i class="ri-arrow-left-line align-bottom"></i>
                            <span>{{ __('Back to albums') }}</span>
                        </a>
                        <span class="badge bg-primary-subtle text-primary">
                            {{ str_replace('_', ' ', ucfirst($menuType)) }}
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $album->name }}</h4>
                    <p class="text-muted mb-0">{{ __('Drag and drop cards to reorder items inside this album.') }}</p>
                </div>

                @can('gallery.create')
                    <a href="{{ route('admin.gallery.items.create', [$menu, $album]) }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom"></i>
                        <span>{{ __('Add item') }}</span>
                    </a>
                @endcan
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($items->isEmpty())
                        <div class="text-center py-5">
                            <div class="avatar-md mx-auto mb-3">
                                <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                    <i class="ri-folder-open-line"></i>
                                </div>
                            </div>
                            <h5 class="mb-1">{{ __('No items found') }}</h5>
                            <p class="text-muted mb-0">{{ __('Create the first item for this album to start building the gallery.') }}</p>
                        </div>
                    @else
                        <div class="row g-4" id="sortable-items">
                            @foreach($items as $item)
                                <div class="col-12 col-md-6 col-xl-4" data-id="{{ $item->id }}">
                                    <div class="card h-100 shadow-sm border">
                                        <div class="card-header bg-light-subtle border-bottom d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-light drag-handle" title="{{ __('Drag to reorder') }}">
                                                    <i class="ri-draggable"></i>
                                                </button>
                                                <span class="fw-semibold text-truncate">{{ $item->title ?: __('Untitled item') }}</span>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                @if($item->is_active)
                                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                                @endif

                                                @if($menuType === 'files' && $item->publication)
                                                    <span class="badge bg-info-subtle text-info">{{ __('Publication') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="rounded-3 border bg-light-subtle d-flex align-items-center justify-content-center overflow-hidden mb-3" style="height: 240px;">
                                                @if($menuType === 'photo_gallery' && $item->file_path)
                                                    <img
                                                        src="{{ asset('storage/' . $item->file_path) }}"
                                                        alt="{{ $item->title }}"
                                                        class="w-100 h-100"
                                                        style="object-fit: cover;"
                                                    >
                                                @elseif($menuType === 'video_gallery' && $item->file_path)
                                                    <video
                                                        controls
                                                        preload="metadata"
                                                        class="w-100 h-100"
                                                        style="object-fit: cover;"
                                                    >
                                                        <source src="{{ asset('storage/' . $item->file_path) }}">
                                                    </video>
                                                @elseif($menuType === 'files' && $item->file_path)
                                                    <div class="text-center px-4">
                                                        <i class="ri-file-text-line display-5 text-danger"></i>
                                                        <div class="fw-medium mt-2">{{ basename((string) $item->file_path) }}</div>
                                                    </div>
                                                @else
                                                    <div class="text-center px-4">
                                                        <i class="ri-image-line display-5 text-muted"></i>
                                                        <div class="text-muted mt-2">{{ __('No preview available') }}</div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <div class="text-muted small mb-1">{{ __('Description') }}</div>
                                                <div class="text-body">
                                                    {{ \Illuminate\Support\Str::limit($item->description ?: __('No description'), 120) }}
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2">
                                                @if($item->file_path)
                                                    <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="ri-external-link-line align-bottom"></i>
                                                        <span>{{ __('Open file') }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex gap-2">
                                                @can('gallery.edit')
                                                    <a href="{{ route('admin.gallery.items.edit', [$menu, $album, $item]) }}" class="btn btn-primary btn-sm w-100">
                                                        <i class="ri-edit-line align-bottom"></i>
                                                        <span>{{ __('Edit') }}</span>
                                                    </a>
                                                @endcan

                                                @can('gallery.delete')
                                                    <button
                                                        type="button"
                                                        class="btn btn-danger btn-sm w-100 delete-item"
                                                        data-url="{{ route('admin.gallery.items.destroy', [$menu, $album, $item]) }}"
                                                    >
                                                        <i class="ri-delete-bin-line align-bottom"></i>
                                                        <span>{{ __('Delete') }}</span>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('sortable-items');

            if (container) {
                new Sortable(container, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function () {
                        const order = Array.from(container.querySelectorAll('[data-id]'))
                            .map(function (element) {
                                return element.getAttribute('data-id');
                            })
                            .filter(Boolean);

                        fetch('{{ route('admin.gallery.items.update-order', [$menu, $album]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        });
                    }
                });
            }

            document.querySelectorAll('.delete-item').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!confirm('{{ __('Are you sure you want to delete this item?') }}')) {
                        return;
                    }

                    fetch(this.dataset.url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (response) {
                            if (response.success) {
                                window.location.reload();
                                return;
                            }

                            alert(response.message || '{{ __('Delete failed.') }}');
                        })
                        .catch(function () {
                            alert('{{ __('Delete failed.') }}');
                        });
                });
            });
        });
    </script>
@endpush
