/* languages-index.js */
/* PSR-12 equivalent in JS style: consistent formatting, clear comments */
/* global Swal */
(function () {
    // Initialize DataTable if available
    if (window.jQuery && jQuery().DataTable) {
        jQuery('#languages-table').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                { targets: [0, 1, 5, 6, 7, 8, 9], orderable: false, searchable: false }
            ]
        });
    }

    // Toast helper (uses SweetAlert2 if present)
    function toast(type, msg) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                timer: 2500,
                showConfirmButton: false,
                icon: type,
                title: msg
            });
        } else {
            // Fallback
            alert(msg);
        }
    }

    // Confirm delete
    document.querySelectorAll('form.js-delete').forEach(function (frm) {
        frm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!window.Swal) {
                frm.submit();
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'This language will be moved to trash (soft delete).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(function (res) {
                if (res.isConfirmed) {
                    frm.submit();
                }
            });
        });
    });

    // Toggle status via AJAX
    document.querySelectorAll('.js-status-switch').forEach(function (sw) {
        sw.addEventListener('change', function () {
            var tr = sw.closest('tr');
            var id = tr.getAttribute('data-id');
            var status = sw.checked ? 'Active' : 'Inactive';
            var label = tr.querySelector('.form-check-label');

            fetch('/eradmin/languages/' + id + '/toggle-status', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
                .then(function (r) {
                    return r.json().then(function (payload) {
                        return { ok: r.ok, data: payload };
                    });
                })
                .then(function (res) {
                    if (res.ok && res.data.ok) {
                        label.textContent = res.data.status;
                        toast('success', 'Status updated.');
                        return;
                    }

                    // Revert UI on error
                    sw.checked = !sw.checked;
                    label.textContent = sw.checked ? 'Active' : 'Inactive';
                    toast('error', res.data.message || 'Failed to update status.');
                })
                .catch(function () {
                    sw.checked = !sw.checked;
                    label.textContent = sw.checked ? 'Active' : 'Inactive';
                    toast('error', 'Request failed.');
                });
        });
    });
})();

(function () {
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    function toast(type, msg) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                timer: 2500,
                showConfirmButton: false,
                icon: type,
                title: msg
            });
            return;
        }

        alert(msg);
    }

    async function patchJson(url, payload) {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            throw new Error((data && data.message) ? data.message : 'Request failed');
        }

        return data;
    }

    function setSwitchLabel(labelEl, isOn) {
        if (!labelEl) {
            return;
        }
        const yes = window.transYes || 'Yes';
        const no = window.transNo || 'No';
        labelEl.textContent = isOn ? yes : no;
    }

    document.addEventListener('change', async function (e) {
        const requiredSwitch = e.target.closest('.js-required-switch');
        if (!requiredSwitch) {
            return;
        }

        const tr = requiredSwitch.closest('tr');
        if (!tr || !tr.dataset.toggleRequiredUrl) {
            return;
        }

        const url = tr.dataset.toggleRequiredUrl;
        const previousValue = !requiredSwitch.checked; // change event-dən əvvəlki state
        const labelEl = requiredSwitch.closest('.form-check')?.querySelector('.form-check-label');

        try {
            requiredSwitch.disabled = true;

            const data = await patchJson(url, {
                is_required: requiredSwitch.checked ? 1 : 0
            });

            if (data && data.ok) {
                const serverValue = !!data.is_required;
                requiredSwitch.checked = serverValue;
                setSwitchLabel(labelEl, serverValue);
                toast('success', data.message || 'Updated');
                return;
            }

            requiredSwitch.checked = previousValue;
            setSwitchLabel(labelEl, previousValue);
            toast('error', (data && data.message) ? data.message : 'Update failed');
        } catch (err) {
            requiredSwitch.checked = previousValue;
            setSwitchLabel(labelEl, previousValue);
            toast('error', err.message || 'Update failed');
        } finally {
            requiredSwitch.disabled = false;
        }
    });
})();
