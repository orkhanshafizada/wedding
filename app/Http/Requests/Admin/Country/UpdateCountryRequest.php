<?php
namespace App\Http\Requests\Admin\Country;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $activeCodes = $this->activeLanguageCodes();
        $requiredCodes = $this->requiredLanguageCodes();

        $rules = [
            'iso2'         => ['required', 'string', 'size:2'],
            'iso3'         => ['nullable', 'string', 'size:3'],
            'numcode'      => ['nullable', 'string', 'max:6'],
            'un_member'    => ['nullable', 'string', 'max:12'],
            'calling_code' => ['nullable', 'string', 'max:16'],
            'cctld'        => ['nullable', 'string', 'max:8'],

            'short_names'  => ['required', 'array'],
            'long_names'   => ['required', 'array'],

            'is_active'    => ['nullable', 'boolean'],
        ];

        foreach ($activeCodes as $code) {
            $rules["short_names.{$code}"] = [
                in_array($code, $requiredCodes, true) ? 'required' : 'nullable',
                'string',
                'max:255',
            ];

            $rules["long_names.{$code}"] = [
                in_array($code, $requiredCodes, true) ? 'required' : 'nullable',
                'string',
                'max:255',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        $activeCodes = $this->activeLanguageCodes();
        $requiredCodes = $this->requiredLanguageCodes();

        $messages = [
            'iso2.required' => __('Please enter the ISO2 country code.'),
            'iso2.string'   => __('The ISO2 country code must be a valid string.'),
            'iso2.size'     => __('The ISO2 country code must be exactly :size characters.'),

            'iso3.string' => __('The ISO3 country code must be a valid string.'),
            'iso3.size'   => __('The ISO3 country code must be exactly :size characters.'),

            'numcode.string' => __('The numeric code must be a valid string.'),
            'numcode.max'    => __('The numeric code may not be greater than :max characters.'),

            'un_member.string' => __('The UN member value must be a valid string.'),
            'un_member.max'    => __('The UN member value may not be greater than :max characters.'),

            'calling_code.string' => __('The calling code must be a valid string.'),
            'calling_code.max'    => __('The calling code may not be greater than :max characters.'),

            'cctld.string' => __('The ccTLD value must be a valid string.'),
            'cctld.max'    => __('The ccTLD value may not be greater than :max characters.'),

            'short_names.required' => __('Please enter country short names.'),
            'short_names.array'    => __('Country short names must be a valid array.'),

            'long_names.required' => __('Please enter country long names.'),
            'long_names.array'    => __('Country long names must be a valid array.'),

            'is_active.boolean' => __('The active status value must be true or false.'),
        ];

        foreach ($activeCodes as $code) {
            $langLabel = mb_strtoupper((string) $code);

            if (in_array($code, $requiredCodes, true)) {
                $messages["short_names.{$code}.required"] = __('Please enter the :lang short name.', ['lang' => $langLabel]);
                $messages["long_names.{$code}.required"] = __('Please enter the :lang long name.', ['lang' => $langLabel]);
            }

            $messages["short_names.{$code}.string"] = __('The :lang short name must be a valid string.', ['lang' => $langLabel]);
            $messages["short_names.{$code}.max"]    = __('The :lang short name may not be greater than :max characters.', ['lang' => $langLabel]);

            $messages["long_names.{$code}.string"] = __('The :lang long name must be a valid string.', ['lang' => $langLabel]);
            $messages["long_names.{$code}.max"]    = __('The :lang long name may not be greater than :max characters.', ['lang' => $langLabel]);
        }

        return $messages;
    }

    public function validated($key = null, $default = null): array
    {
        /** @var array<string, mixed> $validated */
        $validated = parent::validated($key, $default);

        if (isset($validated['iso2'])) {
            $validated['iso2'] = strtoupper((string) $validated['iso2']);
        }

        if (!empty($validated['iso3'])) {
            $validated['iso3'] = strtoupper((string) $validated['iso3']);
        }

        $validated['short_names'] = $this->fillMissingTranslations(
            Arr::get($validated, 'short_names', [])
        );

        $validated['long_names'] = $this->fillMissingTranslations(
            Arr::get($validated, 'long_names', [])
        );

        $validated['is_active'] = array_key_exists('is_active', $validated)
            ? (bool) $validated['is_active']
            : true;

        return $validated;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeTranslationKeys('short_names');
        $this->sanitizeTranslationKeys('long_names');
    }

    private function sanitizeTranslationKeys(string $field): void
    {
        $activeCodes = $this->activeLanguageCodes();

        $data = $this->all();
        $translations = $data[$field] ?? null;

        if (!is_array($translations)) {
            return;
        }

        $filtered = [];
        foreach ($activeCodes as $code) {
            if (array_key_exists($code, $translations)) {
                $filtered[$code] = $translations[$code];
            }
        }

        $data[$field] = $filtered;
        $this->replace($data);
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, mixed>
     */
    private function fillMissingTranslations(array $translations): array
    {
        $activeCodes = $this->activeLanguageCodes();
        $requiredCodes = $this->requiredLanguageCodes();

        $primaryCode = $requiredCodes[0] ?? ($activeCodes[0] ?? null);
        $primaryValue = ($primaryCode !== null && !empty($translations[$primaryCode]))
            ? (string) $translations[$primaryCode]
            : null;

        foreach ($activeCodes as $code) {
            if (in_array($code, $requiredCodes, true)) {
                continue;
            }

            if (empty($translations[$code]) && $primaryValue !== null) {
                $translations[$code] = $primaryValue;
            }
        }

        return $translations;
    }

    /**
     * @return array<int, string>
     */
    private function activeLanguageCodes(): array
    {
        return Language::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function requiredLanguageCodes(): array
    {
        return Language::query()
            ->active()
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();
    }
}
