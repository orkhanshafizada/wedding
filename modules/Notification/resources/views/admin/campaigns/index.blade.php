@extends('admin.layouts.app')

@section('title', __('Notification campaigns'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">{{ __('Notification campaigns') }}</h4>
                    <p class="text-muted mb-0">{{ __('Create, start and track email, SMS and push campaigns from one place.') }}</p>
                </div>

                @can('notification.send.create')
                    <a href="{{ route('admin.notification.campaigns.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i> {{ __('Create campaign') }}
                    </a>
                @endcan
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @include('admin.partials.datatable', [
                        'tableId' => 'notificationCampaignsTable',
                        'columns' => $columns,
                        'rows' => $formattedRows,
                        'checkboxes' => false,
                        'actions' => false,
                        'deleteButton' => false,
                        'pageLength' => 10,
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
            document.querySelectorAll('.js-campaign-start-form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('.js-campaign-start-btn');
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
        })();
    </script>
@endpush
