<?php

namespace Modules\Menu\Http\Requests;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\AdminPermission\Services\AdminAccessService;
use Modules\Menu\Enums\MenuIncludedItemType;
use Modules\Menu\Enums\MenuType;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('menu.create');
    }

    public function rules(): array
    {
        $imageRules = $this->imageRules();

        return [
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'type' => ['required', 'string', 'max:50', Rule::in($this->allowedTypeValues())],
            'view_type' => ['required', 'string', 'max:50'],

            'status' => ['sometimes', 'boolean'],
            'in_header' => ['sometimes', 'boolean'],
            'in_footer' => ['sometimes', 'boolean'],
            'show_on_main_page' => ['sometimes', 'boolean'],
            'show_in_sitemap' => ['sometimes', 'boolean'],

            'icon' => ['nullable', 'string', 'max:255'],
            'icon_image' => ['nullable', 'file', ...$imageRules],
            'main_image' => ['nullable', 'file', ...$imageRules],
            'text_color' => ['nullable', 'string', 'max:20'],
            'bg_color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'included_items' => ['nullable', 'array'],
            'included_items.*.type' => ['required_with:included_items.*.id', 'string', Rule::in(MenuIncludedItemType::values())],
            'included_items.*.id' => ['required_with:included_items.*.type', 'integer', 'min:0'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', 'max:10'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.link' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string'],
            'translations.*.meta_keywords' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' => __('Selected parent menu does not exist.'),
            'type.required' => __('Menu type is required.'),
            'type.in' => __('You are not allowed to use the selected menu type.'),
            'type.max' => __('Menu type can\'t be longer than 50 characters.'),
            'view_type.required' => __('View type is required.'),
            'view_type.max' => __('View type can\'t be longer than 50 characters.'),

            'icon_image.mimes' => __('Icon image format is invalid.'),
            'icon_image.max' => __('Icon image file is too large.'),
            'main_image.mimes' => __('Main image format is invalid.'),
            'main_image.max' => __('Main image file is too large.'),

            'included_items.array' => __('Included items format is invalid.'),
            'included_items.*.type.required_with' => __('Included item type is required.'),
            'included_items.*.type.in' => __('Included item type is invalid.'),
            'included_items.*.id.required_with' => __('Included item value is required.'),
            'included_items.*.id.integer' => __('Included item value must be numeric.'),
            'included_items.*.id.min' => __('Included item value is invalid.'),

            'translations.required' => __('Translations data is required.'),
            'translations.array' => __('Translations must be an array.'),
            'translations.min' => __('At least one translation is required.'),
            'translations.*.locale.required' => __('Locale is required for each translation.'),
            'translations.*.locale.max' => __('Locale can\'t exceed 10 characters.'),
            'translations.*.name.max' => __('Name cannot be longer than 255 characters.'),
            'translations.*.title.max' => __('Title cannot be longer than 255 characters.'),
            'translations.*.link.max' => __('The link cannot be longer than 255 characters.'),
            'translations.*.meta_title.max' => __('Meta title cannot be longer than 255 characters.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $translations = collect((array) $this->input('translations', []))
            ->map(function ($row) {
                if (! is_array($row)) {
                    return [];
                }

                $row['locale'] = trim((string) ($row['locale'] ?? ''));
                $row['name'] = $this->nullableTrimmedString($row['name'] ?? null);
                $row['title'] = $this->nullableTrimmedString($row['title'] ?? null);
                $row['description'] = $this->nullableTrimmedString($row['description'] ?? null);
                $row['link'] = $this->nullableTrimmedString($row['link'] ?? null);
                $row['meta_title'] = $this->nullableTrimmedString($row['meta_title'] ?? null);
                $row['meta_description'] = $this->nullableTrimmedString($row['meta_description'] ?? null);
                $row['meta_keywords'] = $this->nullableTrimmedString($row['meta_keywords'] ?? null);

                return $row;
            })
            ->values()
            ->all();

        $includedItems = collect((array) $this->input('included_items', []))
            ->map(function ($row): ?array {
                if (! is_array($row)) {
                    return null;
                }

                $type = trim((string) ($row['type'] ?? ''));
                $id = (int) ($row['id'] ?? 0);

                if ($type === '') {
                    return null;
                }

                if (! $this->allowsZeroIncludedId($type) && $id <= 0) {
                    return null;
                }

                if ($this->allowsZeroIncludedId($type)) {
                    $id = 0;
                }

                return [
                    'type' => $type,
                    'id' => $id,
                ];
            })
            ->filter()
            ->unique(fn (array $row): string => $row['type'] . ':' . $row['id'])
            ->values()
            ->all();

        $this->merge([
            'type' => trim((string) $this->input('type')),
            'view_type' => $this->normalizeViewType($this->input('view_type')),
            'status' => $this->boolean('status'),
            'in_header' => $this->boolean('in_header'),
            'in_footer' => $this->boolean('in_footer'),
            'show_on_main_page' => $this->boolean('show_on_main_page'),
            'show_in_sitemap' => $this->boolean('show_in_sitemap'),
            'included_items' => $includedItems,
            'translations' => $translations,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $translations = collect((array) $this->input('translations', []));
            $translationsByLocale = $translations
                ->mapWithKeys(function ($row, $index) {
                    $locale = trim((string) ($row['locale'] ?? ''));

                    return $locale !== '' ? [$locale => ['index' => $index, 'row' => $row]] : [];
                });

            foreach ($this->requiredLocaleCodes() as $locale) {
                if (! $translationsByLocale->has($locale)) {
                    $validator->errors()->add('translations', __('Required language translation is missing.') . ' (' . $locale . ')');
                    continue;
                }

                $item = $translationsByLocale->get($locale);
                $name = trim((string) ($item['row']['name'] ?? ''));

                if ($name === '') {
                    $validator->errors()->add(
                        'translations.' . $item['index'] . '.name',
                        __('The name field is required for each required language.') . ' (' . $locale . ')'
                    );
                }
            }

            foreach ($translations as $index => $row) {
                $link = trim((string) ($row['link'] ?? ''));

                if ($link !== '' && $this->linkExists($link)) {
                    $validator->errors()->add(
                        'translations.' . $index . '.link',
                        __('This link is already used in another menu.')
                    );
                }
            }

            foreach ((array) $this->input('included_items', []) as $index => $item) {
                $type = trim((string) ($item['type'] ?? ''));
                $id = (int) ($item['id'] ?? 0);

                if ($type === MenuIncludedItemType::MENU->value && ! DB::table('menus')->where('id', $id)->exists()) {
                    $validator->errors()->add('included_items.' . $index . '.id', __('Selected included menu does not exist.'));
                }

                if ($type === MenuIncludedItemType::MENU->value && ! $this->userCanReferenceMenu($id)) {
                    $validator->errors()->add('included_items.' . $index . '.id', __('You are not allowed to include the selected menu.'));
                }

                if ($type === MenuIncludedItemType::BRAND->value && ! DB::table('product_filters')->where('id', $id)->whereNull('deleted_at')->exists()) {
                    $validator->errors()->add('included_items.' . $index . '.id', __('Selected included brand filter does not exist.'));
                }
            }

            $parentId = (int) $this->input('parent_id');

            if ($parentId > 0 && ! $this->userCanReferenceMenu($parentId)) {
                $validator->errors()->add('parent_id', __('You are not allowed to use the selected parent menu.'));
            }
        });
    }

    protected function requiredLocaleCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->map(static fn ($code): string => trim((string) $code))
            ->filter()
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallbackCode = (string) Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code');

        return $fallbackCode !== '' ? [$fallbackCode] : [];
    }

    protected function imageRules(): array
    {
        $allowedImages = Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $maxImageSizeKb = $this->parseSizeToKilobytes((string) Settings::get('file_manager', 'max_image_size', '10MB'));

        return [
            'mimes:' . implode(',', $allowedImages),
            'max:' . $maxImageSizeKb,
        ];
    }

    protected function parseSizeToKilobytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 10 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $value, $matches)) {
            $number = (int) $matches[1];
            $unit = strtolower($matches[2] ?? 'mb');

            return match ($unit) {
                'kb' => $number,
                'gb' => $number * 1024 * 1024,
                default => $number * 1024,
            };
        }

        return (int) $value;
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function linkExists(string $link, ?int $ignoreMenuId = null): bool
    {
        $query = DB::table('menu_translations')->where('link', $link);

        if ($ignoreMenuId !== null) {
            $query->where('menu_id', '!=', $ignoreMenuId);
        }

        return $query->exists();
    }

    private function allowedTypeValues(): array
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return [];
        }

        $accessService = app(AdminAccessService::class);

        if ($accessService->isSuperAdmin($user)) {
            return $this->allMenuTypeValues();
        }

        return collect($accessService->allowedMenuTypeValues($user))
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function userCanReferenceMenu(int $menuId): bool
    {
        $user = $this->user();

        if (! $user instanceof User || $menuId <= 0) {
            return false;
        }

        $accessService = app(AdminAccessService::class);

        if ($accessService->isSuperAdmin($user)) {
            return true;
        }

        return $accessService->can($user, 'menu.view')
            || $accessService->can($user, 'menu.content', DB::table('menus')->where('id', $menuId)->first())
            || in_array($menuId, $accessService->allowedMenuIdsForActions($user, ['view', 'content', 'edit', 'delete']), true);
    }

    private function allMenuTypeValues(): array
    {
        return array_map(
            static fn (MenuType $type): string => $type->value,
            MenuType::cases()
        );
    }

    private function normalizeViewType(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : 'default';
    }

    private function allowsZeroIncludedId(string $type): bool
    {
        return in_array($type, [
            MenuIncludedItemType::SLIDER->value,
            MenuIncludedItemType::SELF->value,
        ], true);
    }
}
