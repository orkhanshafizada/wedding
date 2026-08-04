@extends('admin.layouts.app')

@section('title', $country->exists ? 'Edit country' : 'Create country')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">
                    {{ $country->exists ? 'Edit country' : 'Create country' }}
                </h4>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-soft-secondary">
                        <i class="ri-arrow-left-line align-bottom me-1"></i>
                        Back to list
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST"
                          action="{{ $country->exists
                                ? route('admin.countries.update', $country)
                                : route('admin.countries.store') }}">

                        @csrf
                        @if($country->exists)
                            @method('PUT')
                        @endif

                        @php
                            $shortNames = old('short_names', $country->short_names ?? []);
                            $longNames  = old('long_names',  $country->long_names  ?? []);
                        @endphp

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">ISO2 <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="iso2"
                                       value="{{ old('iso2', $country->iso2) }}"
                                       class="form-control @error('iso2') is-invalid @enderror"
                                       maxlength="2">
                                @error('iso2')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">ISO3</label>
                                <input type="text"
                                       name="iso3"
                                       value="{{ old('iso3', $country->iso3) }}"
                                       class="form-control @error('iso3') is-invalid @enderror"
                                       maxlength="3">
                                @error('iso3')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Calling code</label>
                                <input type="text"
                                       name="calling_code"
                                       value="{{ old('calling_code', $country->calling_code) }}"
                                       class="form-control @error('calling_code') is-invalid @enderror">
                                @error('calling_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">CCTLD</label>
                                <input type="text"
                                       name="cctld"
                                       value="{{ old('cctld', $country->cctld) }}"
                                       class="form-control @error('cctld') is-invalid @enderror">
                                @error('cctld')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">UN member</label>
                                <input type="text"
                                       name="un_member"
                                       value="{{ old('un_member', $country->un_member) }}"
                                       class="form-control @error('un_member') is-invalid @enderror">
                                @error('un_member')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="card border">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Names</h6>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                                    @foreach($languages as $i => $lang)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link @if($i === 0) active @endif"
                                                    id="c-tab-{{ $lang->id }}"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#c-pane-{{ $lang->id }}"
                                                    type="button" role="tab"
                                                    aria-controls="c-pane-{{ $lang->id }}"
                                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                                {{ strtoupper($lang->code) }}
                                                @if($lang->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content">
                                    @foreach($languages as $i => $lang)
                                        @php
                                            $code = $lang->code;
                                            $isRequired = (bool) $lang->is_required;
                                        @endphp

                                        <div class="tab-pane fade @if($i === 0) show active @endif"
                                             id="c-pane-{{ $lang->id }}"
                                             role="tabpanel"
                                             aria-labelledby="c-tab-{{ $lang->id }}">

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        Short name ({{ strtoupper($code) }})
                                                        @if($isRequired)<span class="text-danger">*</span>@endif
                                                    </label>
                                                    <input type="text"
                                                           name="short_names[{{ $code }}]"
                                                           value="{{ $shortNames[$code] ?? '' }}"
                                                           @if($isRequired) required @endif
                                                           class="form-control @error("short_names.$code") is-invalid @enderror">
                                                    @error("short_names.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        Long name ({{ strtoupper($code) }})
                                                        @if($isRequired)<span class="text-danger">*</span>@endif
                                                    </label>
                                                    <input type="text"
                                                           name="long_names[{{ $code }}]"
                                                           value="{{ $longNames[$code] ?? '' }}"
                                                           @if($isRequired) required @endif
                                                           class="form-control @error("long_names.$code") is-invalid @enderror">
                                                    @error("long_names.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                        @checked(old('is_active', $country->exists ? $country->is_active : true))>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                {{ $country->exists ? 'Save changes' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
