@extends('admin.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.slider.index') }}">{{ __('Sliders') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Create Slider') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.slider.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @include('slider::admin.form', [
                            'slider' => null
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
