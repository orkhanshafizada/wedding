@php
    $selectedRoleIds = collect(old('role_ids', $currentRoleIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="page-content">
    <div class="container-fluid">
        <form action="{{ $action }}" method="post">
            @csrf
            @if($method)
                @method($method)
            @endif

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ $admin->exists ? __('Edit admin') : __('Add admin') }}</h4>
                    <div class="text-muted small">{{ __('Create admin users and assign one or more roles.') }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save') }}
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="fw-semibold">{{ __('Admin information') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Full name') }}</label>
                                <input type="text"
                                       name="fullname"
                                       value="{{ old('fullname', $admin->fullname) }}"
                                       class="form-control @error('fullname') is-invalid @enderror">
                                @error('fullname')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email', $admin->email) }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Active', 'Inactive', 'Pending'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $admin->status ?: 'Active') === $status)>
                                            {{ __($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('Password') }}
                                        @if($admin->exists)
                                            <span class="text-muted small">({{ __('optional') }})</span>
                                        @endif
                                    </label>
                                    <input type="password"
                                           name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           autocomplete="new-password">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">{{ __('Confirm password') }}</label>
                                    <input type="password"
                                           name="password_confirmation"
                                           class="form-control"
                                           autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="fw-semibold">{{ __('Roles') }}</div>
                            <div class="text-muted small">{{ __('Select roles assigned to this admin.') }}</div>
                        </div>

                        <div class="card-body">
                            @error('role_ids')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="role-list">
                                @forelse($roles as $role)
                                    <label class="role-item">
                                        <input type="checkbox"
                                               name="role_ids[]"
                                               value="{{ $role->id }}"
                                               class="form-check-input"
                                            @checked(in_array((int) $role->id, $selectedRoleIds, true))>
                                        <span class="role-body">
                                            <span class="role-title">
                                                {{ $role->display_name ?: $role->name }}
                                                @if($role->is_super_admin)
                                                    <span class="badge bg-danger-subtle text-danger ms-1">{{ __('Super admin') }}</span>
                                                @endif
                                            </span>
                                            <span class="role-subtitle">{{ $role->name }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="text-muted">{{ __('Roles not found.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
    <style>
        .role-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .role-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border: 1px solid #eef0f4;
            border-radius: 14px;
            padding: 12px 14px;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }

        .role-item:hover {
            background: #f8fafc;
            border-color: #dbe3ee;
        }

        .role-body {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .role-title {
            font-weight: 600;
            color: #212529;
        }

        .role-subtitle {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
@endpush
