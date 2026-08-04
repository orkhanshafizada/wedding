@extends('admin.layouts.app')

@section('title', $mode === 'create' ? __('Create template') : __('Edit template'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-0">
                        {{ $mode === 'create' ? __('Create template') : __('Edit template') }}
                    </h4>
                    <div class="text-muted">{{ __('Notification template content (Email/SMS/Push).') }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notification.templates.index') }}" class="btn btn-soft-secondary">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('Back') }}
                    </a>
                    <button type="submit" form="templateForm" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> {{ __('Save') }}
                    </button>
                </div>
            </div>

            <form id="templateForm"
                  method="POST"
                  action="{{ $mode === 'create'
                        ? route('admin.notification.templates.store')
                        : route('admin.notification.templates.update', $template) }}">
                @csrf
                @if($mode !== 'create')
                    @method('PUT')
                @endif

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">{{ __('Template info') }}</h6>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                @checked(old('is_active', $template?->is_active ?? true))>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label" for="key">{{ __('Template key') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-key-2-line"></i></span>
                                    <input id="key"
                                           type="text"
                                           name="key"
                                           class="form-control"
                                           value="{{ old('key', $template?->key) }}"
                                           placeholder="forgot_password"
                                           autocomplete="off">
                                </div>
                                <div class="form-text">
                                    {{ __('Only lowercase letters, numbers and underscore.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Translations') }}</h6>
                    </div>

                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            @foreach($languages as $i => $lang)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if($i === 0) active @endif"
                                            data-bs-toggle="tab"
                                            data-bs-target="#lang-{{ $lang->id }}"
                                            type="button"
                                            role="tab">
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <i class="ri-translate-2"></i>
                                            <span>{{ $lang->name }}</span>
                                            <span class="badge bg-light text-muted border">{{ strtoupper($lang->code) }}</span>
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content pt-3">
                            @foreach($languages as $i => $lang)
                                @php
                                    $tr = $template?->translations?->firstWhere('language_id', $lang->id);
                                @endphp

                                <div class="tab-pane fade @if($i === 0) show active @endif"
                                     id="lang-{{ $lang->id }}"
                                     role="tabpanel">
                                    <input type="hidden"
                                           name="translations[{{ $lang->id }}][language_id]"
                                           value="{{ $lang->id }}">

                                    <div class="row g-3">
                                        <div class="col-xl-4">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="translations[{{ $lang->id }}][name]"
                                                   value="{{ old('translations.'.$lang->id.'.name', $tr?->name) }}"
                                                   placeholder="{{ __('e.g. Forgot password') }}">
                                        </div>

                                        <div class="col-xl-8">
                                            <label class="form-label">{{ __('Email subject') }}</label>
                                            <input type="text"
                                                   class="form-control"
                                                   name="translations[{{ $lang->id }}][email_subject]"
                                                   value="{{ old('translations.'.$lang->id.'.email_subject', $tr?->email_subject) }}"
                                                   placeholder="{{ __('e.g. Reset your password') }}">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                                <label class="form-label mb-0">{{ __('Email body (HTML)') }}</label>

                                                <div class="d-flex flex-wrap gap-1">
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{customer_name}">{customer_name}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{customer_surname}">{customer_surname}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{customer_email}">{customer_email}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{code}">{code}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{logo_light}">{logo_light}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{logo_dark}">{logo_dark}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{order_number}">{order_number}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{order_status}">{order_status}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="email_body_{{ $lang->id }}" data-placeholder="{link}">{link}</button>
                                                </div>
                                            </div>

                                            <textarea id="email_body_{{ $lang->id }}"
                                                      class="form-control mt-2 js-editor js-placeholder-target"
                                                      rows="9"
                                                      name="translations[{{ $lang->id }}][email_body]"
                                                      placeholder="<p>{{ __('Write HTML email content here...') }}</p>">{{ old('translations.'.$lang->id.'.email_body', $tr?->email_body) }}</textarea>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                                <label class="form-label mb-0">{{ __('Simple body (SMS/Push)') }}</label>

                                                <div class="d-flex flex-wrap gap-1">
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{customer_name}">{customer_name}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{customer_surname}">{customer_surname}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{customer_email}">{customer_email}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{code}">{code}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{logo_light}">{logo_light}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{logo_dark}">{logo_dark}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{order_number}">{order_number}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{order_status}">{order_status}</button>
                                                    <button type="button" class="btn btn-sm btn-soft-info js-placeholder-btn" data-target="simple_body_{{ $lang->id }}" data-placeholder="{link}">{link}</button>
                                                </div>
                                            </div>

                                            <textarea id="simple_body_{{ $lang->id }}"
                                                      class="form-control mt-2 js-placeholder-target"
                                                      rows="4"
                                                      name="translations[{{ $lang->id }}][simple_body]">{{ old('translations.'.$lang->id.'.simple_body', $tr?->simple_body) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
                    <a href="{{ route('admin.notification.templates.index') }}" class="btn btn-light">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>

            @push('scripts')
                <script>
                    (function () {
                        const placeholderButtonsSelector = '.js-placeholder-btn';
                        const editorRegistry = new Map();

                        const insertPlainTextarea = (textarea, text) => {
                            const start = textarea.selectionStart ?? textarea.value.length;
                            const end = textarea.selectionEnd ?? textarea.value.length;

                            textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);

                            const pos = start + text.length;
                            textarea.focus();
                            textarea.setSelectionRange(pos, pos);
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                            textarea.dispatchEvent(new Event('change', { bubbles: true }));
                        };

                        const insertCkeditor5 = (editor, text) => {
                            editor.model.change((writer) => {
                                const selection = editor.model.document.selection;
                                const position = selection.getFirstPosition();
                                writer.insertText(text, position);
                            });
                            editor.editing.view.focus();
                        };

                        const insertCkeditor4 = (editor, text) => {
                            editor.focus();
                            editor.insertText(text);
                            editor.updateElement();
                        };

                        const resolveEditorForTextarea = (textarea) => {
                            const id = textarea.getAttribute('id');
                            if (!id) {
                                return null;
                            }

                            if (editorRegistry.has(id)) {
                                return editorRegistry.get(id);
                            }

                            if (window.CKEDITOR && window.CKEDITOR.instances && window.CKEDITOR.instances[id]) {
                                const wrapped = { type: 'ckeditor4', instance: window.CKEDITOR.instances[id] };
                                editorRegistry.set(id, wrapped);
                                return wrapped;
                            }

                            if (window.__ckeditors && window.__ckeditors[id]) {
                                const wrapped = { type: 'ckeditor5', instance: window.__ckeditors[id] };
                                editorRegistry.set(id, wrapped);
                                return wrapped;
                            }

                            if (window.editors && window.editors[id]) {
                                const wrapped = { type: 'ckeditor5', instance: window.editors[id] };
                                editorRegistry.set(id, wrapped);
                                return wrapped;
                            }

                            return null;
                        };

                        const insertIntoTarget = (targetId, placeholder) => {
                            const textarea = document.getElementById(targetId);
                            if (!textarea) {
                                return;
                            }

                            const editor = resolveEditorForTextarea(textarea);
                            if (!editor) {
                                insertPlainTextarea(textarea, placeholder);
                                return;
                            }

                            if (editor.type === 'ckeditor5') {
                                insertCkeditor5(editor.instance, placeholder);
                                textarea.dispatchEvent(new Event('change', { bubbles: true }));
                                return;
                            }

                            if (editor.type === 'ckeditor4') {
                                insertCkeditor4(editor.instance, placeholder);
                                textarea.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        };

                        document.addEventListener('click', (e) => {
                            const btn = e.target.closest(placeholderButtonsSelector);
                            if (!btn) {
                                return;
                            }

                            const targetId = btn.getAttribute('data-target');
                            const placeholder = btn.getAttribute('data-placeholder');

                            if (!targetId || !placeholder) {
                                return;
                            }

                            insertIntoTarget(targetId, placeholder);
                        });

                        document.addEventListener('DOMContentLoaded', () => {
                            const editors = document.querySelectorAll('textarea.js-editor[id]');
                            if (!editors.length) {
                                return;
                            }

                            window.__ckeditors = window.__ckeditors || {};

                            editors.forEach((el) => {
                                const id = el.getAttribute('id');
                                if (!id) {
                                    return;
                                }

                                if (window.ClassicEditor && typeof window.ClassicEditor.create === 'function') {
                                    window.ClassicEditor.create(el).then((editor) => {
                                        window.__ckeditors[id] = editor;
                                        editorRegistry.set(id, { type: 'ckeditor5', instance: editor });
                                    }).catch(() => {});
                                }
                            });
                        });
                    })();
                </script>
            @endpush
        </div>
    </div>
@endsection
