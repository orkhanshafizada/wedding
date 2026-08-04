@extends('admin.layouts.app')

@section('title', __('Form Data'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Form Data') }} - {{ $menu->title }}</h4>
                <a href="{{ route('admin.form.index', $menu) }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('Back to Form') }}
                </a>
            </div>

            @include('admin.partials.datatable', [
                'tableId' => 'formDataTable',
                'columns' => $columns,
                'rows' => $formattedRows,
                'checkboxes' => true,
                'actions' => true,
                'exportButton' => true,
                'exportRoute' => route('admin.form.export-data', $menu),
                'deleteButton' => true,
                'deleteRoute' => route('admin.form.response.destroy', ['menu' => $menu->id, 'response' => ':id']),
                'bulkDeleteRoute' => route('admin.form.bulk-delete', $menu),
                'pageLength' => 10,
                'order' => [1, 'desc'],
            ])
        </div>
    </div>
@endsection
