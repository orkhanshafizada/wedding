<?php

namespace Modules\Form\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Form\Http\Requests\Admin\UpdateFormResponseStatusRequest;
use Modules\Form\Models\FormLabel;
use Modules\Form\Models\FormResponse;
use Modules\Form\Services\FormResponseService;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class FormController extends Controller
{
    public function __construct(
        private readonly FormResponseService $formResponseService
    ) {
    }

    public function index(Menu $menu): View
    {
        $labels = FormLabel::with([
            'translations' => function ($query): void {
                $query->where('locale', app()->getLocale());
            },
        ])
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->get();

        return view('form::admin.index', compact('menu', 'labels'));
    }

    public function viewData(Menu $menu): View
    {
        $labels = FormLabel::with('translations')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->get();

        $responses = FormResponse::query()
            ->where('menu_id', $menu->id)
            ->latest('created_at')
            ->get();

        $columns = [
            ['label' => __('ID')],
        ];

        foreach ($labels as $label) {
            $translation = $label->translation(app()->getLocale())
                ?? $label->translations->first();

            $columns[] = [
                'label' => $translation?->name ?? __('No translation'),
            ];
        }

        $columns[] = ['label' => __('Date')];
        $columns[] = [
            'label' => __('Status'),
            'width' => '110',
        ];

        $formattedRows = $responses->map(function (FormResponse $response) use ($labels, $menu): array {
            $row = [
                'id' => $response->id,
                'cells' => [],
            ];

            foreach ($labels as $label) {
                $labelData = collect($response->labels_data)
                    ->firstWhere('label_id', $label->id);

                $value = $labelData['value'] ?? '-';

                if ($label->type === 'file' && $value && $value !== '-') {
                    $value = $this->formatFileValue((string) $value);
                } elseif (is_bool($value)) {
                    $value = e($value ? __('Yes') : __('No'));
                } elseif (is_array($value)) {
                    $value = e(implode(', ', $value));
                } else {
                    $value = e((string) $value);
                }

                $row['cells'][] = $value;
            }

            $row['cells'][] = sprintf(
                '%s<br><small>%s</small>',
                e($response->created_at->format('d M Y')),
                e($response->created_at->format('H:i:s'))
            );

            $row['cells'][] = [
                'type' => 'status_switch',
                'checked' => $response->isActive(),
                'update_url' => route('admin.form.response.status', [
                    'menu' => $menu->id,
                    'response' => $response->id,
                ]),
            ];

            return $row;
        });

        return view(
            'form::admin.view-data',
            compact('menu', 'columns', 'formattedRows')
        );
    }

    public function updateResponseStatus(
        UpdateFormResponseStatusRequest $request,
        Menu $menu,
        FormResponse $response
    ): JsonResponse {
        try {
            $updatedResponse = $this->formResponseService->updateStatus(
                $menu,
                $response,
                $request->integer('status')
            );

            return response()->json([
                'success' => true,
                'message' => __('Response status updated successfully.'),
                'data' => [
                    'id' => $updatedResponse->id,
                    'status' => $updatedResponse->status,
                    'is_active' => $updatedResponse->isActive(),
                ],
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update response status.'),
            ], 500);
        }
    }

    public function exportData(
        Menu $menu,
        ExcelExportService $excelService
    ): BinaryFileResponse {
        $labels = FormLabel::with('translations')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->get();

        $responses = FormResponse::query()
            ->where('menu_id', $menu->id)
            ->latest('created_at')
            ->get();

        $headers = [__('ID')];

        foreach ($labels as $label) {
            $translation = $label->translation(app()->getLocale())
                ?? $label->translations->first();

            $headers[] = $translation?->name ?? __('No translation');
        }

        $headers[] = __('Date');
        $headers[] = __('Status');

        $data = $responses->map(function (FormResponse $response) use ($labels): array {
            $row = [$response->id];

            foreach ($labels as $label) {
                $labelData = collect($response->labels_data)
                    ->firstWhere('label_id', $label->id);

                $value = $labelData['value'] ?? '-';

                if (is_bool($value)) {
                    $value = $value ? __('Yes') : __('No');
                } elseif (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $row[] = $value;
            }

            $row[] = $response->created_at->format('Y-m-d H:i:s');
            $row[] = $response->isActive()
                ? __('Active')
                : __('Inactive');

            return $row;
        });

        return $excelService->export(
            $data,
            $headers,
            'form-data-' . $menu->id
        );
    }

    public function destroy(
        Menu $menu,
        FormResponse $response
    ): RedirectResponse {
        try {
            $response = FormResponse::query()
                ->whereKey($response->id)
                ->where('menu_id', $menu->id)
                ->firstOrFail();

            $response->delete();

            return redirect()
                ->route('admin.form.view-data', $menu)
                ->with('success', __('Response deleted successfully.'));
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->back()
                ->with('error', __('Failed to delete response.'));
        }
    }

    public function bulkDelete(
        Request $request,
        Menu $menu
    ): JsonResponse {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        try {
            FormResponse::query()
                ->where('menu_id', $menu->id)
                ->whereIn('id', $validated['ids'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => __('Selected responses deleted successfully.'),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => __('Failed to delete selected responses.'),
            ], 500);
        }
    }

    private function formatFileValue(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $url = Storage::disk('public')->url($path);

        $imageExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
        ];

        if (in_array($extension, $imageExtensions, true)) {
            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="d-inline-block">
                    <img src="%s" alt="%s" style="max-width:80px;max-height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                </a>',
                e($url),
                e($url),
                e(__('Uploaded image'))
            );
        }

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                <i class="fas fa-file-%s fa-2x text-primary"></i>
                <br><small class="text-muted">%s</small>
            </a>',
            e($url),
            e($this->getFileIcon($extension)),
            e(basename($path))
        );
    }

    private function getFileIcon(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'zip', 'rar' => 'archive',
            'mp3', 'wav' => 'audio',
            'mp4', 'avi' => 'video',
            default => 'alt',
        };
    }
}