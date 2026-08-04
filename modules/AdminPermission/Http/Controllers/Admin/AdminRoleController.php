<?php

namespace Modules\AdminPermission\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\AdminPermission\Http\Requests\Admin\StoreAdminRoleRequest;
use Modules\AdminPermission\Http\Requests\Admin\UpdateAdminRoleRequest;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\AdminPermission\Models\AdminRole;
use Modules\AdminPermission\Services\AdminRoleService;
use Modules\AdminPermission\Services\MenuPermissionSyncService;
use Modules\AdminPermission\Services\SystemPermissionSyncService;
use Modules\Menu\Models\Menu;

class AdminRoleController extends Controller
{
    public function __construct(private readonly AdminRoleService $service)
    {
    }

    public function index(): View
    {
        $roles = AdminRole::query()
            ->withCount(['permissions', 'users'])
            ->where('is_system', false)
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->paginate(20);

        return view('adminpermission::admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(SystemPermissionSyncService $systemPermissionSyncService, MenuPermissionSyncService $menuPermissionSyncService): View
    {
        $systemPermissionSyncService->sync();
        $menuPermissionSyncService->syncAll();

        return view('adminpermission::admin.roles.create', $this->formData(new AdminRole()));
    }

    public function store(StoreAdminRoleRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role created successfully.'));
    }

    public function edit(AdminRole $role, SystemPermissionSyncService $systemPermissionSyncService, MenuPermissionSyncService $menuPermissionSyncService): View
    {
        if ($role->is_system === true)
        {
            abort(403);
        }

        $systemPermissionSyncService->sync();
        $menuPermissionSyncService->syncAll();

        $role->load('permissions');

        return view('adminpermission::admin.roles.edit', $this->formData($role));
    }

    public function update(UpdateAdminRoleRequest $request, AdminRole $role): RedirectResponse
    {
        abort_if($role->is_system && ! $request->boolean('is_active'), 422, __('System role cannot be disabled.'));

        $this->service->update($role, $request->validated());

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role updated successfully.'));
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->is_system || $role->is_super_admin) {
            return back()->with('warning', __('This role cannot be deleted.'));
        }

        $this->service->delete($role);

        return back()->with('success', __('Role deleted successfully.'));
    }

    private function formData(AdminRole $role): array
    {
        $permissions = AdminPermission::query()
            ->with('menu.translations')
            ->active()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $systemPermissions = $permissions
            ->where('scope', 'system')
            ->values();

        $menuPermissions = $permissions
            ->where('scope', 'menu')
            ->groupBy('menu_id');

        $menus = Menu::query()
            ->with(['translations', 'childrenRecursive.translations'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $current = $role->exists
            ? $role->permissions->pluck('id')->map(fn ($id): int => (int) $id)->all()
            : [];

        return [
            'role' => $role,
            'systemPermissions' => $systemPermissions,
            'menuPermissions' => $menuPermissions,
            'menus' => $menus,
            'current' => $current,
        ];
    }
}
