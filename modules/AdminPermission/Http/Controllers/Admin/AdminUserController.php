<?php

namespace Modules\AdminPermission\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminPermission\Http\Requests\Admin\StoreAdminUserRequest;
use Modules\AdminPermission\Http\Requests\Admin\UpdateAdminUserRequest;
use Modules\AdminPermission\Models\AdminRole;
use Modules\AdminPermission\Services\AdminUserService;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $service)
    {
    }

    public function index(): View
    {
        $admins = User::query()
            ->with('adminRoles')
            ->where('is_secret', false)
            ->orderByDesc('id')
            ->paginate(20);

        return view('adminpermission::admin.admins.index', [
            'admins' => $admins,
        ]);
    }

    public function create(): View
    {
        return view('adminpermission::admin.admins.create', [
            'admin' => new User(),
            'roles' => $this->roles(),
            'currentRoleIds' => [],
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.admins.index')
            ->with('success', __('Admin created successfully.'));
    }

    public function edit(User $admin): View
    {
        if ($admin->is_secret)
        {
            abort(403);
        }

        $admin->load('adminRoles');

        return view('adminpermission::admin.admins.edit', [
            'admin' => $admin,
            'roles' => $this->roles(),
            'currentRoleIds' => $admin->adminRoles->pluck('id')->map(fn ($id): int => (int) $id)->all(),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $admin): RedirectResponse
    {
        if ($admin->is_secret)
        {
            abort(403);
        }

        $this->service->update($admin, $request->validated());

        return redirect()
            ->route('admin.admins.index')
            ->with('success', __('Admin updated successfully.'));
    }

    public function destroy(User $admin): RedirectResponse
    {
        if ((int) auth()->id() === (int) $admin->id) {
            return back()->with('warning', __('You cannot delete your own account.'));
        }

        $this->service->delete($admin);

        return back()->with('success', __('Admin deleted successfully.'));
    }

    public function toggleStatus(Request $request, User $admin): JsonResponse
    {
        $data = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        if ((int) auth()->id() === (int) $admin->id && ! $data['active']) {
            return response()->json([
                'ok' => false,
                'message' => __('You cannot disable your own account.'),
            ], 422);
        }

        $admin->update([
            'status' => $data['active'] ? 'Active' : 'Inactive',
        ]);

        return response()->json([
            'ok' => true,
            'status' => $admin->status,
            'message' => __('Admin status updated successfully.'),
        ]);
    }

    private function roles()
    {
        return AdminRole::query()
            ->active()
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->get();
    }
}
