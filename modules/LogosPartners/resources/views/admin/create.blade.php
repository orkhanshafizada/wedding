@extends('admin.layouts.app')

@section('title', __('Add New Logos Partner'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('Add New Logos Partner') }} - {{ $menu->name }}</h5>
                    <a href="{{ route('admin.logospartners.index', $menu) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.logospartners.store', $menu) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('logospartners::admin.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.logospartners.index', $menu) }}" class="btn btn-secondary">
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
