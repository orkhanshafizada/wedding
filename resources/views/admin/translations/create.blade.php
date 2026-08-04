@extends('admin.layouts.app')

@section('title', __('Add Translation'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('Add Translation') }}</h4>
                    <p class="text-muted mb-0">{{ __('Create one translation key and fill values by language tabs.') }}</p>
                </div>

                <a href="{{ route('admin.translations.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line align-bottom me-1"></i>{{ __('Back') }}
                </a>
            </div>

            <form action="{{ route('admin.translations.store') }}" method="POST" id="translation-form">
                @csrf
                @include('admin.translations.partials.form')
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/assets/js/pages/translations-form.js') }}"></script>
@endpush
