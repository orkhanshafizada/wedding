@extends('admin.layouts.app')

@section('title', __('Grids') . ' - ' . $menu->name)

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Grids') }} - {{ $menu->name }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">{{ __('Menus') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('Grids') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('Grids List') }}</h5>
                            <a href="{{ route('admin.grids.create', $menu) }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('Add New') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @include('admin.partials.datatable', [
                                'tableId' => 'gridsTable',
                                'columns' => $columns,
                                'rows' => $rows,
                                'checkboxes' => true,
                                'actions' => true,
                                'exportButton' => false,
                                'editRoute' => route('admin.grids.edit', [$menu, ':id']),
                                'deleteButton' => true,
                                'deleteRoute' => route('admin.grids.destroy', [$menu, ':id']),
                                'bulkDeleteRoute' => route('admin.grids.bulk-delete', $menu),
                                'pageLength' => 10,
                                'order' => [0, 'desc'],
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
