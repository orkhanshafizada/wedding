@php $k = 'security'; @endphp

<div class="tab-pane fade @if($activeTab === 'security') show active @endif" id="tab-security" role="tabpanel" aria-labelledby="tab-security-tab">
    <div class="row g-4">

        {{-- Login attempts --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Security') }}</div>

                    <label class="form-label">{{ __('Max login attempts') }}</label>
                    <input type="number" class="form-control mb-2" name="{{ $k }}[max_login_attempts]" value="{{ $data['max_login_attempts'] }}">

                    <label class="form-label">{{ __('Lock minutes') }}</label>
                    <input type="number" class="form-control" name="{{ $k }}[lock_minutes]" value="{{ $data['lock_minutes'] }}">
                </div>
            </div>
        </div>

        {{-- Password policy --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Password policy') }}</div>

                    <label class="form-label">{{ __('Min length') }}</label>
                    <input type="number" class="form-control mb-2" name="{{ $k }}[password_policy][min]" value="{{ $data['password_policy']['min'] ?? 8 }}">

                    <div class="d-flex gap-3">
                        @foreach(['upper' => 'Require upper', 'digit' => 'Require digit', 'symbol' => 'Require symbol'] as $pk => $lblKey)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $k }}[password_policy][{{ $pk }}]" value="1" id="pp-{{ $pk }}" @checked($data['password_policy'][$pk] ?? false)>
                                <label class="form-check-label" for="pp-{{ $pk }}">{{ __($lblKey) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Rate limit --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Rate limit') }}</div>

                    <label class="form-label">{{ __('Rate max') }}</label>
                    <input type="number" class="form-control mb-2" name="{{ $k }}[rate_limit][max]" value="{{ $data['rate_limit']['max'] ?? 60 }}">

                    <label class="form-label">{{ __('Rate window (minute)') }}</label>
                    <input type="number" class="form-control mb-2" name="{{ $k }}[rate_limit][window_min]" value="{{ $data['rate_limit']['window_min'] ?? 1 }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[rate_limit][enabled]" value="1" id="rl" @checked($data['rate_limit']['enabled'] ?? false)>
                        <label class="form-check-label" for="rl">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Captcha --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Captcha') }}</div>

                    <label class="form-label">{{ __('Site key') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[captcha][site_key]" value="{{ $data['captcha']['site_key'] ?? '' }}">

                    <label class="form-label">{{ __('Secret key') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[captcha][secret_key]" value="{{ $data['captcha']['secret_key'] ?? '' }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[captcha][enabled]" value="1" id="cap" @checked($data['captcha']['enabled'] ?? false)>
                        <label class="form-check-label" for="cap">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
