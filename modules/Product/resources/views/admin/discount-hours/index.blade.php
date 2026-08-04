@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Discount Hours') }}</h4>
                        @can('product.discount_hour.create')
                            <div class="page-title-right">
                                <a href="{{ route('admin.product.discount_hours.create') }}" class="btn btn-primary">
                                    <i class="ri-add-line me-1"></i>{{ __('Add') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search by ID') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">{{ __('All status') }}</option>
                                <option value="Active" @selected(request('status') === 'Active')>{{ __('Active') }}</option>
                                <option value="Inactive" @selected(request('status') === 'Inactive')>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-soft-primary" type="submit">
                                <i class="ri-search-line me-1"></i>{{ __('Filter') }}
                            </button>
                            <a href="{{ route('admin.product.discount_hours.index') }}" class="btn btn-soft-secondary">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Starts') }}</th>
                                <th>{{ __('Ends') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Items') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($discountHours as $discountHour)
                                <tr>
                                    <td>{{ $discountHour->id }}</td>
                                    <td>{{ optional($discountHour->starts_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ optional($discountHour->ends_at)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($discountHour->status === 'Active')
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ (int) $discountHour->items_count }}</td>
                                    <td class="text-end">
                                        @can('product.discount_hour.edit')
                                            <a href="{{ route('admin.product.discount_hours.edit', $discountHour) }}" class="btn btn-sm btn-soft-primary">
                                                <i class="ri-edit-2-line"></i> </a>
                                        @endcan
                                        @can('product.discount_hour.delete')
                                            <form action="{{ route('admin.product.discount_hours.destroy', $discountHour) }}" method="post" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Delete?') }}')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-sm btn-soft-danger">
                                                    <i class="ri-delete-bin-5-line"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">{{ __('No data') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $discountHours->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
