@push('scripts')
    <script>
        (function () {
            const typeSelect = document.getElementById('payment-method-type');
            const gatewaySelect = document.getElementById('payment-method-gateway');
            const gatewayFieldWrapper = document.getElementById('gateway-field-wrapper');
            const gatewaySettingsCard = document.getElementById('gateway-settings-card');
            const gatewaySections = document.querySelectorAll('.gateway-settings-section');
            const installmentCard = document.getElementById('installment-card');
            const addInstallmentRowButton = document.getElementById('add-installment-row');
            const installmentTableBody = document.querySelector('#installment-table tbody');

            function currentRowIndex() {
                return installmentTableBody.querySelectorAll('tr').length;
            }

            function installmentRowTemplate(index) {
                return `
                    <tr>
                        <td>
                            <input type="number" min="1" name="installments[${index}][month]" class="form-control">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="installments[${index}][percent]" class="form-control">
                        </td>
                        <td>
                            <input type="number" min="0" name="installments[${index}][sort_order]" value="0" class="form-control">
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-installment-row">{{ __('Remove') }}</button>
                        </td>
                    </tr>
                `;
            }

            function syncGatewaySections() {
                const gateway = gatewaySelect.value;

                gatewaySections.forEach(function (section) {
                    section.style.display = section.dataset.gateway === gateway ? '' : 'none';
                });

                gatewaySettingsCard.style.display = gateway ? '' : 'none';
            }

            function syncByType() {
                const type = typeSelect.value;

                if (type === 'online') {
                    gatewayFieldWrapper.style.display = '';
                } else {
                    gatewayFieldWrapper.style.display = 'none';
                    gatewaySelect.value = '';
                }

                installmentCard.style.display = '';
                syncGatewaySections();
            }

            addInstallmentRowButton.addEventListener('click', function () {
                installmentTableBody.insertAdjacentHTML('beforeend', installmentRowTemplate(currentRowIndex()));
            });

            document.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-installment-row');

                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('tr');

                if (row) {
                    row.remove();
                }
            });

            typeSelect.addEventListener('change', syncByType);
            gatewaySelect.addEventListener('change', syncGatewaySections);

            syncByType();
        })();
    </script>
@endpush
