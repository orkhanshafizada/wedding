<?php

namespace Modules\Form\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Modules\Form\Enums\FormLabelTypeEnum;
use Modules\Form\Models\FormLabel;
use Modules\Form\Models\FormResponse;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;

class PublicFormService
{
    public function getActiveFormData(?Menu $menu = null): array
    {
        $menu ??= $this->findActiveFormMenu();

        if ($menu === null) {
            return [
                'menu' => null,
                'labels' => new EloquentCollection(),
                'responses' => collect(),
            ];
        }

        $labels = $this->getFormLabels($menu);

        return [
            'menu' => $menu,
            'labels' => $labels,
            'responses' => $this->getApprovedResponses($menu, $labels),
        ];
    }

    private function findActiveFormMenu(): ?Menu
    {
        return Menu::query()
            ->with('translations')
            ->where('type', MenuType::FORM->value)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    private function getFormLabels(Menu $menu): EloquentCollection
    {
        return FormLabel::query()
            ->with('translations')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function getApprovedResponses(
        Menu $menu,
        EloquentCollection $labels
    ): Collection {
        $nameLabel = $labels->first(
            static fn (FormLabel $label): bool => in_array(
                (string) $label->type,
                [
                    FormLabelTypeEnum::Textbox,
                    FormLabelTypeEnum::Email,
                ],
                true
            )
        );

        $wishLabel = $labels->first(
            static fn (FormLabel $label): bool => (
                (string) $label->type === FormLabelTypeEnum::Textarea
            )
        );

        if ($nameLabel === null || $wishLabel === null) {
            return collect();
        }

        return FormResponse::query()
            ->where('menu_id', $menu->id)
            ->where('status', 1)
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function (FormResponse $response) use (
                $nameLabel,
                $wishLabel
            ): array {
                $answers = collect((array) $response->labels_data)
                    ->filter(
                        static fn (mixed $answer): bool => is_array($answer)
                            && array_key_exists('label_id', $answer)
                    )
                    ->mapWithKeys(
                        static fn (array $answer): array => [
                            (int) $answer['label_id'] => $answer['value'] ?? null,
                        ]
                    );

                return [
                    'id' => (int) $response->id,
                    'full_name' => trim(
                        (string) $answers->get((int) $nameLabel->id)
                    ),
                    'wish' => trim(
                        (string) $answers->get((int) $wishLabel->id)
                    ),
                ];
            })
            ->filter(
                static fn (array $response): bool => (
                    $response['full_name'] !== ''
                    && $response['wish'] !== ''
                )
            )
            ->values();
    }
}