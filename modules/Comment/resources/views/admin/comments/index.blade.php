@extends('admin.layouts.app')

@section('title', __('Comments'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Comments') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ __('Comments') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0">
                    <form action="{{ route('admin.comment.comments.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text"
                                       name="q"
                                       value="{{ $filters['q'] ?? '' }}"
                                       class="form-control"
                                       placeholder="{{ __('Fullname, comment, customer, variation') }}">
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-control" data-choices>
                                    <option value="">{{ __('All') }}</option>
                                    @foreach(\Modules\Comment\Enums\CommentStatusEnum::getOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">{{ __('Date from') }}</label>
                                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">{{ __('Date to') }}</label>
                                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">{{ __('Per page') }}</label>
                                <select name="per_page" class="form-control" data-choices>
                                    @foreach([20, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" @selected((int) ($filters['per_page'] ?? 20) === $perPage)>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line align-bottom me-1"></i>{{ __('Filter') }}
                                    </button>

                                    <a href="{{ route('admin.comment.comments.index') }}" class="btn btn-light">
                                        <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @include('admin.shared.alerts')

                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>{{ __('Fullname') }}</th>
                                <th style="width: 100px;">{{ __('Rating') }}</th>
                                <th>{{ __('Comment') }}</th>
                                <th>{{ __('Variation') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th style="width: 140px;">{{ __('Status') }}</th>
                                <th style="width: 180px;">{{ __('Created at') }}</th>
                                <th class="text-end" style="width: 260px;">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($comments as $comment)
                                @php
                                    $variation = $comment->variation;
                                    $variationName = $variation
                                        ? (
                                            $variation->translations->firstWhere('language_id', \App\Models\Language::query()->where('code', app()->getLocale())->value('id'))?->name
                                            ?? $variation->translations->first()?->name
                                            ?? ('#' . $variation->id)
                                        )
                                        : '-';

                                    $customerLabel = $comment->customer
                                        ? trim(($comment->customer->name ?? '') . ' ' . ($comment->customer->surname ?? ''))
                                        : '-';

                                    if ($customerLabel === '') {
                                        $customerLabel = $comment->customer?->email ?? '-';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ $comment->fullname }}</td>
                                    <td>{{ $comment->rating }}</td>
                                    <td style="white-space: normal; min-width: 280px;">{{ $comment->comment }}</td>
                                    <td style="white-space: normal; min-width: 240px;">{{ $variationName }}</td>
                                    <td>{{ $customerLabel }}</td>
                                    <td>
                                        <span class="badge {{ \Modules\Comment\Enums\CommentStatusEnum::getBadgeClass($comment->status) }}">
                                            {{ \Modules\Comment\Enums\CommentStatusEnum::getLabel($comment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $comment->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @can('comment.edit')
                                                <form action="{{ route('admin.comment.comments.approve', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.comment.comments.decline', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('comment.delete')
                                                <form action="{{ route('admin.comment.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
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
                                        {{ __('No comments found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $comments->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
