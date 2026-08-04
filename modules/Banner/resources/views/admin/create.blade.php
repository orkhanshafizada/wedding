@extends('admin.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.banner.index') }}">{{ __('Banners') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Create Banner') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.banner.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @include('banner::admin.form', [
                            'banner' => null,
                            'languages' => $languages,
                            'requiredLanguageCodes' => $requiredLanguageCodes,
                            'positionOptions' => $positionOptions
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
