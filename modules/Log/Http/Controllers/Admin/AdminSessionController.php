<?php
namespace Modules\Log\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Log\Http\Requests\Admin\SessionIndexRequest;
use Modules\Log\Models\AdminSession;

final class AdminSessionController extends Controller
{
    public function index(SessionIndexRequest $request): View
    {
        $q = AdminSession::query()->with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $q->where('user_id', (int) $request->integer('user_id'));
        }

        if ($request->filled('ip')) {
            $q->where('ip', 'like', '%' . $request->string('ip')->toString() . '%');
        }

        foreach (['device_type', 'browser', 'os'] as $field) {
            if ($request->filled($field)) {
                $q->where($field, 'like', '%' . $request->string($field)->toString() . '%');
            }
        }

        if ($request->filled('date_from')) {
            $q->whereDate('login_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $q->whereDate('login_at', '<=', $request->date('date_to'));
        }

        $sessions = $q->paginate(30)->withQueryString();

        return view('log::admin.sessions.index', compact('sessions'));
    }

    public function show(AdminSession $session): View
    {
        $session->load('user');

        return view('log::admin.sessions.show', compact('session'));
    }
}
