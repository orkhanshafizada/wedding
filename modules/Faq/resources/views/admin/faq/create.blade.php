@extends('admin.layouts.app')

@section('title', 'Yeni FAQ')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Yeni FAQ Yarat - {{ $menu->name }}</h5>
                    <a href="{{ route('admin.faq.index', $menu) }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Geri
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.faq.store', $menu) }}" method="POST">
                        @csrf
                        @include('faq::admin.faq.form')

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.faq.index', $menu) }}" class="btn btn-secondary">
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

