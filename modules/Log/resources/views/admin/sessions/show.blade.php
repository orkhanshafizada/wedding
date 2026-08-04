@extends('admin.layouts.app')
@section('title', __('Session') . ' #' . $session->id)
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">{{ __('Session') }} #{{ $session->id }}</h5>
                            <a class="btn btn-light" href="{{ route('admin.log.sessions.index') }}">{{ __('Back') }}</a>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('User') }}</div>
                                    <div>{{ $session->user?->fullname ?? '-' }}</div>
                                    <div class="text-muted small">{{ $session->user?->email ?? '' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('IP') }}</div>
                                    <div>{{ $session->ip ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Session ID') }}</div>
                                    <div class="text-muted small">{{ $session->session_id ?? '-' }}</div>
                                </div>

                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Device') }}</div>
                                    <div>{{ $session->device_type ?? '-' }}</div>
                                    <div class="text-muted small">{{ trim(($session->device_brand ?? '') . ' ' . ($session->device_model ?? '')) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('OS') }}</div>
                                    <div>{{ $session->os ? trim($session->os . ' ' . ($session->os_version ?? '')) : '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Browser') }}</div>
                                    <div>{{ $session->browser ? trim($session->browser . ' ' . ($session->browser_version ?? '')) : '-' }}</div>
                                </div>

                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Login') }}</div>
                                    <div>{{ $session->login_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Logout') }}</div>
                                    <div>{{ $session->logout_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ __('Last activity') }}</div>
                                    <div>{{ $session->last_activity_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="fw-semibold">{{ __('User Agent') }}</div>
                                    <div class="text-muted small" style="word-break: break-word;">{{ $session->user_agent ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
