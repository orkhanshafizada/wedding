<?php

namespace Modules\Form\Enums;

use BenSampo\Enum\Enum;

final class FormLabelTypeEnum extends Enum
{
    const Textbox = 'textbox';
    const Textarea = 'textarea';
    const Numeric = 'numeric';
    const NumericWithDot = 'numeric_with_dot';
    const SingleSelectbox = 'single_selectbox';
    const MultiSelectbox = 'multi_selectbox';
    const SingleSelectboxWithOther = 'single_selectbox_with_other';
    const Checkbox = 'checkbox';
    const CheckboxWithOther = 'checkbox_with_other';
    const Radio = 'radio';
    const RadioWithOther = 'radio_with_other';
    const File = 'file';
    const DateTime = 'datetime';
    const DateOnly = 'date_only';
    const TimeOnly = 'time_only';
    const PhoneNumber = 'phone_number';
    const Email = 'email';

    public static function getDescription($value): string
    {
        return match ($value) {
            self::Textbox => __('Textbox'),
            self::Textarea => __('Textarea'),
            self::Numeric => __('Numeric'),
            self::NumericWithDot => __('Numeric (with dot)'),
            self::SingleSelectbox => __('Single-selectbox'),
            self::MultiSelectbox => __('Multi-selectbox'),
            self::SingleSelectboxWithOther => __('Single-selectbox (with other)'),
            self::Checkbox => __('Checkbox'),
            self::CheckboxWithOther => __('Checkbox (with other)'),
            self::Radio => __('Radio'),
            self::RadioWithOther => __('Radio (with other)'),
            self::File => __('File'),
            self::DateTime => __('Date & Time'),
            self::DateOnly => __('Date only'),
            self::TimeOnly => __('Time only'),
            self::PhoneNumber => __('Phone Number'),
            self::Email => __('Email'),
            default => $value,
        };
    }

    public static function getOptions(): array
    {
        return [
            self::Textbox => self::getDescription(self::Textbox),
            self::Textarea => self::getDescription(self::Textarea),
            self::Numeric => self::getDescription(self::Numeric),
            self::NumericWithDot => self::getDescription(self::NumericWithDot),
            self::SingleSelectbox => self::getDescription(self::SingleSelectbox),
            self::MultiSelectbox => self::getDescription(self::MultiSelectbox),
            self::SingleSelectboxWithOther => self::getDescription(self::SingleSelectboxWithOther),
            self::Checkbox => self::getDescription(self::Checkbox),
            self::CheckboxWithOther => self::getDescription(self::CheckboxWithOther),
            self::Radio => self::getDescription(self::Radio),
            self::RadioWithOther => self::getDescription(self::RadioWithOther),
            self::File => self::getDescription(self::File),
            self::DateTime => self::getDescription(self::DateTime),
            self::DateOnly => self::getDescription(self::DateOnly),
            self::TimeOnly => self::getDescription(self::TimeOnly),
            self::PhoneNumber => self::getDescription(self::PhoneNumber),
            self::Email => self::getDescription(self::Email),
        ];
    }

    /**
     * Get validation rules for specific label type
     */
    public static function getValidationRules(string $type): array
    {
        return match ($type) {
            self::Textbox => ['string', 'max:255'],
            self::Textarea => ['string', 'max:5000'],
            self::Numeric => ['numeric'],
            self::NumericWithDot => ['numeric'],
            self::SingleSelectbox => ['string'],
            self::MultiSelectbox => ['array'],
            self::SingleSelectboxWithOther => ['string'],
            self::Checkbox => ['boolean'],
            self::CheckboxWithOther => ['string'],
            self::Radio => ['string'],
            self::RadioWithOther => ['string'],
            self::File => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx',
                'max:10240',
            ],
            self::DateTime => ['date_format:Y-m-d H:i:s'],
            self::DateOnly => ['date_format:Y-m-d'],
            self::TimeOnly => ['date_format:H:i:s'],
            self::PhoneNumber => ['string', 'regex:/^\+?[0-9]{10,15}$/'],
            self::Email => ['email'],
            default => ['string'],
        };
    }

    /**
     * Get validation error message for specific label type
     */
    public static function getValidationMessage(string $type, ?string $labelName = null): string
    {
        $labelName = $labelName ?? __('Field');

        return match ($type) {
            self::Textbox => __('The :label must be a text', ['label' => $labelName]),
            self::Textarea => __('The :label must be a text', ['label' => $labelName]),
            self::Numeric => __('The :label must be a number', ['label' => $labelName]),
            self::NumericWithDot => __('The :label must be a number', ['label' => $labelName]),
            self::SingleSelectbox => __('The :label must be selected', ['label' => $labelName]),
            self::MultiSelectbox => __('The :label must be an array', ['label' => $labelName]),
            self::SingleSelectboxWithOther => __('The :label must be selected', ['label' => $labelName]),
            self::Checkbox => __('The :label must be checked or unchecked', ['label' => $labelName]),
            self::CheckboxWithOther => __('The :label must be selected', ['label' => $labelName]),
            self::Radio => __('The :label must be selected', ['label' => $labelName]),
            self::RadioWithOther => __('The :label must be selected', ['label' => $labelName]),
            self::File => __('The :label must be a valid file', ['label' => $labelName]),
            self::DateTime => __('The :label must be a valid date and time (Y-m-d H:i:s)', ['label' => $labelName]),
            self::DateOnly => __('The :label must be a valid date (Y-m-d)', ['label' => $labelName]),
            self::TimeOnly => __('The :label must be a valid time (H:i:s)', ['label' => $labelName]),
            self::PhoneNumber => __('The :label must be a valid phone number', ['label' => $labelName]),
            self::Email => __('The :label must be a valid email address', ['label' => $labelName]),
            default => __('The :label is invalid', ['label' => $labelName]),
        };
    }
}
