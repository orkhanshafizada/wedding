@extends('admin.layouts.app')

@section('title', __('Customers'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('Edit Customer') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.customers.index') }}">{{ __('Customers') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                    </ol>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                @include('customer::admin.customers.form', ['customer' => $customer])
                            </div>

                            <div class="card-footer d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light">
                                    {{ __('Cancel') }}
                                </a>

                                <button class="btn btn-primary">
                                    <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
