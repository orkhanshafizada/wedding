@php $k = 'system'; @endphp

<div class="tab-pane fade @if($activeTab === 'system') show active @endif" id="tab-system" role="tabpanel" aria-labelledby="tab-system-tab">
    <div class="row g-4">

        {{-- Sistem əsas ayarları --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('System') }}</div>

                    <label class="form-label">{{ __('Env') }}</label>
                    <select class="form-select mb-2" name="{{ $k }}[env]">
                        @foreach(['development','staging','production'] as $e)
                            <option value="{{ $e }}" @selected(($data['env'] ?? 'development') === $e)>{{ ucfirst($e) }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">{{ __('Timezone') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[timezone]" value="{{ $data['timezone'] }}">

                    <label class="form-label">{{ __('Date format') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[date_format]" value="{{ $data['date_format'] }}">

                    <label class="form-label">{{ __('Default language') }}</label>
                    <select class="form-select" name="{{ $k }}[default_language_id]">
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}" @selected(($data['default_language_id'] ?? null) == $lang->id)>{{ $lang->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Cache --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Cache') }}</div>

                    <label class="form-label">{{ __('Cache ttl') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[cache][ttl]" value="{{ $data['cache']['ttl'] ?? '1 hour' }}">

                    <label class="form-label">{{ __('Driver') }}</label>
                    <select class="form-select mb-2" name="{{ $k }}[cache][driver]">
                        @foreach(['file','redis','array','memcached'] as $d)
                            <option value="{{ $d }}" @selected(($data['cache']['driver'] ?? 'file') === $d)>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">{{ __('Prefix') }}</label>
                    <input type="text" class="form-control mb-2" name="{{ $k }}[cache][prefix]" value="{{ $data['cache']['prefix'] ?? '' }}">

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[cache][enabled]" value="1" id="cache-en" @checked($data['cache']['enabled'] ?? false)>
                        <label class="form-check-label" for="cache-en">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Queue --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Queue') }}</div>

                    <label class="form-label">{{ __('Default driver') }}</label>
                    <select class="form-select mb-2" name="{{ $k }}[queue][driver]">
                        @foreach(['sync','database','redis'] as $d)
                            <option value="{{ $d }}" @selected(($data['queue']['driver'] ?? 'sync') === $d)>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">{{ __('Failed ttl') }}</label>
                    <input type="number" class="form-control" name="{{ $k }}[queue][failed_ttl]" value="{{ $data['queue']['failed_ttl'] ?? 30 }}">
                </div>
            </div>
        </div>

        {{-- Backup --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Backup') }}</div>

                    <label class="form-label">{{ __('Frequency') }}</label>
                    <select class="form-select mb-2" name="{{ $k }}[backup][frequency]">
                        @foreach(['hourly','daily','weekly','monthly'] as $f)
                            <option value="{{ $f }}" @selected(($data['backup']['frequency'] ?? 'daily') === $f)>{{ ucfirst($f) }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">{{ __('Retention days') }}</label>
                    <input type="number" class="form-control" name="{{ $k }}[backup][retention_days]" value="{{ $data['backup']['retention_days'] ?? 7 }}">
                </div>
            </div>
        </div>

    </div>
</div>
