@php
    $formAction = $role->exists
        ? route('admin.roles.update', $role)
        : route('admin.roles.store');

    $formMethod = $role->exists ? 'PUT' : null;

    $selectedPermissionIds = collect(old('permissions', $current ?? []))
        ->map(fn ($id): int => (int) $id)
        ->unique()
        ->values()
        ->all();

    $systemActions = ['view', 'create', 'edit', 'delete'];
    $menuPermissionActions = ['view', 'content', 'edit', 'delete'];

    $normalizeAction = static function (mixed $action): string {
        $action = strtolower(trim((string) $action));

        return match ($action) {
            'add', 'store' => 'create',
            'update' => 'edit',
            'destroy', 'remove' => 'delete',
            default => $action,
        };
    };

    $systemMatrix = [];

    foreach ($systemPermissions->groupBy('group') as $groupName => $permissions) {
        $row = array_fill_keys($systemActions, null);

        foreach ($permissions as $permission) {
            $action = $normalizeAction($permission->action);

            if (array_key_exists($action, $row)) {
                $row[$action] = $permission;
            }
        }

        $systemMatrix[(string) $groupName] = $row;
    }

    ksort($systemMatrix);

    $menuName = static function ($menu): string {
        $locale = (string) app()->getLocale();

        return trim((string) (
            $menu->translations->firstWhere('locale', $locale)?->name
            ?? $menu->translations->first()?->name
            ?? ('Menu #' . $menu->id)
        ));
    };

    $menuTypeValue = static function ($menu): string {
        return $menu->type instanceof \BackedEnum
            ? (string) $menu->type->value
            : (string) $menu->type;
    };
@endphp

