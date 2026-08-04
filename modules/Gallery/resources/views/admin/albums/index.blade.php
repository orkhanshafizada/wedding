@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ $menu->name }} - {{ __('Albums') }}</h4>
                @can('gallery.create')
                    <a href="{{ route('admin.gallery.create', $menu) }}" class="btn btn-primary">
                        <i class="ri-add-line"></i> {{ __('Add New Album') }}
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
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th style="width: 50px;">{{ __('Sort') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Cover Image') }}</th>
                                <th>{{ __('Show Album') }}</th>
                                <th>{{ __('Items') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th style="width: 150px;">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-albums">
                            @forelse($albums as $album)
                                <tr data-id="{{ $album->id }}">
                                    <td class="drag-handle" style="cursor: move;">
                                        <i class="ri-drag-move-2-fill"></i>
                                    </td>
                                    <td>{{ $album->name }}</td>
                                    <td>
                                        @if($album->cover_image)
                                            <img src="{{ asset('storage/' . $album->cover_image) }}"
                                                 alt="{{ $album->name }}"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">{{ __('No image') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $album->show_album ? 'success' : 'secondary' }}">
                                            {{ $album->show_album ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.gallery.items.index', [$menu, $album]) }}"
                                           class="text-primary">
                                            {{ $album->items_count }} {{ __('items') }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $album->is_active ? 'success' : 'danger' }}">
                                            {{ $album->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.gallery.items.index', [$menu, $album]) }}"
                                               class="btn btn-info"
                                               title="{{ __('View Items') }}">
                                                <i class="ri-folder-open-line"></i>
                                            </a>
                                            @can('gallery.edit')
                                                <a href="{{ route('admin.gallery.edit', [$menu, $album]) }}"
                                                   class="btn btn-primary"
                                                   title="{{ __('Edit') }}">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                            @endcan
                                            @can('gallery.delete')
                                                <button type="button"
                                                        class="btn btn-danger delete-album"
                                                        data-id="{{ $album->id }}"
                                                        title="{{ __('Delete') }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        {{ __('No albums found') }}
                                    </td>
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

@push('scripts')
    <script>
        // Sortable
        const sortable = new Sortable(document.getElementById('sortable-albums'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const order = [];
                document.querySelectorAll('#sortable-albums tr').forEach(tr => {
                    const id = tr.getAttribute('data-id');
                    if (id) order.push(id);
                });

                fetch('{{ route('admin.gallery.update-order', $menu) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Order updated');
                        }
                    });
            }
        });

        // Delete album
        document.querySelectorAll('.delete-album').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!confirm('{{ __('Are you sure you want to delete this album?') }}')) return;

                const albumId = this.getAttribute('data-id');
                fetch(`{{ route('admin.gallery.index', $menu) }}/${albumId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            });
        });
    </script>
@endpush
