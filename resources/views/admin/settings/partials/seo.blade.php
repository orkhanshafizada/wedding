@php $k = 'seo'; @endphp

<div class="tab-pane fade @if($activeTab === 'seo') show active @endif" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-tab">
    <div class="row g-4">

        {{-- Google --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Google') }}</div>

                    <label class="form-label">{{ __('Analytics id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[google][analytics_id]" value="{{ $data['google']['analytics_id'] ?? '' }}">

                    <label class="form-label">{{ __('GTM id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[google][gtm_id]" value="{{ $data['google']['gtm_id'] ?? '' }}">

                    <label class="form-label">{{ __('Search console') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[google][search_console]" value="{{ $data['google']['search_console'] ?? '' }}">

                    <label class="form-label">{{ __('Ads conversion id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[google][ads_conversion_id]" value="{{ $data['google']['ads_conversion_id'] ?? '' }}">

                    <label class="form-label">{{ __('Google map key') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[google][map_key]" value="{{ $data['google']['map_key'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- Yandex --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Yandex') }}</div>

                    <label class="form-label">{{ __('Metrika id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[yandex][metrika_id]" value="{{ $data['yandex']['metrika_id'] ?? '' }}">

                    <label class="form-label">{{ __('Verify key') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[yandex][verify_key]" value="{{ $data['yandex']['verify_key'] ?? '' }}">

                    <label class="form-label">{{ __('Webmaster code') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[yandex][webmaster_code]" value="{{ $data['yandex']['webmaster_code'] ?? '' }}">

                    <label class="form-label">{{ __('Ecommerce id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[yandex][ecommerce_id]" value="{{ $data['yandex']['ecommerce_id'] ?? '' }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[yandex][webvisor]" value="1" id="webv" @checked($data['yandex']['webvisor'] ?? false)>
                        <label class="form-check-label" for="webv">{{ __('Webvizor') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Facebook --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Facebook') }}</div>

                    <label class="form-label">{{ __('Pixel id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[facebook][pixel_id]" value="{{ $data['facebook']['pixel_id'] ?? '' }}">

                    <label class="form-label">{{ __('App id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[facebook][app_id]" value="{{ $data['facebook']['app_id'] ?? '' }}">

                    <label class="form-label">{{ __('Verify key') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[facebook][verify_key]" value="{{ $data['facebook']['verify_key'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- Sitemap --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Sitemap params') }}</div>

                    <label class="form-label">{{ __('Update freq') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[sitemap][freq]" value="{{ $data['sitemap']['freq'] ?? 'weekly' }}">

                    <label class="form-label">{{ __('Priority') }}</label>
                    <input type="number" step="0.1" min="0" max="1" class="form-control mb-2" name="{{ $k }}[sitemap][priority]" value="{{ $data['sitemap']['priority'] ?? '0.8' }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[sitemap][auto]" value="1" id="smauto" @checked($data['sitemap']['auto'] ?? false)>
                        <label class="form-check-label" for="smauto">{{ __('Auto generate') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Robots.txt --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Robots.txt') }}</div>
                    <textarea class="form-control" rows="6" name="{{ $k }}[robots]">{{ $data['robots'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

    </div>
</div>
