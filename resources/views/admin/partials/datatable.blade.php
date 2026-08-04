@php
    $tableId = $tableId ?? 'dataTable_' . uniqid();
    $columns = $columns ?? [];
    $rows = $rows ?? [];
    $checkboxes = $checkboxes ?? true;
    $actions = $actions ?? true;
    $exportButton = $exportButton ?? false;
    $exportRoute = $exportRoute ?? null;
    $editRoute = $editRoute ?? null;
    $deleteButton = $deleteButton ?? false;
    $deleteRoute = $deleteRoute ?? null;
    $bulkDeleteRoute = $bulkDeleteRoute ?? null;
    $pageLength = $pageLength ?? 10;
    $order = $order ?? [1, 'desc'];
@endphp

<div class="datatable-wrapper">
    {{-- Action Buttons --}}
    @if($exportButton || $deleteButton)
        <div class="d-flex justify-content-end align-items-center mb-3 gap-2">
            @if($exportButton && $exportRoute)
                <a href="{{ $exportRoute }}" class="btn btn-success">
                    <i class="ri-file-excel-2-line me-1"></i> {{ __('Export XLS') }}
                </a>
            @endif

            @if($deleteButton)
                <button type="button" class="btn btn-danger delete-selected-btn"
                        data-table="{{ $tableId }}"
                        data-bulk-delete-route="{{ $bulkDeleteRoute }}"
                        disabled>
                    <i class="ri-delete-bin-line me-1"></i> {{ __('Delete Selected') }}
                </button>
            @endif
        </div>
    @endif

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="{{ $tableId }}" class="table table-striped table-bordered align-middle w-100 ajax-datatable">
                    <thead class="table-light">
                        <tr>
                            @if($checkboxes)
                                <th width="30">
                                    <input type="checkbox" class="form-check-input check-all" data-table="{{ $tableId }}">
                                </th>
                            @endif

                            @foreach($columns as $column)
                                <th {!! isset($column['width']) ? 'width="'.$column['width'].'"' : '' !!}>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach

                            @if($actions)
                                <th width="80">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @if($checkboxes)
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $row['id'] }}">
                                    </td>
                                @endif

                                {{-- ID Column --}}
                                <td>{{ $row['id'] }}</td>

                                    @foreach($row['cells'] as $cell)
                                        <td>
                                            @if(is_array($cell) && ($cell['type'] ?? null) === 'status_switch')
                                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                    <input
                                                            type="checkbox"
                                                            class="form-check-input response-status-switch"
                                                            role="switch"
                                                            data-update-url="{{ $cell['update_url'] }}"
                                                            data-current-status="{{ $cell['checked'] ? 1 : 0 }}"
                                                            aria-label="{{ __('Change response status') }}"
                                                            @checked($cell['checked'])
                                                    >
                                                </div>
                                            @else
                                                {!! $cell !!}
                                            @endif
                                        </td>
                                    @endforeach

                                @if($actions)
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($editRoute)
                                                <a href="{{ str_replace(':id', $row['id'], $editRoute) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                            @endif

                                            @if($deleteRoute)
                                                <form action="{{ str_replace(':id', $row['id'], $deleteRoute) }}"
                                                      method="POST"
                                                      class="d-inline delete-form"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + ($checkboxes ? 1 : 0) + ($actions ? 1 : 0) }}"
                                    class="text-center text-muted py-4">
                                    {{ __('No data available') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Universal DataTable Handler
    if (typeof window.DataTableHandler === 'undefined') {

        window.DataTableHandler = {
            tables: {},
            bindStatusSwitchEvents: function(tableId) {
                $('#' + tableId + ' .response-status-switch')
                    .off('change.responseStatus')
                    .on('change.responseStatus', function() {
                        const statusSwitch = $(this);
                        const updateUrl = statusSwitch.data('update-url');
                        const previousStatus = Number(
                            statusSwitch.attr('data-current-status')
                        );
                        const requestedStatus = statusSwitch.prop('checked') ? 1 : 0;

                        statusSwitch.prop('disabled', true);

                        $.ajax({
                            url: updateUrl,
                            method: 'PATCH',
                            headers: {
                                Accept: 'application/json'
                            },
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                status: requestedStatus
                            },
                            success: function(response) {
                                const savedStatus = Number(response.data.status);

                                statusSwitch
                                    .prop('checked', savedStatus === 1)
                                    .attr('data-current-status', savedStatus);
                            },
                            error: function(xhr) {
                                statusSwitch
                                    .prop('checked', previousStatus === 1)
                                    .attr('data-current-status', previousStatus);

                                alert(
                                    xhr.responseJSON?.message ||
                                    '{{ __('Failed to update response status.') }}'
                                );
                            },
                            complete: function() {
                                statusSwitch.prop('disabled', false);
                            }
                        });
                    });
            },
            init: function(tableId, options = {}) {
                const defaultOptions = {
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('All') }}"]],
                    order: [[1, 'desc']],
                    columnDefs: [{orderable: false, targets: []}],
                    language: {
                        search: "{{ __('Search') }}:",
                        lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
                        info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
                        infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
                        infoFiltered: "({{ __('filtered from') }} _MAX_ {{ __('total entries') }})",
                        paginate: {
                            first: "{{ __('First') }}",
                            last: "{{ __('Last') }}",
                            next: "{{ __('Next') }}",
                            previous: "{{ __('Previous') }}"
                        },
                        emptyTable: "{{ __('No data available in table') }}"
                    },
                    drawCallback: function() {
                        DataTableHandler.bindCheckboxEvents(tableId);
                        DataTableHandler.bindStatusSwitchEvents(tableId);
                    }
                };

                const config = $.extend(true, {}, defaultOptions, options);
                this.tables[tableId] = $('#' + tableId).DataTable(config);
                this.bindCheckboxEvents(tableId);
                this.bindDeleteButton(tableId);
                this.bindStatusSwitchEvents(tableId);
            },

            bindCheckboxEvents: function(tableId) {
                $('.check-all[data-table="' + tableId + '"]').off('change').on('change', function() {
                    const isChecked = $(this).prop('checked');
                    $('#' + tableId + ' .row-checkbox:visible').prop('checked', isChecked);
                    DataTableHandler.updateDeleteButton(tableId);
                });

                $('#' + tableId + ' .row-checkbox').off('change').on('change', function() {
                    DataTableHandler.updateCheckAllState(tableId);
                    DataTableHandler.updateDeleteButton(tableId);
                });
            },

            updateCheckAllState: function(tableId) {
                const total = $('#' + tableId + ' .row-checkbox:visible').length;
                const checked = $('#' + tableId + ' .row-checkbox:visible:checked').length;
                $('.check-all[data-table="' + tableId + '"]').prop('checked', total === checked && total > 0);
            },

            updateDeleteButton: function(tableId) {
                const count = $('#' + tableId + ' .row-checkbox:checked').length;
                $('.delete-selected-btn[data-table="' + tableId + '"]').prop('disabled', count === 0);
            },

            bindDeleteButton: function(tableId) {
                $('.delete-selected-btn[data-table="' + tableId + '"]').off('click').on('click', function() {
                    const bulkDeleteRoute = $(this).data('bulk-delete-route');

                    if (!bulkDeleteRoute) {
                        console.error('Bulk delete route not provided');
                        return;
                    }

                    const selectedIds = [];
                    $('#' + tableId + ' .row-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length > 0) {
                        if (confirm('{{ __('Are you sure you want to delete selected items?') }}')) {
                            $.ajax({
                                url: bulkDeleteRoute,
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    ids: selectedIds
                                },
                                success: function(response) {
                                    if (response.success) {
                                        location.reload();
                                    } else {
                                        alert(response.message || '{{ __('Failed to delete items') }}');
                                    }
                                },
                                error: function(xhr) {
                                    alert('{{ __('An error occurred while deleting items') }}');
                                }
                            });
                        }
                    }
                });
            }
        };
    }
    </script>
    @endpush
@endonce

@push('scripts')
<script>
$(document).ready(function() {
    @php
        $disableSort = [];
        if($checkboxes) $disableSort[] = 0;
        if($actions) $disableSort[] = -1;
    @endphp

    DataTableHandler.init('{{ $tableId }}', {
        pageLength: {{ $pageLength }},
        order: @json($order),
        columnDefs: [{orderable: false, targets: @json($disableSort)}]
    });
});
// Handle delete selected event
$(document).on('datatable:delete', function(e, tableId, selectedIds) {
    console.log('Delete IDs:', selectedIds);
    // TODO: Implement delete functionality
    alert('{{ __('Delete functionality will be implemented') }}');
});
</script>
@endpush

