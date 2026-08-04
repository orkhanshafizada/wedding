@extends('admin.layouts.app')

@section('title', __('Edit Filter'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{__('Edit Filter')}}: {{ $filter->slug }}</h5>
                    <a href="{{ route('admin.product.filters.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> {{__('Go Back')}}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.product.filters.update', $filter) }}" method="POST"  enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('product::admin.filters.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.product.filters.index') }}" class="btn btn-secondary">
                                {{__('Cancel')}}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> {{__('Update')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

