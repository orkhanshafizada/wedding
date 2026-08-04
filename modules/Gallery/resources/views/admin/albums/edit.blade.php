@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('Edit Album') }} - {{ $menu->name }}</h4>
                <a href="{{ route('admin.gallery.index', $menu) }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i> {{ __('Back') }}
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.gallery.update', [$menu, $album]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('General Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $album->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Status') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="show_album" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_album" id="show_album" value="1"
                                        {{ old('show_album', $album->show_album) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_album">{{ __('Show Album') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">{{ __('Cover Image') }}</label>
                            @if($album->cover_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $album->cover_image) }}"
                                         alt="{{ $album->name }}"
                                         style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                            <small class="text-muted">{{ __('Maximum size: 5MB') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach($languages as $index => $lang)
                                <li class="nav-item">
                                    <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                       data-bs-toggle="tab"
                                       href="#lang-{{ $lang->code }}"
                                       role="tab">
                                        {{ $lang->native_name }}
                                        @if($lang->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            @foreach($languages as $index => $lang)
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                     id="lang-{{ $lang->code }}"
                                     role="tabpanel">
                                    <div class="mb-3">
                                        <label for="name_{{ $lang->code }}" class="form-label">
                                            {{ __('Name') }}
                                            @if($lang->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="text"
                                               class="form-control @error('name.'.$lang->code) is-invalid @enderror"
                                               id="name_{{ $lang->code }}"
                                               name="name[{{ $lang->code }}]"
                                               value="{{ old('name.'.$lang->code, optional($album->translation($lang->code))->name ?? '') }}"
                                               @if($lang->is_required) required @endif>
                                        @error('name.'.$lang->code)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> {{ __('Update') }}
                    </button>
                    <a href="{{ route('admin.gallery.index', $menu) }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
