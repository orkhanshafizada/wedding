<?php
namespace Modules\Log\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Log\Models\AdminSession;

final class AdminSessionService
{
    public function __construct(
        private readonly DeviceInfoService $deviceInfoService
    ) {
    }

    public function logLogin(Request $request, bool $isSuccessful = true): ?AdminSession
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $ua = (string) $request->userAgent();
        $info = $this->deviceInfoService->parse($ua);

        return AdminSession::create([
            'user_id' => (int) $user->getKey(),
            'guard' => (string) config('log.admin_guard', 'web'),
            'session_id' => (string) $request->session()->getId(),
            'ip' => (string) $request->ip(),
            'user_agent' => $ua,
            'device_type' => $info['device_type'] ?? null,
            'device_brand' => $info['device_brand'] ?? null,
            'device_model' => $info['device_model'] ?? null,
            'os' => $info['os'] ?? null,
            'os_version' => $info['os_version'] ?? null,
            'browser' => $info['browser'] ?? null,
            'browser_version' => $info['browser_version'] ?? null,
            'login_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
            'logout_at' => null,
            'is_successful' => $isSuccessful,
        ]);
    }

    public function logLogout(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $sessionId = (string) $request->session()->getId();

        AdminSession::query()
            ->where('user_id', (int) $user->getKey())
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'logout_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
            ]);
    }

    public function heartbeat(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $sessionId = (string) $request->session()->getId();

        AdminSession::query()
            ->where('user_id', (int) $user->getKey())
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'last_activity_at' => Carbon::now(),
            ]);
    }
}
