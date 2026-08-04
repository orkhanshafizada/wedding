@extends('admin.layouts.app')

@section('title', __('New Label'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('New Label') }}</h5>
                    <a href="{{ route('admin.form.index', $menu) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> {{ __('Go Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.form.label.store', $menu) }}" method="POST">
                        @csrf
                        @include('form::admin.form-label.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.form.index', $menu) }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