<div class="page-content">
    <div class="container-fluid">
        <form action="{{ $formAction }}" method="post" id="admin-role-form" autocomplete="off">
            @csrf

            @if($formMethod)
                @method($formMethod)
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="mb-1">{{ $role->exists ? __('Edit role') : __('Add role') }}</h4>
                    <div class="text-muted small">{{ __('Create roles and assign system or menu specific permissions.') }}</div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light">
                        {{ __('Cancel') }}
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save') }}
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm sticky-xl-top admin-role-side-card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="role-name" class="form-label">{{ __('Role name') }}</label>
                                <input type="text"
                                       id="role-name"
                                       name="name"
                                       value="{{ old('name', $role->name) }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="content_manager"
                                       maxlength="100"
                                       required>

                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="role-display-name" class="form-label">{{ __('Display name') }}</label>
                                <input type="text"
                                       id="role-display-name"
                                       name="display_name"
                                       value="{{ old('display_name', $role->display_name) }}"
                                       class="form-control @error('display_name') is-invalid @enderror"
                                       placeholder="{{ __('Content manager') }}"
                                       maxlength="150">

                                @error('display_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="border rounded-3 p-3 mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_active"
                                           value="1"
                                           id="is-active"
                                        @checked((bool) old('is_active', $role->exists ? $role->is_active : true))>
                                    <label class="form-check-label" for="is-active">{{ __('Active') }}</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_super_admin" value="0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_super_admin"
                                           value="1"
                                           id="is-super-admin"
                                        @checked((bool) old('is_super_admin', $role->is_super_admin))>
                                    <label class="form-check-label" for="is-super-admin">{{ __('Super admin') }}</label>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <div class="fw-semibold mb-1">{{ __('Selected permissions') }}</div>
                                <div class="fs-4 fw-bold" id="selected-permission-count">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-6">
                                    <div class="fw-semibold">{{ __('System permissions') }}</div>
                                    <div class="text-muted small">{{ __('Global permissions for modules and admin sections.') }}</div>
                                </div>

                                <div class="col-lg-6 text-lg-end">
                                    <button type="button"
                                            class="btn btn-sm btn-soft-primary js-check-section"
                                            data-target="#system-permissions"
                                            data-visible-only="0">
                                        {{ __('Check section') }}
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-soft-secondary js-uncheck-section"
                                            data-target="#system-permissions"
                                            data-visible-only="0">
                                        {{ __('Uncheck section') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0" id="system-permissions">
                            <div class="table-responsive permission-table-wrap">
                                <table class="table align-middle mb-0 permission-table">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Permission group') }}</th>
                                        <th class="text-center">{{ __('All') }}</th>
                                        <th class="text-center">{{ __('View') }}</th>
                                        <th class="text-center">{{ __('Create') }}</th>
                                        <th class="text-center">{{ __('Edit') }}</th>
                                        <th class="text-center">{{ __('Delete') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($systemMatrix as $groupName => $row)
                                        @php
                                            $rowPermissions = collect($row)->filter();
                                            $rowIds = $rowPermissions->pluck('id')->map(fn ($id): int => (int) $id)->all();
                                            $checkedCount = count(array_intersect($rowIds, $selectedPermissionIds));
                                            $allChecked = count($rowIds) > 0 && $checkedCount === count($rowIds);
                                        @endphp

                                        <tr data-permission-row="1">
                                            <td>
                                                <div class="fw-semibold">{{ $groupName }}</div>
                                            </td>

                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-flex m-0">
                                                    <input class="form-check-input js-row-master"
                                                           type="checkbox"
                                                        @checked($allChecked)
                                                        @disabled(count($rowIds) === 0)>
                                                </div>
                                            </td>

                                            @foreach($systemActions as $action)
                                                <td class="text-center">
                                                    @if($row[$action])
                                                        <div class="form-check form-switch d-inline-flex m-0">
                                                            <input class="form-check-input js-permission"
                                                                   type="checkbox"
                                                                   name="permissions[]"
                                                                   value="{{ $row[$action]->id }}"
                                                                @checked(in_array((int) $row[$action]->id, $selectedPermissionIds, true))>
                                                        </div>
                                                    @else
                                                        <div class="form-check form-switch d-inline-flex m-0 opacity-50">
                                                            <input class="form-check-input" type="checkbox" disabled>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted text-center py-4">{{ __('System permissions not found.') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @error('permissions')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-5">
                                    <div class="fw-semibold">{{ __('Menu permissions') }}</div>
                                    <div class="text-muted small">{{ __('Assign permissions to specific menus.') }}</div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"><i class="ri-search-line"></i></span>
                                        <input type="text"
                                               id="menu-permission-search"
                                               class="form-control"
                                               placeholder="{{ __('Search menus...') }}"
                                               autocomplete="off">
                                    </div>
                                </div>

                                <div class="col-lg-3 text-lg-end">
                                    <button type="button"
                                            class="btn btn-sm btn-soft-primary js-check-section"
                                            data-target="#menu-permissions"
                                            data-visible-only="1">
                                        {{ __('Check visible') }}
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-soft-secondary js-uncheck-section"
                                            data-target="#menu-permissions"
                                            data-visible-only="1">
                                        {{ __('Uncheck visible') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0" id="menu-permissions">
                            <div class="menu-permission-list">
                                @forelse($menus as $menu)
                                    @include('adminpermission::admin.roles.partials.menu-permission-node', [
                                        'menu' => $menu,
                                        'level' => 0,
                                        'menuPermissions' => $menuPermissions,
                                        'selectedPermissionIds' => $selectedPermissionIds,
                                        'menuPermissionActions' => $menuPermissionActions,
                                        'menuName' => $menuName,
                                        'menuTypeValue' => $menuTypeValue,
                                    ])
                                @empty
                                    <div class="text-muted text-center py-4">{{ __('Menus not found.') }}</div>
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
        .admin-role-side-card {
            top: 92px;
        }

        .permission-table-wrap,
        .menu-permission-list {
            border: 1px solid #eef0f4;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }

        .permission-table thead tr {
            background: #1f49c4;
        }

        .permission-table thead th {
            color: #fff;
            border: 0;
            font-weight: 600;
            padding: .9rem .95rem;
            white-space: nowrap;
        }

        .permission-table tbody td {
            padding: .85rem .95rem;
            border-top: 1px solid #eef0f4;
        }

        .permission-table tbody tr:hover {
            background: rgba(31, 73, 196, .04);
        }

        .menu-permission-row {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) 110px 110px 110px 110px 110px;
            gap: 12px;
            align-items: center;
            padding: 12px 14px;
            border-top: 1px solid #eef0f4;
            background: #fff;
        }

        .menu-permission-row:first-child {
            border-top: 0;
        }

        .menu-permission-row:hover {
            background: #f8fafc;
        }

        .menu-permission-title {
            min-width: 0;
        }

        .menu-permission-name {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-permission-meta {
            color: #6c757d;
            font-size: 12px;
        }

        .menu-permission-action {
            text-align: center;
        }

        .menu-permission-header {
            background: #f8fafc;
            color: #405169;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: 12px;
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-check-input:disabled {
            cursor: not-allowed;
            opacity: .55;
            background-color: #e9ecef !important;
            border-color: #e9ecef !important;
        }

        @media (max-width: 1200px) {
            .menu-permission-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .menu-permission-action {
                text-align: left;
            }

            .menu-permission-header {
                display: none;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const permissionSelector = '.js-permission';

            function isHidden(element) {
                return element.closest('.d-none') !== null;
            }

            function permissionBoxes(scope, visibleOnly) {
                return Array.from(scope.querySelectorAll(permissionSelector)).filter(function (checkbox) {
                    if (checkbox.disabled) {
                        return false;
                    }

                    return !visibleOnly || !isHidden(checkbox);
                });
            }

            function updateSelectedCount() {
                const target = document.getElementById('selected-permission-count');

                if (!target) {
                    return;
                }

                target.textContent = String(document.querySelectorAll(permissionSelector + ':checked').length);
            }

            function syncRowMaster(row) {
                const rowMaster = row.querySelector('.js-row-master');

                if (!rowMaster) {
                    return;
                }

                const boxes = Array.from(row.querySelectorAll(permissionSelector)).filter(function (checkbox) {
                    return !checkbox.disabled;
                });

                const checkedBoxes = boxes.filter(function (checkbox) {
                    return checkbox.checked;
                });

                rowMaster.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < boxes.length;
                rowMaster.checked = boxes.length > 0 && checkedBoxes.length === boxes.length;
                rowMaster.disabled = boxes.length === 0;
            }

            function syncAllRowMasters() {
                document.querySelectorAll('[data-permission-row="1"]').forEach(syncRowMaster);
                updateSelectedCount();
            }

            function applySectionState(button, checked) {
                const targetSelector = button.getAttribute('data-target');
                const scope = targetSelector ? document.querySelector(targetSelector) : null;

                if (!scope) {
                    return;
                }

                const visibleOnly = button.getAttribute('data-visible-only') === '1';

                permissionBoxes(scope, visibleOnly).forEach(function (checkbox) {
                    checkbox.checked = checked;
                });

                syncAllRowMasters();
            }

            document.addEventListener('change', function (event) {
                if (event.target.classList.contains('js-row-master')) {
                    const row = event.target.closest('[data-permission-row="1"]');

                    if (row) {
                        row.querySelectorAll(permissionSelector).forEach(function (checkbox) {
                            if (!checkbox.disabled) {
                                checkbox.checked = event.target.checked;
                            }
                        });
                    }
                }

                if (
                    event.target.classList.contains('js-permission') ||
                    event.target.classList.contains('js-row-master')
                ) {
                    syncAllRowMasters();
                }
            });

            document.querySelectorAll('.js-check-section').forEach(function (button) {
                button.addEventListener('click', function () {
                    applySectionState(this, true);
                });
            });

            document.querySelectorAll('.js-uncheck-section').forEach(function (button) {
                button.addEventListener('click', function () {
                    applySectionState(this, false);
                });
            });

            const searchInput = document.getElementById('menu-permission-search');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();

                    document.querySelectorAll('[data-menu-permission-row="1"]').forEach(function (row) {
                        const search = (row.dataset.search || '').toLowerCase();
                        row.classList.toggle('d-none', query !== '' && !search.includes(query));
                    });
                });
            }

            syncAllRowMasters();
        })();
    </script>
@endpush
