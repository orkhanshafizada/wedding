@extends('admin.layouts.app')

@section('title', __('Create Grid') . ' - ' . $menu->name)

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Create Grid') }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">{{ __('Menus') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.grids.index', $menu) }}">{{ __('Grids') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('Create') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @include('grids::admin.partials.form')
        </div>
    </div>
@endsection
