<div class="d-flex gap-2">
    <a href="{{ route('admin.grids.edit', [$menu, $grid]) }}" class="btn btn-sm btn-primary" title="{{ __('Edit') }}">
        <i class="ri-pencil-line"></i>
    </a>

    <form action="{{ route('admin.grids.destroy', [$menu, $grid]) }}" method="POST" class="d-inline"
          onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Delete') }}">
            <i class="ri-delete-bin-line"></i>
        </button>
    </form>
</div>
