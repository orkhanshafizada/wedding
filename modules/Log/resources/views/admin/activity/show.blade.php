@extends('admin.layouts.app')
@section('title', __('Activity') . ' #' . $activity->id)
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">{{ __('Activity') }} #{{ $activity->id }}</h5>
                            <a class="btn btn-light" href="{{ route('admin.log.activity.index') }}">{{ __('Back') }}</a>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Actor') }}</div>
                                    <div>{{ $activity->actor?->fullname ?? '-' }}</div>
                                    <div class="text-muted small">{{ $activity->actor?->email ?? '' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Action') }}</div>
                                    <div>{{ $activity->action }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Module') }}</div>
                                    <div>{{ $activity->module ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold">{{ __('Subject') }}</div>
                                    <div class="text-muted small">{{ $activity->subject_type ?? '-' }} {{ $activity->subject_id ? ('#' . $activity->subject_id) : '' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-semibold">{{ __('Route / Method') }}</div>
                                    <div class="text-muted small">{{ $activity->method ?? '-' }} {{ $activity->route ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold">{{ __('IP') }}</div>
                                    <div>{{ $activity->ip ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-semibold">{{ __('Date') }}</div>
                                    <div>{{ $activity->created_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="fw-semibold">{{ __('URL') }}</div>
                                    <div class="text-muted small" style="word-break: break-word;">{{ $activity->url ?? '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="fw-semibold">{{ __('User Agent') }}</div>
                                    <div class="text-muted small" style="word-break: break-word;">{{ $activity->user_agent ?? '-' }}</div>
                                </div>

                                <div class="col-12 mt-2">
                                    <h6 class="mb-2">{{ __('Changes') }}</h6>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Field') }}</th>
                                                <th>{{ __('Old') }}</th>
                                                <th>{{ __('New') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($activity->changes as $c)
                                                <tr>
                                                    <td class="fw-semibold">{{ $c->field }}</td>
                                                    <td class="text-muted small" style="white-space: pre-wrap; word-break: break-word;">{{ $c->old_value ?? '-' }}</td>
                                                    <td class="text-muted small" style="white-space: pre-wrap; word-break: break-word;">{{ $c->new_value ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-muted">{{ __('No changes') }}</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
