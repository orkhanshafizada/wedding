<div class="d-inline-flex align-items-center gap-1 action-buttons">
    {{-- Edit --}}
    <a href="{{ $action_update }}"
       class="btn btn-icon btn-sm btn-ghost-primary rounded-circle border"
       data-bs-toggle="tooltip"
       title="{{ __('Edit') }}"
       aria-label="{{ __('Edit') }}">
        <i class="ri-edit-line"></i>
    </a>

    {{-- Delete --}}
    <form class="d-inline js-delete"
          method="POST"
          action="{{ $action_delete }}"
          data-confirm-title="{{ __('Confirm delete') }}"
          data-confirm-text="{{ __('do you confirm ?') }}"
          data-confirm-yes="{{ __('Yes delete') }}"
          data-confirm-no="{{ __('No cancel') }}">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="btn btn-icon btn-sm btn-ghost-danger rounded-circle border"
                data-bs-toggle="tooltip"
                title="{{ __('Delete') }}"
                aria-label="{{ __('Delete') }}">
            <i class="ri-delete-bin-line"></i>
        </button>
    </form>
</div>

@push('styles')
    <style>
        .action-buttons .btn { width: 32px; height: 32px; padding: 0; }
        .action-buttons .btn i { font-size: 16px; line-height: 1; }
        .action-buttons .btn.border { border-color: var(--vz-border-color, #e9ebec); background: #fff; }
        .action-buttons .btn:hover { box-shadow: 0 2px 6px rgba(16,24,40,.08); }
    </style>
@endpush
