@extends('admin.layouts.app')

@section('title', 'Yeni Team Staff')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Yeni Team Staff Yarat - {{ $menu->name }}</h5>
                    <a href="{{ route('admin.team-staff.index', $menu) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Geri
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.team-staff.store', $menu) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('teamstaff::admin.team-staff.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.team-staff.index', $menu) }}" class="btn btn-secondary">
                                Ləğv et
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Yadda saxla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('teamstaff::admin.team-staff.partials.file-manager-scripts')
@endpush

