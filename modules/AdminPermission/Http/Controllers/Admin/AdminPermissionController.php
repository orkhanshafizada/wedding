<?php

namespace Modules\AdminPermission\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\AdminPermission\Http\Requests\Admin\StoreAdminPermissionRequest;
use Modules\AdminPermission\Http\Requests\Admin\UpdateAdminPermissionRequest;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\AdminPermission\Services\MenuPermissionSyncService;
use Modules\AdminPermission\Services\SystemPermissionSyncService;
use Modules\Menu\Models\Menu;

class AdminPermissionController extends Controller
{
    public function index(): View
    {
        $permissions = AdminPermission::query()
            ->with('menu.translations')
            ->orderBy('group')
            ->orderBy('scope')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return view('adminpermission::admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): View
    {
        return view('adminpermission::admin.permissions.create', [
            'permission' => new AdminPermission(),
            'menus' => $this->menus(),
        ]);
    }

    public function store(StoreAdminPermissionRequest $request): RedirectResponse
    {
        AdminPermission::query()->create($request->validated());

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', __('Permission created successfully.'));
    }

    public function edit(AdminPermission $permission): View
    {
        return view('adminpermission::admin.permissions.edit', [
            'permission' => $permission,
            'menus' => $this->menus(),
        ]);
    }

    public function update(UpdateAdminPermissionRequest $request, AdminPermission $permission): RedirectResponse
    {
        $permission->update($request->validated());

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', __('Permission updated successfully.'));
    }

    public function destroy(AdminPermission $permission): RedirectResponse
    {
        $permission->roles()->detach();
        $permission->delete();

        return back()->with('success', __('Permission deleted successfully.'));
    }

    public function syncSystem(SystemPermissionSyncService $service): RedirectResponse
    {
        $count = $service->sync();

        return back()->with('success', __('System permissions synchronized successfully. Count: :count', ['count' => $count]));
    }

    public function syncMenus(MenuPermissionSyncService $service): RedirectResponse
    {
        $count = $service->syncAll();

        return back()->with('success', __('Menu permissions synchronized successfully. Count: :count', ['count' => $count]));
    }

    private function menus()
    {
        return Menu::query()
            ->with('translations')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
