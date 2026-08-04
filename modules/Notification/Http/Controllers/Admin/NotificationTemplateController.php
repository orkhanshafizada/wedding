<?php

namespace Modules\Notification\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Notification\Http\Requests\Admin\StoreNotificationTemplateRequest;
use Modules\Notification\Http\Requests\Admin\UpdateNotificationTemplateRequest;
use Modules\Notification\Models\NotificationTemplate;
use Modules\Notification\Services\NotificationTemplateService;

class NotificationTemplateController extends Controller
{
    public function index(): View
    {
        $templates = NotificationTemplate::query()
            ->with([
                'translations' => function ($query) {
                    $query->select('id', 'notification_template_id', 'language_id', 'name');
                },
            ])
            ->withCount('translations')
            ->orderByDesc('id')
            ->get();

        $columns = [
            ['label' => __('Id'), 'width' => '220'],
            ['label' => __('Key'), 'width' => '220'],
            ['label' => __('Name'), 'width' => '320'],
            ['label' => __('Status'), 'width' => '140'],
            ['label' => __('Translations'), 'width' => '140'],
            ['label' => __('Updated at'), 'width' => '180'],
        ];

        $formattedRows = $templates->map(function (NotificationTemplate $template) {
            $badge = $template->is_active
                ? '<span class="badge bg-success">' . e(__('Active')) . '</span>'
                : '<span class="badge bg-secondary">' . e(__('Inactive')) . '</span>';

            $name = (string) optional($template->translations->first())->name;

            return [
                'id' => $template->id,
                'cells' => [
                    '<span class="js-system-template-flag" data-system-template="' . ($template->system_template ? '1' : '0') . '">' . e($template->key) . '</span>',
                    e($name),
                    $badge,
                    (string) $template->translations_count,
                    e($template->updated_at?->format('d M Y H:i') ?? ''),
                ],
            ];
        });

        return view('notification::admin.templates.index', compact('columns', 'formattedRows'));
    }

    public function create(): View
    {
        $languages = Language::query()->active()->orderBy('sort_order')->get();

        return view('notification::admin.templates.form', [
            'template' => null,
            'languages' => $languages,
            'mode' => 'create',
        ]);
    }

    public function store(StoreNotificationTemplateRequest $request, NotificationTemplateService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()
            ->route('admin.notification.templates.index')
            ->with('success', __('Template created successfully.'));
    }

    public function edit(NotificationTemplate $template): View
    {
        $template->load('translations');
        $languages = Language::query()->active()->orderBy('sort_order')->get();

        return view('notification::admin.templates.form', [
            'template' => $template,
            'languages' => $languages,
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $template, NotificationTemplateService $service): RedirectResponse
    {
        $service->update($template, $request->validated());

        return redirect()
            ->route('admin.notification.templates.index')
            ->with('success', __('Template updated successfully.'));
    }

    public function destroy(NotificationTemplate $template, NotificationTemplateService $service): RedirectResponse
    {
        $service->delete($template);

        return redirect()
            ->route('admin.notification.templates.index')
            ->with('success', __('Template deleted successfully.'));
    }

    public function bulkDelete(Request $request, NotificationTemplateService $service): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ], [
            'ids.required' => __('Please select items.'),
        ]);

        $count = $service->bulkDelete($validated['ids']);

        return response()->json([
            'success' => true,
            'message' => __('Deleted successfully.'),
            'deleted' => $count,
        ]);
    }
}
