<?php

namespace Modules\Form\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Form\Enums\FormLabelTypeEnum;
use Modules\Form\Models\FormLabel;
use Modules\Form\Models\FormResponse;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Form\Repositories\FormResponseRepository;

class FormResponseService
{
    public function __construct(
        private readonly FormResponseRepository $formResponseRepository
    ) {
    }

    public function storeFromApi(Menu $menu, array $answers): FormResponse
    {
        $this->validateMenu($menu);

        $labels = FormLabel::query()
            ->with('translations')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($labels->isEmpty()) {
            throw ValidationException::withMessages([
                'answers' => __('The selected form has no configured fields.'),
            ]);
        }

        $labelIds = $labels
            ->pluck('id')
            ->map(static fn (mixed $labelId): int => (int) $labelId)
            ->values()
            ->all();

        $this->validateAnswerKeys($answers, $labelIds);

        [$rules, $messages] = $this->buildValidationConfiguration($labels);

        $validated = Validator::make(
            ['answers' => $answers],
            $rules,
            $messages
        )->validate();

        $normalizedAnswers = $this->normalizeAnswers(
            $labelIds,
            (array) $validated['answers']
        );

        return DB::transaction(
            function () use ($menu, $normalizedAnswers): FormResponse {
                $formResponse = new FormResponse();
                $formResponse->menu()->associate($menu);
                $formResponse->labels_data = $normalizedAnswers;
                $formResponse->status = 0;
                $formResponse->saveOrFail();

                return $formResponse->refresh();
            }
        );
    }

    private function validateMenu(Menu $menu): void
    {
        $menuType = $menu->type instanceof MenuType
            ? $menu->type->value
            : (string) $menu->type;

        if (
            $menuType !== MenuType::FORM->value
            || (int) $menu->status !== 1
        ) {
            throw ValidationException::withMessages([
                'form' => __('The selected form is unavailable.'),
            ]);
        }
    }

    private function buildValidationConfiguration(Collection $labels): array
    {
        $rules = [
            'answers' => [
                'required',
                'array',
                'min:1',
            ],
        ];

        $messages = [
            'answers.required' => __('Form answers are required.'),
            'answers.array' => __('Form answers must be provided in a valid format.'),
            'answers.min' => __('At least one form answer is required.'),
        ];

        foreach ($labels as $label) {
            $key = 'answers.' . $label->id;
            $labelName = $this->labelName($label);
            $fieldRules = [];

            if ((bool) $label->is_required) {
                $fieldRules[] = 'required';

                $messages[$key . '.required'] = __('The :field field is required.', [
                    'field' => $labelName,
                ]);
            } else {
                $fieldRules[] = 'nullable';
            }

            foreach (
                FormLabelTypeEnum::getValidationRules((string) $label->type)
                as $validationRule
            ) {
                if (! in_array($validationRule, $fieldRules, true)) {
                    $fieldRules[] = $validationRule;
                }
            }

            $rules[$key] = $fieldRules;

            $validationMessage = FormLabelTypeEnum::getValidationMessage(
                (string) $label->type,
                $labelName
            );

            foreach ([
                         'string',
                         'numeric',
                         'array',
                         'boolean',
                         'email',
                         'date',
                         'date_format',
                         'regex',
                     ] as $validationRule) {
                $messages[$key . '.' . $validationRule] = $validationMessage;
            }
        }

        return [$rules, $messages];
    }

    private function validateAnswerKeys(array $answers, array $labelIds): void
    {
        $allowedLabelIds = array_fill_keys($labelIds, true);

        foreach (array_keys($answers) as $answerKey) {
            $answerKey = (string) $answerKey;

            if (
                ! ctype_digit($answerKey)
                || ! isset($allowedLabelIds[(int) $answerKey])
            ) {
                throw ValidationException::withMessages([
                    'answers' => __('One or more submitted answers do not belong to the selected form.'),
                ]);
            }
        }
    }

    private function labelName(FormLabel $label): string
    {
        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', config('app.locale'));

        $translation = $label->translations->firstWhere('locale', $locale);

        if ($translation === null && $fallbackLocale !== $locale) {
            $translation = $label->translations->firstWhere(
                'locale',
                $fallbackLocale
            );
        }

        $translation ??= $label->translations->first();

        return trim((string) ($translation?->name ?? __('Field')));
    }

    private function normalizeAnswers(array $labelIds, array $answers): array
    {
        $normalizedAnswers = [];

        foreach ($labelIds as $labelId) {
            $value = $answers[$labelId] ?? $answers[(string) $labelId] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            $normalizedAnswers[] = [
                'label_id' => $labelId,
                'value' => $value,
            ];
        }

        return $normalizedAnswers;
    }

    public function updateStatus(
        Menu $menu,
        FormResponse $response,
        int $status
    ): FormResponse {
        return DB::transaction(function () use ($menu, $response, $status): FormResponse {
            $lockedResponse = $this->formResponseRepository->findForMenuOrFail(
                $menu,
                $response,
                true
            );

            return $this->formResponseRepository->updateStatus(
                $lockedResponse,
                $status
            );
        });
    }
}