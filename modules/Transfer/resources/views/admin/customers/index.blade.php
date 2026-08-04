@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Müştərilərin transferi</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.transfer.index') }}">Transfer</a></li>
                                <li class="breadcrumb-item active">Müştərilər</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Toplam source customer</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-primary fs-14 mb-0">OpenCart</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['total_count'] }}</h4>
                                    <span class="text-muted">oc_customer</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-user-3-line text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Importa hazır customer</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">Ready</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['ready_count'] }}</h4>
                                    <span class="text-muted">boş və duplicate email xaric</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-user-follow-line text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Boş email</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-warning fs-14 mb-0">Skip</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['empty_email_count'] }}</h4>
                                    <span class="text-muted">email boş və ya invalid</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-mail-close-line text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Duplicate email</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-danger fs-14 mb-0">Skip</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['duplicate_email_count'] }}</h4>
                                    <span class="text-muted">source daxilində təkrarlanan</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-user-unfollow-line text-danger"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-8">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Transfer əməliyyatı</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-border-left mb-4" role="alert">
                                Customer-lər queue və chunk ilə import ediləcək. OpenCart password hash ayrıca saxlanacaq, ilk uğurlu login zamanı Laravel hash-ə çevriləcək. Address-lər oc_address əsasında customer_addresses cədvəlinə sync ediləcək.
                            </div>

                            <form action="{{ route('admin.transfer.customers.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    Transferə başla
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Address sayı</h4>
                        </div>
                        <div class="card-body">
                            <h3 class="mb-3">{{ $preview['address_count'] }}</h3>
                            <p class="text-muted mb-0">oc_address cədvəlində customer_id &gt; 0 olan sətirlər</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1">Preview</h4>
                                <span class="badge bg-primary-subtle text-primary">{{ count($preview['sample_customers']) }} qeyd</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ad</th>
                                        <th>Soyad</th>
                                        <th>Email</th>
                                        <th>Telefon</th>
                                        <th>Status</th>
                                        <th>Address sayı</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['sample_customers'] as $customer)
                                        <tr>
                                            <td>{{ $customer['customer_id'] }}</td>
                                            <td>{{ $customer['firstname'] }}</td>
                                            <td>{{ $customer['lastname'] }}</td>
                                            <td>{{ $customer['email'] }}</td>
                                            <td>{{ $customer['telephone'] }}</td>
                                            <td>
                                                @if ($customer['status'] === 1)
                                                    <span class="badge bg-success-subtle text-success">Aktiv</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Passiv</span>
                                                @endif
                                            </td>
                                            <td>{{ $customer['address_count'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Import üçün uyğun customer tapılmadı.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
