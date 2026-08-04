@php $k = 'smtp'; @endphp

<div class="tab-pane fade @if($activeTab === 'smtp') show active @endif" id="tab-smtp" role="tabpanel" aria-labelledby="tab-smtp-tab">
    <div class="card">
        <div class="card-body">
            <div class="row g-3">

                {{-- Driver --}}
                <div class="col-md-3">
                    <label class="form-label">{{ __('Email driver') }}</label>
                    <select class="form-select" name="{{ $k }}[driver]">
                        @foreach(['smtp','sendmail','log'] as $d)
                            <option value="{{ $d }}" @selected(($data['driver'] ?? 'smtp') === $d)>{{ strtoupper($d) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Host --}}
                <div class="col-md-3">
                    <label class="form-label">{{ __('Hostname') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[host]" value="{{ $data['host'] ?? '' }}">
                </div>

                {{-- Port --}}
                <div class="col-md-2">
                    <label class="form-label">{{ __('Port') }}</label>
                    <input type="number" class="form-control" name="{{ $k }}[port]" value="{{ $data['port'] ?? 465 }}">
                </div>

                {{-- Encryption --}}
                <div class="col-md-2">
                    <label class="form-label">{{ __('Encryption') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[encryption]" value="{{ $data['encryption'] ?? 'ssl' }}">
                </div>

                {{-- Username --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('Username') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[username]" value="{{ $data['username'] ?? '' }}">
                </div>

                {{-- Password --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input type="password" class="form-control" name="{{ $k }}[password]" value="{{ $data['password'] ?? '' }}">
                </div>

                {{-- From Email --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('From email') }}</label>
                    <input type="email" class="form-control" name="{{ $k }}[from_email]" value="{{ $data['from_email'] ?? '' }}">
                </div>

                {{-- From Name --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('From name') }}</label>
                    <input type="text" class="form-control" name="{{ $k }}[from_name]" value="{{ $data['from_name'] ?? 'Laravel' }}">
                </div>

            </div>
        </div>
    </div>
</div>
