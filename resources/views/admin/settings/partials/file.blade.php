@php
    $k     = 'file';
    $sizes = $data['sizes'];
    $wm    = $data['watermark'];
@endphp

<div class="tab-pane fade @if($activeTab === 'file') show active @endif" id="tab-file" role="tabpanel" aria-labelledby="tab-file-tab">
    <div class="row g-3">

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Max file size') }}</label>
                            <input type="text" class="form-control" name="{{ $k }}[max_file_size]" value="{{ $data['max_file_size'] }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Max image size') }}</label>
                            <input type="text" class="form-control" name="{{ $k }}[max_image_size]" value="{{ $data['max_image_size'] }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Max Video size') }}</label>
                            <input type="text" class="form-control" name="{{ $k }}[max_video_size]" value="{{ $data['max_video_size'] }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Image quality') }}</label>
                            <input type="number" min="1" max="100" class="form-control" name="{{ $k }}[image_quality]" value="{{ $data['image_quality'] }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Storage driver') }}</label>
                            <select class="form-select" name="{{ $k }}[storage_driver]">
                                @foreach(['local','s3'] as $d)
                                    <option value="{{ $d }}" @selected(($data['storage_driver'] ?? 'local') === $d)>{{ strtoupper($d) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Allowed images') }}</label>
                            <input type="text" class="form-control mb-2" name="{{ $k }}[allowed_images]" value="{{ $data['allowed_images'] }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Allowed videos') }}</label>
                            <input type="text" class="form-control" name="{{ $k }}[allowed_videos]" value="{{ $data['allowed_videos'] }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Allowed files') }}</label>
                            <input type="text" class="form-control" name="{{ $k }}[allowed_files]" value="{{ $data['allowed_files'] }}">
                        </div>
                    </div>
                    <div class="form-text">{{ __('Separate with commas') }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Image sizes') }}</div>
                    <div class="row g-3">

                        <div class="col-12">{{ __('Thumb') }}</div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Width') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][thumb][w]" value="{{ $sizes['thumb']['w'] ?? 150 }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Height') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][thumb][h]" value="{{ $sizes['thumb']['h'] ?? 150 }}">
                        </div>

                        <div class="col-12 mt-2">{{ __('Medium') }}</div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Width') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][medium][w]" value="{{ $sizes['medium']['w'] ?? 300 }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Height') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][medium][h]" value="{{ $sizes['medium']['h'] ?? 300 }}">
                        </div>

                        <div class="col-12 mt-2">{{ __('Large') }}</div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Width') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][large][w]" value="{{ $sizes['large']['w'] ?? 800 }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ __('Height') }}</label>
                            <input type="number" class="form-control" name="{{ $k }}[sizes][large][h]" value="{{ $sizes['large']['h'] ?? 800 }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ __('Watermark') }}</div>
                    <label class="form-label">{{ __('Position') }}</label>
                    <select class="form-select mb-2" name="{{ $k }}[watermark][position]">
                        @foreach(['top-left','top-right','bottom-left','bottom-right','center'] as $p)
                            <option value="{{ $p }}" @selected(($wm['position'] ?? 'bottom-right') === $p)>{{ $p }}</option>
                        @endforeach
                    </select>

                    <label class="form-label">{{ __('Opacity') }}</label>
                    <input type="number" min="0" max="100" class="form-control mb-2"
                           name="{{ $k }}[watermark][opacity]"
                           value="{{ $wm['opacity'] ?? 50 }}">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="{{ $k }}[watermark][enabled]" value="1" id="wm_enabled" @checked(($wm['enabled'] ?? false) === true)>
                        <label class="form-check-label" for="wm_enabled">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
