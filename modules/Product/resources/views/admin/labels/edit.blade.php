@extends('admin.layouts.app')
@section('title',__('Edit Product Label'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{__('Edit Product Label')}}</h4>
                <a href="{{ route('admin.product.labels.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line align-bottom me-1"></i> {{__('Back')}}
                </a>
            </div>

            <form action="{{ route('admin.product.labels.update', $label) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{__('Label Details')}}</h5>
                    </div>
                    <div class="card-body">
                        @include('product::admin.labels.form')
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line align-bottom me-1"></i> {{__('Update')}}
                        </button>
                        <a href="{{ route('admin.product.labels.index') }}" class="btn btn-secondary">
                            {{__('Cancel')}}
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

