@extends('admin.layouts.app')
@section('title', __('Settings'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Settings') }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('Settings') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @includeWhen(View::exists('admin.shared.alerts'), 'admin.shared.alerts')

            <form method="POST"
                  action="{{ route('admin.settings.update') }}"
                  enctype="multipart/form-data"
                  id="settingsForm"
                  class="needs-validation"
                  novalidate>
                @csrf

                <input type="hidden" name="_active_tab" id="activeTabInput" value="{{ $activeTab }}"/>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">{{ __('Settings') }}</h6>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ri-save-3-line me-1 align-middle"></i>
                            {{ __('Save') }}
                        </button>
                    </div>

                    <div class="card-body">
                        <ul class="nav nav-pills nav-customs nav-danger mb-3" id="settingsTabs" role="tablist">
                            @foreach([
                              'general'=>__('General'),
                              'social'=>__('Social'),
                              'smtp'=>__('Smtp'),
                              'security'=>__('Security'),
                              'seo'=>__('Seo'),
                              'og'=>__('OG / Share'),
                              'oauth'=>__('Oauth'),
                              'system'=>__('System'),
                              'file'=>__('File'),
                            ] as $key => $label)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if($activeTab === $key) active @endif"
                                            data-bs-toggle="tab"
                                            data-bs-target="#tab-{{ $key }}"
                                            type="button"
                                            role="tab"
                                            aria-controls="tab-{{ $key }}"
                                            aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                                        {{ $label }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @include('admin.settings.partials.general', [
                                'data'      => $data['general'],
                                'languages' => $data['languages'],
                                'activeTab' => $activeTab
                            ])

                            @includeWhen(View::exists('admin.settings.partials.social'),   'admin.settings.partials.social',   ['data'=>$data['social'],   'activeTab'=>$activeTab])
                            @includeWhen(View::exists('admin.settings.partials.smtp'),     'admin.settings.partials.smtp',     ['data'=>$data['smtp'],     'activeTab'=>$activeTab])
                            @includeWhen(View::exists('admin.settings.partials.security'), 'admin.settings.partials.security', ['data'=>$data['security'], 'activeTab'=>$activeTab])
                            @includeWhen(View::exists('admin.settings.partials.seo'),      'admin.settings.partials.seo',      ['data'=>$data['seo'],      'activeTab'=>$activeTab])

                            @includeWhen(View::exists('admin.settings.partials.og'),       'admin.settings.partials.og',       [
                                'data'=>$data['og'],
                                'languages'=>$data['languages'],
                                'activeTab'=>$activeTab
                            ])

                            @includeWhen(View::exists('admin.settings.partials.oauth'),    'admin.settings.partials.oauth',    ['data'=>$data['oauth'],    'activeTab'=>$activeTab])
                            @includeWhen(View::exists('admin.settings.partials.system'),   'admin.settings.partials.system',   ['data'=>$data['system'],   'languages'=>$data['languages'], 'activeTab'=>$activeTab])
                            @includeWhen(View::exists('admin.settings.partials.file'),     'admin.settings.partials.file',     ['data'=>$data['file'],     'activeTab'=>$activeTab])
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const tabs = document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]');
            const activeInput = document.getElementById('activeTabInput');

            tabs.forEach((btn) => {
                btn.addEventListener('shown.bs.tab', function (e) {
                    const id = e.target.getAttribute('data-bs-target') || '';
                    const key = id.replace('#tab-', '');
                    if (activeInput) activeInput.value = key;

                    if (history.replaceState && key) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', key);
                        history.replaceState(null, '', url.toString());
                    }
                });
            });

            const MAX_SIZE = 25 * 1024 * 1024;
            document.querySelectorAll('input[type=file]').forEach((inp) => {
                inp.addEventListener('change', function () {
                    if (this.files && this.files[0] && this.files[0].size > MAX_SIZE) {
                        alert(@json(__('File too large')));
                        this.value = '';
                    }
                });
            });
        })();
    </script>
@endpush
