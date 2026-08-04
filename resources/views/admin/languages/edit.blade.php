@extends('admin.layouts.app')

@section('title', __('Edit language'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">{{ __('Edit language') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.languages.update', $language) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.common.saveButton', ['action_cancel' => route('admin.languages.index')])
                        @include('admin.languages.form', ['language' => $language])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
