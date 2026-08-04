@extends('admin.layouts.app')

@section('title', 'Countries')

@section('content')
    @php
        $primaryCode = $requiredLanguageCodes[0] ?? ($activeLanguageCodes[0] ?? null);

        $secondaryCode = null;
        if (!empty($requiredLanguageCodes) && count($requiredLanguageCodes) > 1) {
            $secondaryCode = $requiredLanguageCodes[1];
        } elseif (!empty($activeLanguageCodes)) {
            foreach ($activeLanguageCodes as $c) {
                if ($c !== $primaryCode) { $secondaryCode = $c; break; }
            }
        }
    @endphp

    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">Countries</h4>

                @can('country.create')
                    <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>Add new
                    </a>
                @endcan
            </div>

            <form method="GET" action="{{ route('admin.countries.index') }}" class="card mb-3">
                <div class="card-body row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               class="form-control"
                               placeholder="Search">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="">All</option>
                            <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
                            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-link">Reset</a>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 60px">#</th>
                                <th>Name</th>
                                <th style="width: 90px">ISO2</th>
                                <th style="width: 90px">ISO3</th>
                                <th style="width: 140px">Calling code</th>
                                <th style="width: 120px">CCTLD</th>
                                <th style="width: 110px">Status</th>
                                <th style="width: 150px">Status action</th>
                                <th class="text-end" style="width: 130px">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($countries as $country)
                                <tr>
                                    <td>{{ $country->id }}</td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $primaryCode ? ($country->short_names[$primaryCode] ?? '') : '' }}
                                        </div>
                                        @if($secondaryCode)
                                            <div class="text-muted small">
                                                {{ $country->short_names[$secondaryCode] ?? '' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-body">{{ $country->iso2 }}</span>
                                    </td>
                                    <td>{{ $country->iso3 }}</td>
                                    <td>{{ $country->calling_code }}</td>
                                    <td>{{ $country->cctld }}</td>
                                    <td>
                                        @if($country->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @can('country.edit')
                                            <form action="{{ route('admin.countries.toggle-status', $country) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-soft-secondary">
                                                    {{ $country->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @can('country.edit')
                                                <a href="{{ route('admin.countries.edit', $country) }}"
                                                   class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            @endcan

                                            @can('country.delete')
                                                <form action="{{ route('admin.countries.destroy', $country) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Delete this country?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-soft-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No records found
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($countries instanceof \Illuminate\Contracts\Pagination\Paginator && $countries->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $countries->firstItem() }} to {{ $countries->lastItem() }} of {{ $countries->total() }}
                            </div>
                            <div>
                                {{ $countries->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
