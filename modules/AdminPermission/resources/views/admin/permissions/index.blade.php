@extends('admin.layouts.app')

@section('title', __('Permissions'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ __('Permissions') }}</h4>
                    <div class="text-muted small">{{ __('Manage system and menu permissions.') }}</div>
                </div>

                <div class="d-flex gap-2">
                    @can('permission.create')
                        <form action="{{ route('admin.permissions.sync-system') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-soft-primary">
                                <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Sync system') }}
                            </button>
                        </form>

                        <form action="{{ route('admin.permissions.sync-menus') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-soft-info">
                                <i class="ri-menu-search-line align-bottom me-1"></i>{{ __('Sync menus') }}
                            </button>
                        </form>

                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i>{{ __('Add permission') }}
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Permission') }}</th>
                                <th>{{ __('Group') }}</th>
                                <th>{{ __('Scope') }}</th>
                                <th>{{ __('Module') }}</th>
                                <th>{{ __('Action') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $permission->display_name }}</div>
                                        <div class="text-muted small">{{ $permission->name }}</div>
                                        @if($permission->menu)
                                            <div class="text-muted small">
                                                {{ __('Menu') }} #{{ $permission->menu_id }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $permission->group }}</td>
                                    <td>
                                        @if($permission->scope === 'menu')
                                            <span class="badge bg-info-subtle text-info">{{ __('Menu') }}</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary">{{ __('System') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $permission->module }}</td>
                                    <td>{{ $permission->action }}</td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                                {{ __('Actions') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('permission.edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.permissions.edit', $permission) }}">
                                                            <i class="ri-edit-2-line me-1"></i>{{ __('Edit') }}
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('permission.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="post" onsubmit="return confirm('{{ __('Delete?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ri-delete-bin-6-line me-1"></i>{{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ __('Permissions not found.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
