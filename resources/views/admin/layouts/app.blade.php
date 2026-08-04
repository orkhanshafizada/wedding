<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
      data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8"/>
    <title>ADMIN | @yield('title', __('Title'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description"/>
    <meta content="Created by Orkhan Shafizada" name="author"/>
    <meta name="translations-update-url" content="{{ route('admin.translations.update-value', ['translation' => '___ID___']) }}">
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/favicon.ico') }}">
    <link href="{{ asset('admin/assets/libs/jsvectormap/jsvectormap.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/libs/swiper/swiper-bundle.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('admin/assets/js/layout.js') }}?v={{ config('app.asset_version', '1') }}"></script>
    <link href="{{ asset('admin/assets/css/bootstrap.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/css/icons.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/css/app.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/css/custom.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/css/custom_o.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('admin/assets/libs/sweetalert2/sweetalert2.min.css') }}?v={{ config('app.asset_version', '1') }}">
    <link href="{{ asset('admin/assets/libs/select2/select2.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('admin/assets/libs/choices.js/public/assets/styles/choices.min.css') }}?v={{ config('app.asset_version', '1') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/libs/flatpickr/flatpickr.min.css') }}?v={{ config('app.asset_version', '1') }}">

    <link href="{{ asset('admin/assets/plugins/fontawesome-6-pro/css/all.min.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css"/>


    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>
<body>

<!-- Begin page -->
<div id="layout-wrapper">
    @include('admin.partials.navbar')
    <div class="vertical-overlay"></div>
    <div class="main-content">
        @yield('content')
        @include('admin.partials.footer')
    </div>
</div>
<!--start back-to-top-->
<button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="ri-arrow-up-line"></i>
</button>
<!--end back-to-top-->
<!--preloader-->
<div id="preloader">
    <div id="status">
        <div class="spinner-border text-primary avatar-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<div class="customizer-setting d-none d-md-block">
    <div class="btn-info rounded-pill shadow-lg btn btn-icon btn-lg p-2" data-bs-toggle="offcanvas"
         data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
        <i class='mdi mdi-spin mdi-cog-outline fs-22'></i>
    </div>
</div>
@include('admin.partials.settings')
<div class="sidebar-backdrop"></div>
<script src="{{ asset('admin/assets/libs/jquery/jquery-3.7.1.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/simplebar/simplebar.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/node-waves/waves.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/feather-icons/feather.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/pages/plugins/lord-icon-2.1.0.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/plugins.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/apexcharts/apexcharts.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/jsvectormap/jsvectormap.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/jsvectormap/maps/world-merc.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/swiper/swiper-bundle.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/pages/dashboard-ecommerce.init.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/app.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/slugMaker.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/sweetalert2/sweetalert2.all.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/custom.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/js/pages/loading.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/select2/select2.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/sortablejs/Sortable.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('admin/assets/libs/flatpickr/flatpickr.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>

<script>
    window.CKEDITOR_BASEPATH = "{{ asset('admin/assets/plugins/ckeditor') }}/";
</script>
<script src="{{ asset('admin/assets/plugins/ckeditor/ckeditor.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script type="text/javascript">var access_key = "<?=time();?>";</script>

<script>
    (function () {
        'use strict';

        function initEditors() {
            if (typeof window.CKEDITOR === 'undefined') return;

            const uploadUrl = @json(route('admin.ckeditor.upload'));
            const csrfToken = @json(csrf_token());

            document.querySelectorAll('textarea.js-editor').forEach(function (el, idx) {
                if (!el.id) {
                    window.__ckeditorUid = window.__ckeditorUid || 0;
                    window.__ckeditorUid += 1;
                    el.id = 'ckeditor_' + Date.now() + '_' + window.__ckeditorUid;
                }
                if (CKEDITOR.instances[el.id]) return;

                CKEDITOR.replace(el.id, {
                    height: 420,
                    allowedContent: true,

                    // CKEditor 4-də paste image + upload üçün
                    clipboard_handleImages: true,

                    // Səndə token olmadığı üçün exportpdf-i söndürürük
                    removePlugins: 'exportpdf,cloudservices,easyimage',

                    extraPlugins: 'uploadimage',

                    // Upload endpoint
                    filebrowserUploadUrl: uploadUrl + '?_token=' + encodeURIComponent(csrfToken),
                    filebrowserUploadMethod: 'form',

                    toolbar: [
                        {name: 'document', items: ['Source', '-', 'Preview', 'Print']},
                        {
                            name: 'clipboard',
                            items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                        },
                        {name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll']},
                        {name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe']},
                        {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                        {name: 'tools', items: ['Maximize']},
                        '/',
                        {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']},
                        {
                            name: 'paragraph',
                            items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
                        },
                        {name: 'align', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                        {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
                        {name: 'colors', items: ['TextColor', 'BGColor']}
                    ],
                    stylesSet: [
                        {name: 'Line spacing: 1.0', element: 'p', styles: {'line-height': '1'}},
                        {name: 'Line spacing: 1.15', element: 'p', styles: {'line-height': '1.15'}},
                        {name: 'Line spacing: 1.5', element: 'p', styles: {'line-height': '1.5'}},
                        {name: 'Line spacing: 2.0', element: 'p', styles: {'line-height': '2'}},

                        {name: 'Paragraph: Small', element: 'p', styles: {'margin': '0 0 8px 0'}},
                        {name: 'Paragraph: Normal', element: 'p', styles: {'margin': '0 0 16px 0'}},
                        {name: 'Paragraph: Large', element: 'p', styles: {'margin': '0 0 24px 0'}},
                    ],

                });
            });
        }

        document.addEventListener('DOMContentLoaded', initEditors);
        document.addEventListener('shown.bs.tab', initEditors);
        window.__adminInitCkEditors = initEditors;
    })();
</script>

@include('admin.shared.alerts')
@stack('scripts')
</body>
</html>
