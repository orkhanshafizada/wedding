@extends('admin.layouts.app')
@section('title', __('menus'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('Edit Menu') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">{{ __('menus') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                    </ol>
                </div>
            </div>

            <form method="post" action="{{ route('admin.menus.update', $menu) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('menu::admin.menu.partials.form', [
                    'menu' => $menu,
                    'submitLabel' => __('Update'),
                    'parentTree' => $parentTree,
                    'selectedParentId' => $menu->parent_id,
                    'excludeId' => $menu->id,
                    'types' => $types,
                    'languages' => $languages,
                    'includedItemOptions' => $includedItemOptions,
                ])
            </form>

        </div>
    </div>
@endsection
