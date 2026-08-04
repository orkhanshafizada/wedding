@php
    $k = 'og';

    $imagePath = $data['image'] ?? null;
    $imageUrl = $imagePath
        ? (\Illuminate\Support\Str::startsWith($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath))
        : '';

    $imageFileName = $imagePath ? basename($imagePath) : '';
@endphp

<div class="tab-pane fade @if($activeTab === 'og') show active @endif" id="tab-og" role="tabpanel" aria-labelledby="tab-og-tab">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('OG / Share') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                        @foreach($languages as $i => $lang)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($i === 0) active @endif"
                                        id="og-tab-{{ $lang->id }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#og-pane-{{ $lang->id }}"
                                        type="button" role="tab" aria-controls="og-pane-{{ $lang->id }}"
                                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                    {{ strtoupper($lang->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $i => $lang)
                            @php $lid = (string) $lang->id; @endphp
                            <div class="tab-pane fade @if($i === 0) show active @endif"
                                 id="og-pane-{{ $lang->id }}" role="tabpanel" aria-labelledby="og-tab-{{ $lang->id }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('OG title') }} ({{ $lang->code }})</label>
                                        <input type="text" class="form-control"
                                               name="{{ $k }}[title][{{ $lid }}]"
                                               value="{{ $data['title'][$lid] ?? '' }}"
                                               placeholder="Example: Ayti E-commerce - Digital Solutions">
                                        <div class="small text-muted mt-1">
                                            {{ __('The title shown when the link is shared. If left empty, the page title may be used instead.') }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('OG type') }}</label>
                                        <select class="form-select" name="{{ $k }}[type]">
                                            @foreach(['website','article','product'] as $t)
                                                <option value="{{ $t }}" @selected(($data['type'] ?? 'website') === $t)>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <div class="small text-muted mt-1">
                                            {{ __('Content type: website (general), article (blog/news), product (product). Default: website.') }}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">{{ __('OG description') }} ({{ $lang->code }})</label>
                                        <textarea class="form-control" rows="3"
                                                  name="{{ $k }}[description][{{ $lid }}]"
                                                  placeholder="Example: website, mobile app, SEO, etc...">{{ $data['description'][$lid] ?? '' }}</textarea>
                                        <div class="small text-muted mt-1">
                                            {{ __('A short text shown below the title in the link preview. Keep it brief and specific (around 1–2 sentences).') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Twitter card') }}</label>
                            <select class="form-select" name="{{ $k }}[twitter_card]">
                                @foreach(['summary_large_image','summary'] as $c)
                                    <option value="{{ $c }}" @selected(($data['twitter_card'] ?? 'summary_large_image') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1">
                                {{ __('X (Twitter) preview format: summary_large_image (large image) or summary (small image). Default: summary_large_image.') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('Twitter @site') }}</label>
                            <input type="text" class="form-control"
                                   name="{{ $k }}[twitter_site]"
                                   value="{{ $data['twitter_site'] ?? '' }}"
                                   placeholder="@orkhanshafizada">
                            <div class="small text-muted mt-1">
                                {{ __('Your official X account (e.g. @brand). It may appear as “via @brand” in the preview. Leave empty if you do not want this.') }}
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('Canonical URL') }}</label>
                            <input type="text" class="form-control"
                                   name="{{ $k }}[canonical]"
                                   value="{{ $data['canonical'] ?? '' }}"
                                   placeholder="If left empty, the current URL will be used">
                            <div class="small text-muted mt-1">
                                {{ __('The main URL for SEO. If the same page opens with different query/utm parameters, the canonical should be the “clean” URL. If empty, the current URL is used.') }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('OG image') }}</h6>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3" data-og-image>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-semibold">{{ __('Share image') }}</div>
                            <button type="button" class="btn btn-ghost-danger btn-icon btn-sm og-image-remove" title="{{ __('Clear') }}">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        <div class="position-relative text-center mb-2">
                            <img class="img-fluid rounded border @if(!$imageUrl) d-none @endif og-image-preview" src="{{ $imageUrl }}" alt="">
                            <div class="small text-muted mt-2">
                                {{ __('Recommended') }}: 1200x630 (1.91:1)
                            </div>
                            <div class="small text-muted mt-1">
                                {{ __('The image shown when the link is shared (og:image). For best results, use 1200x630 dimensions.') }}
                            </div>
                        </div>

                        <input type="file" accept="image/*" class="form-control og-image-input" name="files[og][image]">
                        <input type="checkbox" class="d-none og-image-remove-flag" name="{{ $k }}[image_remove]" value="1">

                        <div class="small text-muted mt-2 og-image-filename">{{ $imageFileName }}</div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <div class="fw-semibold mb-1">{{ __('Note') }}</div>
                        <div class="small">
                            {{ __('This image will be used as the default og:image when the link is shared on social networks. If a separate OG image is set at the page level, that one will take priority.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            'use strict';

            document.addEventListener('click', function (e) {
                const rmBtn = e.target.closest('.og-image-remove');
                if (!rmBtn) return;

                const box = rmBtn.closest('[data-og-image]');
                if (!box) return;

                const preview = box.querySelector('.og-image-preview');
                const input = box.querySelector('.og-image-input');
                const flag = box.querySelector('.og-image-remove-flag');
                const fname = box.querySelector('.og-image-filename');

                if (preview) preview.classList.add('d-none');
                if (input) input.value = '';
                if (flag) flag.checked = true;
                if (fname) fname.textContent = '';
            }, false);

            document.addEventListener('change', function (e) {
                const input = e.target.closest('.og-image-input');
                if (!input || !input.files || !input.files[0]) return;

                const box = input.closest('[data-og-image]');
                if (!box) return;

                const preview = box.querySelector('.og-image-preview');
                const flag = box.querySelector('.og-image-remove-flag');
                const fname = box.querySelector('.og-image-filename');

                if (flag) flag.checked = false;
                if (fname) fname.textContent = input.files[0].name;

                const reader = new FileReader();
                reader.onload = function (ev) {
                    if (preview) {
                        preview.src = ev.target.result;
                        preview.classList.remove('d-none');
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }, false);
        })();
    </script>
@endpush
