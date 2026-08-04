@extends('admin.layouts.app')

@section('title', __('Roles'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ __('Roles') }}</h4>
                    <div class="text-muted small">{{ __('Manage admin roles and assigned permissions.') }}</div>
                </div>

                @can('role.create')
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>{{ __('Add role') }}
                    </a>
                @endcan
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Permissions') }}</th>
                                <th>{{ __('Users') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $role->display_name ?: $role->name }}</div>
                                        <div class="text-muted small">{{ $role->name }}</div>

                                        @if($role->is_super_admin)
                                            <span class="badge bg-danger-subtle text-danger mt-1">{{ __('Super admin') }}</span>
                                        @endif

                                        @if($role->is_system)
                                            <span class="badge bg-info-subtle text-info mt-1">{{ __('System') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $role->permissions_count }}
                                            </span>
                                    </td>
                                    <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $role->users_count }}
                                            </span>
                                    </td>
                                    <td>
                                        @if($role->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ __('Actions') }}
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('role.edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                                            <i class="ri-edit-2-line me-1"></i>{{ __('Edit') }}
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('role.delete')
                                                    @if(! $role->is_system && ! $role->is_super_admin)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="post" onsubmit="return confirm('{{ __('Delete?') }}')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ri-delete-bin-6-line me-1"></i>{{ __('Delete') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('Roles not found.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
