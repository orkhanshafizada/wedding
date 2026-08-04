@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header border-0 d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">{{ __('Slider Transfer') }}</h4>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.transfer.index') }}" class="btn btn-light">
                            {{ __('Back') }}
                        </a>

                        <form method="POST" action="{{ route('admin.transfer.sliders.import') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ __('Start Slider Transfer') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Module ID') }}</div>
                                <div class="fw-semibold">{{ $preview['module_id'] }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Module Name') }}</div>
                                <div class="fw-semibold">{{ $preview['module_name'] }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Code') }}</div>
                                <div class="fw-semibold">{{ $preview['module_code'] }}</div>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Status') }}</div>
                                <div class="fw-semibold">{{ $preview['status'] ? __('Active') : __('Inactive') }}</div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Count') }}</div>
                                <div class="fw-semibold">{{ $preview['count'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Sort') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Button text') }}</th>
                                <th>{{ __('Button link') }}</th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Mobile image') }}</th>
                                <th>{{ __('Hide text mobile') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($preview['slides'] as $index => $slide)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $slide['sort_order'] }}</td>
                                    <td>{{ $slide['title'] }}</td>
                                    <td>{{ $slide['description'] }}</td>
                                    <td>{{ $slide['button_text'] }}</td>
                                    <td>{{ $slide['button_link'] }}</td>
                                    <td>
                                        <div>{{ $slide['image'] }}</div>
                                        <small class="{{ $slide['image_exists'] ? 'text-success' : 'text-danger' }}">
                                            {{ $slide['image_exists'] ? __('Found') : __('Missing') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ $slide['mobile_image'] }}</div>
                                        <small class="{{ $slide['mobile_image_exists'] ? 'text-success' : 'text-danger' }}">
                                            {{ $slide['mobile_image_exists'] ? __('Found') : __('Missing') }}
                                        </small>
                                    </td>
                                    <td>{{ $slide['hide_text_mobile'] ? __('Yes') : __('No') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('No slides found.') }}</td>
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
@endsection
