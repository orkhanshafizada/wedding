@extends('admin.layouts.app')
@section('title', __('menus'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('Add Menu') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">{{ __('menus') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Add') }}</li>
                    </ol>
                </div>
            </div>

            <form method="post" action="{{ route('admin.menus.store') }}" enctype="multipart/form-data">
                @csrf

                @include('menu::admin.menu.partials.form', [
                    'menu' => null,
                    'submitLabel' => __('Save'),
                    'parentTree' => $parentTree,
                    'selectedParentId' => $selectedParentId,
                    'excludeId' => null,
                    'types' => $types,
                    'languages' => $languages,
                    'includedItemOptions' => $includedItemOptions,
                ])
            </form>

        </div>
    </div>
@endsection
