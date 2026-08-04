@extends('admin.layouts.app')

@section('title', __('Campaign') . ' #' . $campaign->id)

@section('content')
    @php
        $statusLabel = \Modules\Notification\Enums\NotificationCampaignStatus::tryFrom((string) $campaign->status)?->label() ?? $campaign->status;

        $statusBadge = match ((string) $campaign->status) {
            \Modules\Notification\Enums\NotificationCampaignStatus::DRAFT->value => 'bg-secondary-subtle text-secondary',
            \Modules\Notification\Enums\NotificationCampaignStatus::QUEUED->value => 'bg-warning-subtle text-warning',
            \Modules\Notification\Enums\NotificationCampaignStatus::SENDING->value => 'bg-info-subtle text-info',
            \Modules\Notification\Enums\NotificationCampaignStatus::COMPLETED->value => 'bg-success-subtle text-success',
            \Modules\Notification\Enums\NotificationCampaignStatus::FAILED->value => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };

        $isProcessing = in_array((string) $campaign->status, [
            \Modules\Notification\Enums\NotificationCampaignStatus::QUEUED->value,
            \Modules\Notification\Enums\NotificationCampaignStatus::SENDING->value,
        ], true);
    @endphp

    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h4 class="mb-0">{{ __('Campaign') }} #{{ $campaign->id }}</h4>
                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="text-muted small">
                        {{ __('Template') }}: <span class="fw-semibold">{{ $campaign->template->translations->first()?->name ?? $campaign->template->key ?? '-' }}</span>
                        &nbsp;|&nbsp;
                        {{ __('Created by') }}: <span class="fw-semibold">{{ $campaign->creator?->fullname ?? '-' }}</span>
                        &nbsp;|&nbsp;
                        {{ __('Started by') }}: <span class="fw-semibold">{{ $campaign->starter?->fullname ?? '-' }}</span>
                        &nbsp;|&nbsp;
                        {{ __('Started at') }}: <span class="fw-semibold">{{ $campaign->started_at?->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.notification.campaigns.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('Back') }}
                    </a>

                    @if($campaign->status === \Modules\Notification\Enums\NotificationCampaignStatus::DRAFT->value)
                        @can('notification.send.create')
                            <form method="POST" action="{{ route('admin.notification.campaigns.start', $campaign) }}" class="js-campaign-start-form">
                                @csrf
                                <button class="btn btn-success js-campaign-start-btn" type="submit">
                                    <span class="js-default-label">
                                        <i class="ri-play-line me-1"></i> {{ __('Start') }}
                                    </span>
                                    <span class="js-loading-label d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('Starting...') }}
                                    </span>
                                </button>
                            </form>
                        @endcan
                    @endif

                    @can('notification.send.create')
                        <form method="POST" action="{{ route('admin.notification.campaigns.requeue-failed', $campaign) }}" class="js-campaign-requeue-form">
                            @csrf
                            <button class="btn btn-warning js-campaign-requeue-btn" type="submit">
                                <span class="js-default-label">
                                    <i class="ri-refresh-line me-1"></i> {{ __('Requeue failed') }}
                                </span>
                                <span class="js-loading-label d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('Requeueing...') }}
                                </span>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            @if($isProcessing)
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <div>{{ __('Campaign is processing in background. This page refreshes automatically.') }}</div>
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">{{ __('Targets') }}</p>
                            <h3 class="mb-0">{{ (int) $campaign->total_targets }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">{{ __('Queued') }}</p>
                            <h3 class="mb-0">{{ (int) $campaign->total_queued }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">{{ __('Sent') }}</p>
                            <h3 class="mb-0 text-success">{{ (int) $campaign->total_sent }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">{{ __('Failed') }}</p>
                            <h3 class="mb-0 text-danger">{{ (int) $campaign->total_failed }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">{{ __('Skipped') }}</p>
                            <h3 class="mb-0 text-muted">{{ (int) $campaign->total_skipped }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">{{ __('Campaign messages') }}</h5>
                </div>
                <div class="card-body pt-3">
                    @include('admin.partials.datatable', [
                        'tableId' => 'campaignMessagesTable',
                        'columns' => $columns,
                        'rows' => $formattedRows,
                        'checkboxes' => false,
                        'actions' => false,
                        'pageLength' => 25,
                        'order' => [0, 'desc'],
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('.js-campaign-start-form, .js-campaign-requeue-form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('button');
                    const defaultLabel = button.querySelector('.js-default-label');
                    const loadingLabel = button.querySelector('.js-loading-label');

                    button.setAttribute('disabled', 'disabled');

                    if (defaultLabel) {
                        defaultLabel.classList.add('d-none');
                    }

                    if (loadingLabel) {
                        loadingLabel.classList.remove('d-none');
                    }
                });
            });

            const shouldRefresh = @json($isProcessing);

            if (shouldRefresh) {
                window.setTimeout(function () {
                    window.location.reload();
                }, 5000);
            }
        })();
    </script>
@endpush
