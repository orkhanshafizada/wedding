<?php
namespace Modules\Log\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Log\Http\Requests\Admin\ActivityIndexRequest;
use Modules\Log\Models\ActivityLog;

final class ActivityLogController extends Controller
{
    public function index(ActivityIndexRequest $request): View
    {
        $q = ActivityLog::query()->with(['actor', 'subject'])->orderByDesc('id');

        if ($request->filled('actor_id')) {
            $q->where('actor_id', (int) $request->integer('actor_id'));
        }

        foreach (['action', 'module', 'subject_type', 'route', 'ip'] as $field) {
            if ($request->filled($field)) {
                $q->where($field, 'like', '%' . $request->string($field)->toString() . '%');
            }
        }

        if ($request->filled('subject_id')) {
            $q->where('subject_id', (int) $request->integer('subject_id'));
        }

        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $logs = $q->paginate(30)->withQueryString();

        return view('log::admin.activity.index', compact('logs'));
    }

    public function show(ActivityLog $activity): View
    {
        $activity->load(['actor', 'subject', 'changes']);

        return view('log::admin.activity.show', compact('activity'));
    }
}
