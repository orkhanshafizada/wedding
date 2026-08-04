@php $k = 'social'; @endphp

<div class="tab-pane fade @if($activeTab === 'social') show active @endif" id="tab-social" role="tabpanel" aria-labelledby="tab-social-tab">
    <div class="row g-4">
        @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'Twitter', 'linkedin' => 'LinkedIn'] as $key => $label)
            @php $v = $data[$key] ?? []; @endphp
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">{{ __($label) }}</div>

                        <label class="form-label">{{ __('Link') }}</label>
                        <input type="text" class="form-control mb-2" name="{{ $k }}[{{ $key }}][link]" value="{{ $v['link'] ?? '' }}">

                        <label class="form-label">{{ __('Icon') }}</label>
                        <input type="text" class="form-control mb-2" name="{{ $k }}[{{ $key }}][icon]" value="{{ $v['icon'] ?? '' }}">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="{{ $k }}[{{ $key }}][active]" value="1" id="s-{{ $key }}" @checked($v['active'] ?? false)>
                            <label class="form-check-label" for="s-{{ $key }}">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
