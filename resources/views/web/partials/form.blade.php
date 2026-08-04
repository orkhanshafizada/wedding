@if($wishFormMenu && $wishFormLabels->isNotEmpty())
    <section class="wishes-section section-padding" id="wishes">
        <div class="container">
            <div class="wishes-card reveal-card">
                <div class="section-heading">
                    <p class="section-kicker">Arzularınız</p>

                    <h2>Öz Arzularınızı Qeyd Edin</h2>

                    <p>
                        Bizim üçün xoş sözlərinizi və təbriklərinizi yazmağınız çox dəyərlidir.
                    </p>
                </div>

                <form
                        class="wishes-form"
                        action="{{ route('api.v1.forms.menus.responses.store', ['menu' => $wishFormMenu->uuid ?: $wishFormMenu->id]) }}"
                        method="POST"
                        data-wishes-form
                >
                    @foreach($wishFormLabels as $label)
                        @php
                            $translation = $label->translations->firstWhere('locale', app()->getLocale())
                                ?? $label->translations->firstWhere('locale', config('app.fallback_locale'))
                                ?? $label->translations->first();

                            $labelName = $translation?->name ?? 'Field';
                            $placeholder = $translation?->placeholder ?? $labelName;
                            $fieldName = 'answers[' . $label->id . ']';
                            $fieldId = 'form-answer-' . $label->id;
                            $labelType = (string) $label->type;
                        @endphp

                        <label for="{{ $fieldId }}">
                            <span>{{ $labelName }}</span>

                            @if($labelType === \Modules\Form\Enums\FormLabelTypeEnum::Textarea)
                                <div class="emoji-textarea" data-emoji-textarea>
                                    <textarea
                                            id="{{ $fieldId }}"
                                            name="{{ $fieldName }}"
                                            rows="5"
                                            placeholder="{{ $placeholder }}"
                                            data-emoji-input
                                        @required((bool) $label->is_required)
                                    ></textarea>

                                    <div
                                            class="emoji-picker"
                                            role="group"
                                            aria-label="Emojilər"
                                            data-emoji-picker
                                    >
                                        @foreach(['😊', '😍', '🥰', '❤️', '💕', '💖', '🌸', '✨', '🎉', '💍', '🤲', '🫶'] as $emoji)
                                            <button
                                                    class="emoji-button"
                                                    type="button"
                                                    aria-label="{{ $emoji }} əlavə et"
                                                    data-emoji="{{ $emoji }}"
                                            >
                                                {{ $emoji }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::Email)
                                <input
                                        id="{{ $fieldId }}"
                                        type="email"
                                        name="{{ $fieldName }}"
                                        placeholder="{{ $placeholder }}"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::PhoneNumber)
                                <input
                                        id="{{ $fieldId }}"
                                        type="tel"
                                        name="{{ $fieldName }}"
                                        placeholder="{{ $placeholder }}"
                                        inputmode="tel"
                                        autocomplete="tel"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::Numeric)
                                <input
                                        id="{{ $fieldId }}"
                                        type="number"
                                        name="{{ $fieldName }}"
                                        placeholder="{{ $placeholder }}"
                                        inputmode="numeric"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::NumericWithDot)
                                <input
                                        id="{{ $fieldId }}"
                                        type="number"
                                        step="any"
                                        name="{{ $fieldName }}"
                                        placeholder="{{ $placeholder }}"
                                        inputmode="decimal"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::DateOnly)
                                <input
                                        id="{{ $fieldId }}"
                                        type="date"
                                        name="{{ $fieldName }}"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::DateTime)
                                <input
                                        id="{{ $fieldId }}"
                                        type="datetime-local"
                                        name="{{ $fieldName }}"
                                        @required((bool) $label->is_required)
                                >
                            @elseif($labelType === \Modules\Form\Enums\FormLabelTypeEnum::TimeOnly)
                                <input
                                        id="{{ $fieldId }}"
                                        type="time"
                                        name="{{ $fieldName }}"
                                        @required((bool) $label->is_required)
                                >
                            @else
                                <input
                                        id="{{ $fieldId }}"
                                        type="text"
                                        name="{{ $fieldName }}"
                                        placeholder="{{ $placeholder }}"
                                        @required((bool) $label->is_required)
                                >
                            @endif
                        </label>
                    @endforeach

                    <button class="primary-button" type="submit">
                        Arzumu Göndər
                    </button>

                    <p
                            class="form-status"
                            data-form-status
                            aria-live="polite"
                    ></p>
                </form>

                @if($approvedWishes->isNotEmpty())
                    <div class="guest-wishes" data-guest-wishes>
                        @foreach($approvedWishes as $approvedWish)
                            <article class="guest-wish">
                                <strong>{{ $approvedWish['full_name'] }}</strong>

                                <p>{{ $approvedWish['wish'] }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif