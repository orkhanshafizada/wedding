@extends('admin.layouts.app')

@section('title', __('Admins'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ __('Admins') }}</h4>
                    <div class="text-muted small">{{ __('Manage admin users and role assignments.') }}</div>
                </div>

                @can('admin.create')
                    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>{{ __('Add admin') }}
                    </a>
                @endcan
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('Admin') }}</th>
                                <th>{{ __('Roles') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($admins as $admin)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $admin->fullname }}</div>
                                        <div class="text-muted small">{{ $admin->email }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($admin->adminRoles as $role)
                                                <span class="badge bg-primary-subtle text-primary">
                                                        {{ $role->display_name ?: $role->name }}
                                                    </span>
                                            @empty
                                                <span class="text-muted small">{{ __('No role') }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        @if($admin->status === 'Active')
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @elseif($admin->status === 'Inactive')
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Inactive') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $admin->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($admin->created_at)->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                                {{ __('Actions') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('admin.edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.admins.edit', $admin) }}">
                                                            <i class="ri-edit-2-line me-1"></i>{{ __('Edit') }}
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('admin.delete')
                                                    @if((int) auth()->id() !== (int) $admin->id)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="post" onsubmit="return confirm('{{ __('Delete?') }}')">
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
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('Admins not found.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $admins->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
