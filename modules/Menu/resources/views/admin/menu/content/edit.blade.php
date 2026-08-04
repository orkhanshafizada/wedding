@extends('admin.layouts.app')
@section('title', __('Content'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ $menu->name }}</h4>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line align-bottom me-1"></i>{{ __('Back') }}
                </a>
            </div>

            <form action="{{ route('admin.menus.content.update', $menu) }}" method="POST" enctype="multipart/form-data" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="card mt-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">{{ __('Files') }}</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary js-files-pick">
                            <i class="ri-upload-cloud-2-line me-1"></i>{{ __('Upload') }}
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="p-4 rounded-3 border border-2 border-dashed text-center position-relative js-file-dropzone"
                             style="background: rgba(13,110,253,.04); cursor: pointer;"
                             data-input-id="contentFilesInput">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                                 style="width:56px;height:56px;background:rgba(13,110,253,.12);">
                                <i class="ri-folder-upload-line fs-2"></i>
                            </div>

                            <div class="fw-semibold fs-5">{{ __('Drag & drop files here') }}</div>
                            <div class="text-muted">{{ __('or click to select files') }}</div>

                            <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark">{{ __('PDF') }}</span>
                                <span class="badge bg-light text-dark">{{ __('DOC/DOCX') }}</span>
                                <span class="badge bg-light text-dark">{{ __('XLS/XLSX') }}</span>
                                <span class="badge bg-light text-dark">{{ __('MP3') }}</span>
                            </div>

                            <div class="js-uploading-overlay d-none position-absolute top-0 start-0 w-100 h-100 rounded-3"
                                 style="background: rgba(255,255,255,.75);">
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center">
                                    <div class="spinner-border" role="status"></div>
                                    <div class="mt-2 fw-semibold">{{ __('Uploading...') }}</div>
                                    <div class="text-muted small js-uploading-text"></div>
                                </div>
                            </div>
                        </div>

                        <input id="contentFilesInput"
                               class="d-none"
                               type="file"
                               name="files[]"
                               multiple>

                        <div class="js-upload-errors mt-3"></div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-semibold">{{ __('Uploaded files') }}</div>
                                <div class="text-muted small">{{ __('Drag items to reorder') }}</div>
                            </div>

                            <div class="js-files-list"
                                 data-upload-url="{{ route('admin.menus.content.files.upload', $menu) }}"
                                 data-reorder-url="{{ route('admin.menus.content.files.reorder', $menu) }}">
                                @if(isset($files) && $files->isNotEmpty())
                                    @foreach($files as $f)
                                        @php($ext = strtolower((string) $f->extension))
                                        @php($url = asset('storage/' . ltrim($f->path, '/')))
                                        @php($isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'], true))

                                        <div class="d-flex align-items-center justify-content-between border rounded-3 p-2 mb-2 js-file-item"
                                             style="background:#fff;"
                                             draggable="true"
                                             data-id="{{ $f->id }}"
                                             data-delete-url="{{ route('admin.menus.content.files.delete', ['menu' => $menu->id, 'file' => $f->id]) }}">

                                            <div class="d-flex align-items-center gap-2">
                                                @if($isImg)
                                                    <a href="{{ $url }}" target="_blank" class="d-inline-block">
                                                        <img src="{{ $url }}"
                                                             alt=""
                                                             class="rounded-2 border"
                                                             style="width:44px;height:44px;object-fit:cover;">
                                                    </a>
                                                @else
                                                    @php($fa = '')

                                                    @if($ext === 'pdf')
                                                        @php($fa = 'fa-regular fa-file-pdf')
                                                    @elseif(in_array($ext, ['doc','docx'], true))
                                                        @php($fa = 'fa-regular fa-file-word')
                                                    @elseif(in_array($ext, ['xls','xlsx','csv'], true))
                                                        @php($fa = 'fa-regular fa-file-excel')
                                                    @elseif(in_array($ext, ['ppt','pptx'], true))
                                                        @php($fa = 'fa-regular fa-file-powerpoint')
                                                    @elseif(in_array($ext, ['zip','rar','7z'], true))
                                                        @php($fa = 'fa-regular fa-file-zipper')
                                                    @elseif(in_array($ext, ['mp3','wav','ogg','aac','m4a','flac'], true))
                                                        @php($fa = 'fa-regular fa-file-audio')
                                                    @elseif(in_array($ext, ['mp4','mov','avi','mkv'], true))
                                                        @php($fa = 'fa-regular fa-file-video')
                                                    @endif

                                                    @if($fa !== '')
                                                        <i class="{{ $fa }} fs-3"></i>
                                                    @endif
                                                @endif


                                                <div class="d-flex flex-column">
                                                    <a href="{{ $url }}" target="_blank" class="fw-semibold text-decoration-none">
                                                        {{ $f->original_name }}
                                                    </a>
                                                    <div class="text-muted small">
                                                        {{ strtoupper($ext ?: 'FILE') }}
                                                        @if($f->size)
                                                            · {{ number_format($f->size / 1024, 0) }} KB
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-drag-move-2-line text-muted"></i>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-file-delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </div>

                                    @endforeach
                                @else
                                    <div class="text-muted small js-empty-hint">
                                        {{ __('No files uploaded yet.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('Details') }}</h5>
                            </div>
                            <div class="card-body">

                                <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                                    @foreach($locales as $locale)
                                        <li class="nav-item" role="presentation">
                                            <a href="#tab-{{ $locale }}" class="nav-link {{ $active === $locale ? 'active' : '' }}" data-bs-toggle="tab" role="tab">
                                                <span class="fw-semibold text-uppercase">
                                                    {{ $locale }}
                                                    @if(in_array($locale, $requiredLocales ?? [], true))
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content">
                                    @foreach($locales as $locale)
                                        @php($row = $page->data[$locale] ?? ['title' => '', 'description' => ''])
                                        <div class="tab-pane fade {{ $active === $locale ? 'show active' : '' }}" id="tab-{{ $locale }}" role="tabpanel">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    {{ __('Title') }}
                                                    @if(in_array($locale, $requiredLocales ?? [], true))
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label> <input type="text"
                                                                class="form-control"
                                                                name="title[{{ $locale }}]"
                                                                value="{{ old('title.'.$locale, $row['title']) }}"
                                                                @if(in_array($locale, $requiredLocales ?? [], true)) required @endif>
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label">
                                                    {{ __('Description') }}
                                                    @if(in_array($locale, $requiredLocales ?? [], true))
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label> <textarea rows="14"
                                                                   class="form-control js-editor"
                                                                   name="description[{{ $locale }}]"
                                                                   @if(in_array($locale, $requiredLocales ?? [], true)) required @endif>{{ old('description.'.$locale, $row['description']) }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('Main Photo') }}</h5>
                            </div>
                            <div class="card-body">
                                @if($page->main_photo)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/'.$page->main_photo) }}" class="img-fluid rounded-3 border">
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <input class="form-control" type="file" name="main_photo" accept=".jpg,.jpeg,.png">
                                </div>

                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/assets/js/pages/menu-content-edit.js') }}"></script>
@endpush
