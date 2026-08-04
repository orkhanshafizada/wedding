@extends('admin.layouts.app')
@section('title', __('Activity Logs'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Activity Logs') }}</h5>
                        </div>

                        <div class="card-body">
                            <form method="GET" class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <input type="number" name="actor_id" class="form-control" placeholder="{{ __('Actor ID') }}" value="{{ request('actor_id') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="action" class="form-control" placeholder="{{ __('Action') }}" value="{{ request('action') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="module" class="form-control" placeholder="{{ __('Module') }}" value="{{ request('module') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="subject_type" class="form-control" placeholder="{{ __('Subject type') }}" value="{{ request('subject_type') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="subject_id" class="form-control" placeholder="{{ __('Subject ID') }}" value="{{ request('subject_id') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="ip" class="form-control" placeholder="{{ __('IP') }}" value="{{ request('ip') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="route" class="form-control" placeholder="{{ __('Route') }}" value="{{ request('route') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
                                    <a class="btn btn-light" href="{{ route('admin.log.activity.index') }}">{{ __('Reset') }}</a>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Actor') }}</th>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('Module') }}</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th>{{ __('Route') }}</th>
                                        <th>{{ __('IP') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($logs as $l)
                                        <tr>
                                            <td>{{ $l->id }}</td>
                                            <td>
                                                {{ $l->actor?->fullname ?? '-' }}
                                                <div class="text-muted small">{{ $l->actor?->email ?? '' }}</div>
                                            </td>
                                            <td>{{ $l->action }}</td>
                                            <td>{{ $l->module ?? '-' }}</td>
                                            <td class="text-muted small">
                                                {{ $l->subject_type ?? '-' }}
                                                @if($l->subject_id)
                                                    <div>#{{ $l->subject_id }}</div>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $l->route ?? '-' }}</td>
                                            <td>{{ $l->ip ?? '-' }}</td>
                                            <td>{{ $l->created_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-soft-primary" href="{{ route('admin.log.activity.show', $l) }}">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
