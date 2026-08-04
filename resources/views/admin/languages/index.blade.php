@extends('admin.layouts.app')

@section('title', __('Languages'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('Languages') }}</h5>
                    <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('Add language') }}
                    </a>
                </div>

                <div class="card-body">
                    <table id="languages-table" class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Sort') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Native name') }}</th>
                            <th>{{ __('Locale code') }}</th>
                            <th>{{ __('Text direction') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Default admin') }}</th>
                            <th>{{ __('Default site') }}</th>
                            <th>{{ __('Required') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($locales as $lang)
                            <tr data-id="{{ $lang->id }}"
                                data-toggle-required-url="{{ route('admin.languages.toggle-required', $lang) }}">
                                <td>{{ $lang->id }}</td>
                                <td>{{ $lang->sort_order }}</td>
                                <td>{{ $lang->name }}</td>
                                <td>{{ $lang->native_name }}</td>
                                <td><span class="badge bg-light text-dark">{{ $lang->code }}</span></td>
                                <td>
                                    <span class="badge {{ $lang->is_rtl ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                        {{ $lang->is_rtl ? __('Rtl') : 'LTR' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input js-status-switch" type="checkbox"
                                            {{ $lang->status === 'Active' ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $lang->status }}</label>
                                    </div>
                                </td>

                                <td>
                                    @if ($lang->is_default_admin)
                                        <span class="badge bg-success">{{ __('Default') }}</span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.languages.set-default-admin', $lang) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                {{ __('Set default') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <td>
                                    @if ($lang->is_default_site)
                                        <span class="badge bg-success">{{ __('Default') }}</span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.languages.set-default-site', $lang) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                {{ __('Set default') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input js-required-switch"
                                               type="checkbox"
                                            {{ $lang->is_required ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ $lang->is_required ? __('Yes') : __('No') }}
                                        </label>
                                    </div>
                                </td>

                                <td class="text-end">
                                    @include('admin.common.actionButtons', [
                                         'action_update' => route('admin.languages.edit', $lang),
                                         'action_delete' => route('admin.languages.destroy', $lang),
                                         'data' => $lang,
                                     ])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <p class="text-muted small mt-3">
                        {{ __('Note default language protection') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/assets/js/pages/languages-index.js') }}"></script>
@endpush
