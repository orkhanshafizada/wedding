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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Edit Slider') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.slider.update', $slider) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('slider::admin.form', [
                            'slider' => $slider
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
