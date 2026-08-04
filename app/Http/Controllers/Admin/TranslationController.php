<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Translations\StoreTranslationRequest;
use App\Http\Requests\Admin\Translations\UpdateTranslationRequest;
use App\Jobs\Translations\ImportTranslationsJob;
use App\Jobs\Translations\SyncTranslationsJob;
use App\Models\Language;
use App\Models\Translation;
use App\Services\Translations\TranslationBulkTranslateService;
use App\Services\Translations\TranslationExcelService;
use App\Services\Translations\TranslationProgressStore;
use App\Services\Translations\TranslationService;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TranslationController extends Controller
{
    public function __construct(
        private readonly TranslationProgressStore $progressStore,
        private readonly TranslationExcelService $excelService,
        private readonly TranslationService $translationService,
        private readonly TranslationBulkTranslateService $translationBulkTranslateService,
        private readonly Dispatcher $dispatcher
    ) {
    }

    public function index(Request $request): View
    {
        $defaultLocale = (string) (Language::active()->where('is_default_admin', true)->value('code')
            ?? Language::active()->value('code')
            ?? 'az');

        $locale = (string) $request->get('locale', $defaultLocale);
        $q = (string) $request->get('q', '');
        $perPage = max(10, min(100, (int) $request->integer('per_page', 20)));

        $translations = $this->translationService->paginateGroups([
            'locale' => $locale,
            'q' => $q,
        ], $perPage);

        $stats = $this->translationService->localeStats($locale);

        $languages = Language::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'code']);

        return view('admin.translations.index', [
            'translations' => $translations,
            'languages' => $languages,
            'locale' => $locale,
            'q' => $q,
            'perPage' => $perPage,
            'stats' => $stats,
            'defaultSourceLocale' => $defaultLocale,
        ]);
    }

    public function create(): View
    {
        $languages = Language::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'code']);

        return view('admin.translations.create', [
            'translation' => new Translation(),
            'languages' => $languages,
            'group' => collect(),
            'defaultSourceLocale' => (string) (Language::active()->where('is_default_admin', true)->value('code')
                ?? Language::active()->value('code')
                ?? 'az'),
        ]);
    }

    public function store(StoreTranslationRequest $request): RedirectResponse
    {
        $translation = $this->translationService->createGroup(
            $request->validated(),
            (int) auth()->id()
        );

        return redirect()
            ->route('admin.translations.edit', $translation)
            ->with('success', __('Translation created successfully.'));
    }

    public function edit(Translation $translation): View
    {
        $formData = $this->translationService->getFormData($translation);

        return view('admin.translations.edit', [
            'translation' => $translation,
            'languages' => $formData['languages'],
            'group' => $formData['group'],
            'defaultSourceLocale' => (string) (Language::active()->where('is_default_admin', true)->value('code')
                ?? Language::active()->value('code')
                ?? 'az'),
        ]);
    }

    public function update(UpdateTranslationRequest $request, Translation $translation): RedirectResponse
    {
        $translation = $this->translationService->updateGroup(
            $translation,
            $request->validated(),
            (int) auth()->id()
        );

        return redirect()
            ->route('admin.translations.edit', $translation)
            ->with('success', __('Translation updated successfully.'));
    }

    public function destroy(Translation $translation): RedirectResponse
    {
        $this->translationService->deleteGroup($translation);

        return redirect()
            ->route('admin.translations.index')
            ->with('success', __('Translation deleted successfully.'));
    }

    public function startSync(): JsonResponse
    {
        try {
            $token = $this->progressStore->start(__('Sync started...'));

            $this->dispatcher->dispatchSync(new SyncTranslationsJob(
                $token,
                (int) auth()->id()
            ));

            return response()->json([
                'ok' => true,
                'token' => $token,
                'message' => __('Sync completed successfully.'),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function startAutoTranslateMissing(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'target' => ['required', 'string', 'max:20', 'exists:languages,code'],
                'source' => ['nullable', 'string', 'max:20', 'exists:languages,code'],
            ]);

            $target = (string) $validated['target'];
            $source = (string) ($validated['source'] ?? (Language::active()->where('is_default_admin', true)->value('code')
                ?? Language::active()->value('code')
                ?? 'az'));

            $token = $this->translationBulkTranslateService->start($source, $target, 'default');

            return response()->json([
                'ok' => true,
                'token' => $token,
                'message' => __('Auto translation started successfully.'),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function startAutoTranslateGoogle(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'target' => ['required', 'string', 'max:20', 'exists:languages,code'],
                'source' => ['nullable', 'string', 'max:20', 'exists:languages,code'],
            ]);

            $target = (string) $validated['target'];
            $source = (string) ($validated['source'] ?? (Language::active()->where('is_default_admin', true)->value('code')
                ?? Language::active()->value('code')
                ?? 'az'));

            $token = $this->translationBulkTranslateService->start($source, $target, 'google');

            return response()->json([
                'ok' => true,
                'token' => $token,
                'message' => __('Google auto translation started successfully.'),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function progress(string $token): JsonResponse
    {
        $state = $this->progressStore->get($token);

        if ($state === null) {
            return response()->json([
                'ok' => false,
                'done' => true,
                'percent' => 100,
                'message' => __('Operation not found or expired.'),
                'reload' => false,
            ], 404);
        }

        $meta = $state['meta'] ?? [];
        $operation = (string) ($meta['operation'] ?? '');

        if (($state['done'] ?? false) === false && $operation === 'auto_translate') {
            $this->translationBulkTranslateService->advance($token, (int) auth()->id());
            $state = $this->progressStore->get($token) ?? $state;
        }

        return response()->json([
            'ok' => (bool) $state['ok'],
            'done' => (bool) $state['done'],
            'percent' => (int) $state['percent'],
            'message' => (string) $state['message'],
            'reload' => (bool) $state['reload'],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $locale = (string) $request->get('locale', (string) (Language::active()->value('code') ?? 'az'));
        $path = $this->excelService->exportToXlsx($locale);
        $filename = 'translations_' . $locale . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function startImport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
                'mode' => ['required', 'in:upsert,only_empty'],
            ]);

            $storedPath = $validated['file']->storeAs(
                'imports/translations',
                Str::uuid()->toString() . '.xlsx',
                'local'
            );

            $token = $this->progressStore->start(__('Import started...'));

            $this->dispatcher->dispatchSync(new ImportTranslationsJob(
                $token,
                (int) auth()->id(),
                Storage::disk('local')->path($storedPath),
                (string) $validated['mode']
            ));

            return response()->json([
                'ok' => true,
                'token' => $token,
                'message' => __('Import completed successfully.'),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
