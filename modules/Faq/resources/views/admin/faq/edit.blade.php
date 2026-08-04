@extends('admin.layouts.app')

@section('title', 'FAQ Redaktə Et')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">FAQ Redaktə Et - {{ $menu->name }}</h5>
                    <a href="{{ route('admin.faq.index', $menu) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Geri
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.faq.update', [$menu, $faq]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('faq::admin.faq.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.faq.index', $menu) }}" class="btn btn-secondary">
                                Ləğv et
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Yenilə
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

