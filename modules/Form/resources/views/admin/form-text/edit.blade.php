@extends('admin.layouts.app')

@section('title', __('Edit Form Text'))

@section('content')
    @php
        $requiredLanguageCodeList = $requiredLanguageCodes instanceof \Illuminate\Support\Collection
            ? $requiredLanguageCodes->all()
            : (array) $requiredLanguageCodes;
    @endphp

    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Edit Form Text') }} - {{ $menu->title }}</h4>
                <a href="{{ route('admin.form.index', $menu) }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line align-bottom me-1"></i>{{ __('Back') }}
                </a>
            </div>

            <form action="{{ route('admin.form.text.update', $menu) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Form Text Settings') }}</h5>
                    </div>
                    <div class="card-body">

                        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                            @foreach($languages as $i => $lang)
                                @php($isRequired = in_array($lang->code, $requiredLanguageCodeList, true))
                                <li class="nav-item" role="presentation">
                                    <a href="#tab-{{ $lang->code }}"
                                       class="nav-link {{ $i === 0 ? 'active' : '' }}"
                                       data-bs-toggle="tab"
                                       role="tab">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold text-uppercase">{{ $lang->code }}</span>
                                            <span>{{ $lang->name }}</span>
                                            @if($isRequired)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach($languages as $i => $lang)
                                @php($isRequired = in_array($lang->code, $requiredLanguageCodeList, true))
                                @php($tr = $formText->translations->firstWhere('locale', $lang->code))

                                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                                     id="tab-{{ $lang->code }}"
                                     role="tabpanel">

                                    <div class="mb-4">
                                        <label class="form-label">
                                            {{ __('Form Header Text') }}
                                            @if($isRequired)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <textarea name="header_text[{{ $lang->code }}]"
                                                  class="form-control js-editor @error("header_text.{$lang->code}") is-invalid @enderror"
                                                  rows="8">{{ old("header_text.{$lang->code}", $tr?->header_text) }}</textarea>
                                        @error("header_text.{$lang->code}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">
                                            {{ __('Form Success Text') }}
                                            @if($isRequired)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <textarea name="success_text[{{ $lang->code }}]"
                                                  class="form-control js-editor @error("success_text.{$lang->code}") is-invalid @enderror"
                                                  rows="8">{{ old("success_text.{$lang->code}", $tr?->success_text) }}</textarea>
                                        @error("success_text.{$lang->code}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">
                                            {{ __('Form Email Text') }}
                                            @if($isRequired)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <textarea name="email_text[{{ $lang->code }}]"
                                                  class="form-control js-editor @error("email_text.{$lang->code}") is-invalid @enderror"
                                                  rows="8">{{ old("email_text.{$lang->code}", $tr?->email_text) }}</textarea>
                                        @error("email_text.{$lang->code}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.form.index', $menu) }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line align-bottom me-1"></i> {{ __('Save') }}
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
