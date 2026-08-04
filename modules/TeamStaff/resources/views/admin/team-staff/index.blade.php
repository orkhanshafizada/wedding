@extends('admin.layouts.app')

@section('title', 'Team Staff')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Team Staff - {{ $menu->name }}</h5>
                    <div>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary me-2">
                            <i class="ri-arrow-left-line me-1"></i> Menulara qayıt
                        </a>
                        <a href="{{ route('admin.team-staff.create', $menu) }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Yeni Team Staff
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="sortable-table">
                            <thead>
                            <tr>
                                <th style="width: 30px;"></th>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Ad Soyad</th>
                                <th>Şirkət</th>
                                <th>Vəzifə</th>
                                <th>Status</th>
                                <th style="width: 150px;">Əməliyyatlar</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-tbody">
                            @forelse($teamStaff as $staff)
                                <tr data-id="{{ $staff->id }}">
                                    <td class="drag-handle" style="cursor: move;">
                                        <i class="ri-menu-line"></i>
                                    </td>
                                    <td>{{ $staff->id }}</td>
                                    <td>
                                        @if($staff->profile_picture)
                                            <img src="{{ asset('storage/' . $staff->profile_picture) }}"
                                                 alt="Profile"
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                        @else
                                            <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-line"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $names = json_decode($staff->getRawOriginal('name'), true);
                                        @endphp
                                        {{ $names[$adminLang] ?? $names['az'] ?? '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $companies = json_decode($staff->getRawOriginal('company'), true);
                                        @endphp
                                        {{ $companies[$adminLang] ?? $companies['az'] ?? '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $positions = json_decode($staff->getRawOriginal('position'), true);
                                        @endphp
                                        {{ $positions[$adminLang] ?? $positions['az'] ?? '-' }}
                                    </td>
                                    <td>
                                        @if($staff->is_active)
                                            <span class="badge bg-success">
                                                <i class="ri-check-line me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="ri-close-line me-1"></i>Deactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.team-staff.edit', [$menu, $staff]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.team-staff.destroy', [$menu, $staff]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Team Staff silinsin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Heç bir Team Staff tapılmadı
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($teamStaff->hasPages())
                        <div class="mt-3">
                            {{ $teamStaff->links() }}
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
            const tbody = document.getElementById('sortable-tbody');
            if (tbody) {
                new Sortable(tbody, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function(evt) {
                        const items = Array.from(tbody.children);
                        const order = items.map((item, index) => ({
                            id: item.dataset.id,
                            position: index
                        }));

                        fetch('{{ route("admin.team-staff.update-order", $menu) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Sıralama yeniləndi');
                            }
                        })
                        .catch(error => console.error('Xəta:', error));
                    }
                });
            }
        });
    </script>
@endpush
