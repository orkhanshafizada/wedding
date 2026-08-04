<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Log\Services\AdminSessionService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AdminSessionService $adminSessionService
    ) {
    }

    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::attempt($credentials, remember: true)) {
            return back()
                ->withErrors(['email' => __('Auth failed')])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (! $user || $user->status !== UserStatusEnum::Active) {
            $this->adminSessionService->logLogin($request, false);

            Auth::logout();

            return back()
                ->withErrors(['email' => __('Auth failed')])
                ->withInput($request->only('email'));
        }

        if (! $user->adminRoles()->where('admin_roles.is_active', true)->exists()) {
            $this->adminSessionService->logLogin($request, false);

            Auth::logout();

            return back()
                ->withErrors(['email' => __('Auth failed')])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $this->adminSessionService->logLogin($request, true);

        return redirect()->route('admin.dashboard');
    }

    public function logout(): RedirectResponse
    {
        $request = request();

        $this->adminSessionService->logLogout($request);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
