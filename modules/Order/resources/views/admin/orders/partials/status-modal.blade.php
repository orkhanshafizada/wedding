<div class="modal fade" id="orderStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content border-0 shadow-lg">
            @csrf

            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title mb-1">{{ __('Update status') }}</h5>
                    <div class="text-muted small">{{ __('Update order and payment status together.') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body pt-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Order status') }}</label>
                    <select class="form-select" name="to_status">
                        @foreach($statusOptions as $statusOption)
                            <option value="{{ $statusOption['code'] }}">{{ $statusOption['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Payment status') }}</label>
                    <select class="form-select" name="payment_status">
                        <option value="">{{ __('No change') }}</option>
                        @foreach($paymentStatusOptions as $paymentStatusOption)
                            <option value="{{ $paymentStatusOption['code'] }}">{{ $paymentStatusOption['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold">{{ __('Note') }}</label>
                    <textarea class="form-control" name="note" rows="4" placeholder="{{ __('Note') }}"></textarea>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line align-bottom me-1"></i>{{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
