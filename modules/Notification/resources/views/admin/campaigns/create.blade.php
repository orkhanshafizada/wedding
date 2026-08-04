@extends('admin.layouts.app')

@section('title', __('Create notification campaign'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">{{ __('Create notification campaign') }}</h4>
                    <p class="text-muted mb-0">{{ __('Prepare the target audience, channels and template, then create the campaign as draft.') }}</p>
                </div>

                <a href="{{ route('admin.notification.campaigns.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('Back') }}
                </a>
            </div>

            <form method="POST" action="{{ route('admin.notification.campaigns.store') }}" id="campaignCreateForm">
                @csrf

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="avatar-sm flex-shrink-0">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                            <i class="ri-megaphone-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ __('Campaign setup') }}</h5>
                                        <p class="text-muted mb-0">{{ __('Choose template, sending channels and audience rules.') }}</p>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">{{ __('Template') }}</label>
                                        <select class="form-select @error('notification_template_id') is-invalid @enderror" name="notification_template_id" required>
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($templates as $template)
                                                @php
                                                    $templateName = $template->translations->first()?->name ?: $template->key;
                                                @endphp
                                                <option value="{{ $template->id }}" @selected(old('notification_template_id') == $template->id)>
                                                    {{ $templateName }} ({{ $template->key }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('notification_template_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">{{ __('Audience') }}</label>
                                        <select class="form-select @error('audience_type') is-invalid @enderror" name="audience_type" id="audience_type" required>
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($audiences as $audience)
                                                <option value="{{ $audience['value'] }}" @selected(old('audience_type') === $audience['value'])>
                                                    {{ $audience['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('audience_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold d-block">{{ __('Channels') }}</label>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="card border rounded-3 p-3 h-100 cursor-pointer">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="channels[]" value="email" id="ch_email"
                                                            @checked(is_array(old('channels')) && in_array('email', old('channels'), true))>
                                                        <span class="form-check-label fw-semibold" for="ch_email">{{ __('Email') }}</span>
                                                    </div>
                                                    <div class="text-muted small mt-2">{{ __('Recommended for newsletters, promotions and transactional content.') }}</div>
                                                </label>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="card border rounded-3 p-3 h-100 cursor-pointer">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="channels[]" value="sms" id="ch_sms"
                                                            @checked(is_array(old('channels')) && in_array('sms', old('channels'), true))>
                                                        <span class="form-check-label fw-semibold" for="ch_sms">{{ __('SMS') }}</span>
                                                    </div>
                                                    <div class="text-muted small mt-2">{{ __('Short, urgent and direct message flow. Provider integration required.') }}</div>
                                                </label>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="card border rounded-3 p-3 h-100 cursor-pointer">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="channels[]" value="push" id="ch_push"
                                                            @checked(is_array(old('channels')) && in_array('push', old('channels'), true))>
                                                        <span class="form-check-label fw-semibold" for="ch_push">{{ __('Push') }}</span>
                                                    </div>
                                                    <div class="text-muted small mt-2">{{ __('Web or mobile push. Provider integration required.') }}</div>
                                                </label>
                                            </div>
                                        </div>

                                        @error('channels')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                        @error('channels.*')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm" id="filtersCard">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="avatar-sm flex-shrink-0">
                                        <div class="avatar-title bg-info-subtle text-info rounded-circle fs-20">
                                            <i class="ri-filter-3-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ __('Audience filters') }}</h5>
                                        <p class="text-muted mb-0">{{ __('Additional filters appear based on the selected audience type.') }}</p>
                                    </div>
                                </div>

                                <div id="filters_area">
                                    <div class="row g-4">
                                        <div class="col-md-4 d-none" data-filter="order_status">
                                            <label class="form-label fw-semibold">{{ __('Order status') }}</label>
                                            <input type="text" class="form-control @error('filters.order_status') is-invalid @enderror" name="filters[order_status]" value="{{ old('filters.order_status') }}" placeholder="processing">
                                            <div class="form-text">{{ __('Example: processing, completed, shipped') }}</div>
                                            @error('filters.order_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 d-none" data-filter="emails">
                                            <label class="form-label fw-semibold">{{ __('Emails') }}</label>
                                            <textarea class="form-control @error('filters.emails') is-invalid @enderror" rows="8" name="filters[emails]" placeholder="john@example.com&#10;jane@example.com">{{ old('filters.emails') }}</textarea>
                                            <div class="form-text">{{ __('Write one email per line. Example: john@example.com') }}</div>
                                            @error('filters.emails')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 d-none" data-filter="phones">
                                            <label class="form-label fw-semibold">{{ __('Phones') }}</label>
                                            <textarea class="form-control @error('filters.phones') is-invalid @enderror" rows="8" name="filters[phones]" placeholder="+994501112233&#10;+994551234567">{{ old('filters.phones') }}</textarea>
                                            <div class="form-text">{{ __('Write one phone per line. Example: +994501112233') }}</div>
                                            @error('filters.phones')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 d-none" data-filter="abandoned_days">
                                            <label class="form-label fw-semibold">{{ __('Abandoned cart days') }}</label>
                                            <input type="number" min="1" class="form-control @error('filters.abandoned_days') is-invalid @enderror" name="filters[abandoned_days]" value="{{ old('filters.abandoned_days', 7) }}">
                                            <div class="form-text">{{ __('Example: 7 means customers with carts abandoned for at least 7 days.') }}</div>
                                            @error('filters.abandoned_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="alert alert-light border mb-0" id="audienceHelpBox">
                                                <div class="fw-semibold mb-1">{{ __('Selection guide') }}</div>
                                                <div class="text-muted small mb-0">{{ __('Choose an audience to see required input format and examples.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.notification.campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> {{ __('Create campaign') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const audienceField = document.getElementById('audience_type');
            const helpBox = document.getElementById('audienceHelpBox');

            const audienceHelp = {
                customers_all: '{{ __('All customers in the system will be targeted.') }}',
                customers_active: '{{ __('Only active customers will be targeted.') }}',
                customers_inactive: '{{ __('Only inactive customers will be targeted.') }}',
                customers_with_orders: '{{ __('Only customers who have at least one order will be targeted.') }}',
                customers_without_orders: '{{ __('Only customers without any order will be targeted.') }}',
                customers_order_status: '{{ __('Enter order status as plain text. Example: processing or completed.') }}',
                subscribers_all: '{{ __('All subscribers will be targeted.') }}',
                customers_abandoned_cart: '{{ __('Enter minimum abandoned cart day count. Example: 7.') }}',
                customers_specific_emails: '{{ __('Write one email per line. Example: john@example.com') }}',
                customers_specific_phones: '{{ __('Write one phone per line. Example: +994501112233') }}',
            };

            function hideAllFilters() {
                document.querySelectorAll('#filters_area [data-filter]').forEach(function (element) {
                    element.classList.add('d-none');
                });
            }

            function showFilter(key) {
                document.querySelectorAll('#filters_area [data-filter="' + key + '"]').forEach(function (element) {
                    element.classList.remove('d-none');
                });
            }

            function renderAudienceHelp(value) {
                const message = audienceHelp[value] || '{{ __('Choose an audience to see required input format and examples.') }}';

                helpBox.innerHTML = '' +
                    '<div class="fw-semibold mb-1">{{ __('Selection guide') }}</div>' +
                    '<div class="text-muted small mb-0">' + message + '</div>';
            }

            function toggleFilters() {
                const value = audienceField.value;

                hideAllFilters();
                renderAudienceHelp(value);

                if (value === 'customers_order_status') {
                    showFilter('order_status');
                }

                if (value === 'customers_specific_emails') {
                    showFilter('emails');
                }

                if (value === 'customers_specific_phones') {
                    showFilter('phones');
                }

                if (value === 'customers_abandoned_cart') {
                    showFilter('abandoned_days');
                }
            }

            if (audienceField) {
                audienceField.addEventListener('change', toggleFilters);
                toggleFilters();
            }
        })();
    </script>
@endpush
