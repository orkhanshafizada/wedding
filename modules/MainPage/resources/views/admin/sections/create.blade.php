@extends('admin.layouts.app')
@section('title', __('New Main Page Section'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('New Main Page Section') }}</h4>
                <a href="{{ route('admin.main_page.sections.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line align-bottom me-1"></i> {{ __('Back') }}
                </a>
            </div>

            <form action="{{ route('admin.main_page.sections.store') }}" method="POST">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Section Details') }}</h5>
                    </div>
                    <div class="card-body">
                        @include('mainpage::admin.sections.partials.form')
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line align-bottom me-1"></i> {{ __('Save') }}
                        </button>
                        <a href="{{ route('admin.main_page.sections.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@include('mainpage::admin.sections.partials.form-scripts')
