@php $k = 'oauth'; @endphp

<div class="tab-pane fade @if($activeTab === 'oauth') show active @endif" id="tab-oauth" role="tabpanel" aria-labelledby="tab-oauth-tab">
    <div class="row g-4">

        {{-- Telegram --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Telegram') }}</div>

                    <label class="form-label">{{ __('Token') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[telegram][token]" value="{{ $data['telegram']['token'] ?? '' }}">

                    <label class="form-label">{{ __('Webhook url') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[telegram][webhook_url]" value="{{ $data['telegram']['webhook_url'] ?? '' }}">

                    <label class="form-label">{{ __('Chat id') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[telegram][chat_id]" value="{{ $data['telegram']['chat_id'] ?? '' }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[telegram][active]" value="1" id="tgact" @checked($data['telegram']['active'] ?? false)>
                        <label class="form-check-label" for="tgact">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Google, Facebook, LinkedIn --}}
        @foreach(['google' => __('Google'), 'facebook' => __('Facebook'), 'linkedin' => __('LinkedIn')] as $idp => $lbl)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">{{ $lbl }}</div>

                        <label class="form-label">{{ __('Client id') }}</label>
                        <input type="text" class="form-control mb-2" name="{{ $k }}[{{ $idp }}][client_id]" value="{{ $data[$idp]['client_id'] ?? '' }}">

                        <label class="form-label">{{ __('Client secret') }}</label>
                        <input type="text" class="form-control mb-2" name="{{ $k }}[{{ $idp }}][client_secret]" value="{{ $data[$idp]['client_secret'] ?? '' }}">

                        <label class="form-label">{{ __('Redirect url') }}</label>
                        <input type="text" class="form-control mb-2" name="{{ $k }}[{{ $idp }}][redirect_url]" value="{{ $data[$idp]['redirect_url'] ?? '' }}">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="{{ $k }}[{{ $idp }}][active]" value="1" id="{{ $idp }}-act" @checked($data[$idp]['active'] ?? false)>
                            <label class="form-check-label" for="{{ $idp }}-act">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
