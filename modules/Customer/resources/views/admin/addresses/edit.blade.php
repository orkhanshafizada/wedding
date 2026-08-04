@extends('admin.layouts.app')

@section('title', __('Edit Address'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0">{{ __('Edit Address') }}</h4>
                    <p class="text-muted mb-0">{{ trim($customer->name . ' ' . $customer->surname) }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.customers.addresses.update', [$customer, $address]) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        @include('customer::admin.addresses.form', ['address' => $address])
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
