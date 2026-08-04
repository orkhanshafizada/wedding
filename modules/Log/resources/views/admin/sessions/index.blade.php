@extends('admin.layouts.app')
@section('title', __('Admin Sessions'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Admin Sessions') }}</h5>
                        </div>

                        <div class="card-body">
                            <form method="GET" class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <input type="number" name="user_id" class="form-control" placeholder="{{ __('User ID') }}" value="{{ request('user_id') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="ip" class="form-control" placeholder="{{ __('IP') }}" value="{{ request('ip') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="device_type" class="form-control" placeholder="{{ __('Device type') }}" value="{{ request('device_type') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="browser" class="form-control" placeholder="{{ __('Browser') }}" value="{{ request('browser') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="os" class="form-control" placeholder="{{ __('OS') }}" value="{{ request('os') }}">
                                </div>
                                <div class="col-md-1">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-1">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
                                    <a class="btn btn-light" href="{{ route('admin.log.sessions.index') }}">{{ __('Reset') }}</a>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('IP') }}</th>
                                        <th>{{ __('Device') }}</th>
                                        <th>{{ __('OS') }}</th>
                                        <th>{{ __('Browser') }}</th>
                                        <th>{{ __('Login') }}</th>
                                        <th>{{ __('Logout') }}</th>
                                        <th>{{ __('Last activity') }}</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($sessions as $s)
                                        <tr>
                                            <td>{{ $s->id }}</td>
                                            <td>
                                                {{ $s->user?->fullname ?? '-' }}
                                                <div class="text-muted small">{{ $s->user?->email ?? '' }}</div>
                                            </td>
                                            <td>{{ $s->ip ?? '-' }}</td>
                                            <td>
                                                {{ $s->device_type ?? '-' }}
                                                <div class="text-muted small">{{ trim(($s->device_brand ?? '') . ' ' . ($s->device_model ?? '')) }}</div>
                                            </td>
                                            <td>{{ $s->os ? trim($s->os . ' ' . ($s->os_version ?? '')) : '-' }}</td>
                                            <td>{{ $s->browser ? trim($s->browser . ' ' . ($s->browser_version ?? '')) : '-' }}</td>
                                            <td>{{ $s->login_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                            <td>{{ $s->logout_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                            <td>{{ $s->last_activity_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-soft-primary" href="{{ route('admin.log.sessions.show', $s) }}">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $sessions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
