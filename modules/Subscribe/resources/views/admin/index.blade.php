@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Subscribes') }}</h4>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.subscribe.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                class="form-control"
                                placeholder="{{ __('E-mail üzrə axtar...') }}"
                            >
                        </div>
                        <div class="col-md-2">
                            <select name="per_page" class="form-select">
                                @foreach([10,20,50,100] as $size)
                                    <option value="{{ $size }}" @selected((int)request('per_page', 20) === $size)>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">{{ __('Filter') }}</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-nowrap align-middle mb-0">
                            <thead>
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>{{ __('E-mail') }}</th>
                                <th>{{ __('IP') }}</th>
                                <th>{{ __('Created') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($subscribes as $subscribe)
                                <tr>
                                    <td>{{ $subscribe->id }}</td>
                                    <td>{{ $subscribe->email }}</td>
                                    <td>{{ $subscribe->ip }}</td>
                                    <td>{{ $subscribe->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">{{ __('Məlumat tapılmadı.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $subscribes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
