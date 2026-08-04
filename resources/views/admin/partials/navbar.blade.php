@php
    use App\Models\Language;
    use App\Support\Settings;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $navLangs = Language::active()
        ->orderBy('sort_order')
        ->get(['name', 'native_name', 'code', 'is_default_admin']);

    $currentLocale = $admin_locale ?? session('admin_locale', app()->getLocale());
    $langCount = $admin_lang_count ?? $navLangs->count();

    $generalImages = Settings::get('general', 'images', []);

    $resolveImage = static function ($path, $fallback = null) {
        if (blank($path)) {
            return $fallback;
        }

        if (
            Str::startsWith($path, ['http://', 'https://', '//', '/storage/', '/uploads/']) ||
            Str::startsWith($path, 'data:image')
        ) {
            return $path;
        }

        return Storage::url($path);
    };

    $siteTitle = Settings::get('general', 'site_title', config('app.name'));

    if (is_array($siteTitle)) {
        $siteTitle = $siteTitle[$currentLocale] ?? collect($siteTitle)->filter()->first() ?? config('app.name');
    }

    $defaultLogo = asset('admin/assets/images/logo_light.webp');
    $defaultLogoDark = asset('admin/assets/images/logo_dark.webp');
    $defaultMobileLogo = asset('admin/assets/images/logo_light.webp');

    $logoLight = $resolveImage(data_get($generalImages, 'logo'), $defaultLogo);
    $logoDark = $resolveImage(data_get($generalImages, 'logo_dark'), $defaultLogoDark);
    $mobileLogoLight = $resolveImage(data_get($generalImages, 'mobile_logo'), $defaultMobileLogo);
    $mobileLogoDark = $resolveImage(data_get($generalImages, 'mobile_logo_dark'), $mobileLogoLight);

    if (blank($logoDark)) {
        $logoDark = $logoLight;
    }

    if (blank($mobileLogoLight)) {
        $mobileLogoLight = $logoLight;
    }

    if (blank($mobileLogoDark)) {
        $mobileLogoDark = $mobileLogoLight;
    }
@endphp

<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ $mobileLogoLight }}" alt="{{ $siteTitle }}" height="28">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ $logoLight }}" alt="{{ $siteTitle }}" height="28">
                        </span>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ $mobileLogoDark }}" alt="{{ $siteTitle }}" height="28">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ $logoDark }}" alt="{{ $siteTitle }}" height="28">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Search">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="dropdown ms-1 topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="{{ asset('admin/assets/images/flags/' . $currentLocale . '.svg') }}" alt="Header Language" height="20" class="rounded">
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        @foreach($navLangs as $lng)
                            @php
                                $isActive = trim($currentLocale) === trim($lng->code);
                            @endphp

                            <form method="POST" action="{{ route('admin.locale.set') }}" class="px-3 py-1">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $lng->code }}">
                                <input type="hidden" name="return" value="{{ url()->current() }}">

                                <button type="submit" class="dropdown-item {{ $isActive ? 'active' : '' }}">
                                    <img src="{{ asset('admin/assets/images/flags/' . $lng->code . '.svg') }}" alt="{{ $lng->code }}" class="me-2 rounded" height="18">
                                    <span class="align-middle">
                                        {{ $lng->native_name ?: $lng->name }}
                                        @if($lng->is_default_admin)
                                            <small class="text-muted ms-1">({{ __('Default') }})</small>
                                        @endif
                                    </span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class="bx bx-moon fs-22"></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center gap-2">
                            <img class="rounded header-profile-user" src="{{ asset('admin/assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                            <span class="text-start">
                                <span class="d-block fw-medium sidebar-user-name-text">{{ auth()->user()->fullname ?? 'Admin' }}</span>
                                <span class="d-block fs-14 sidebar-user-name-sub-text">
                                    <i class="ri ri-circle-fill fs-10 text-success align-baseline"></i>
                                    <span class="align-middle">Online</span>
                                </span>
                            </span>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">{{ __('Welcome') }} {{ auth()->user()->fullname ?? 'Admin' }}!</h6>

                        <form method="POST" action="{{ route('admin.logout') }}" class="px-3 py-1">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-danger">
                                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
            </div>

            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>

                    <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                        <h4>Are you sure ?</h4>
                        <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-menu navbar-menu">
    <div class="navbar-brand-box p-3">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ $mobileLogoDark  }}" alt="{{ $siteTitle }}" height="35">
            </span>
            <span class="logo-lg">
                <img src="{{ $logoDark }}" alt="{{ $siteTitle }}" style="width:100px;">
            </span>
        </a>

        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ $mobileLogoLight }}" alt="{{ $siteTitle }}" height="35">
            </span>
            <span class="logo-lg">
                <img src="{{ $logoLight }}" alt="{{ $siteTitle }}" style="width:100px;">
            </span>
        </a>

        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ asset('admin/assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{ auth()->user()->fullname ?? 'Admin' }}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text">
                        <i class="ri ri-circle-fill fs-10 text-success align-baseline"></i>
                        <span class="align-middle">Online</span>
                    </span>
                </span>
            </span>
        </button>

        <div class="dropdown-menu dropdown-menu-end">
            <form method="POST" action="{{ route('admin.logout') }}" class="px-3 py-1">
                @csrf
                <button type="submit" class="btn btn-link p-0 text-danger">
                    <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>

    @include('admin.partials.sidebar')

    <div class="sidebar-background"></div>
</div>
