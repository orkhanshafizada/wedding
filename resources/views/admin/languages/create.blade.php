@extends('admin.layouts.app')
@section('title', __('Add language'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">{{ __('Add language') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.languages.store') }}">
                        @csrf
                        @include('admin.common.saveButton', ['action_cancel' => route('admin.languages.index')])
                        @include('admin.languages.form', ['language' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
