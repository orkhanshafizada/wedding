(function(){
    const $ = window.jQuery;
    if (!$) return;

    $(function () {
        const table = $('#admins_table').DataTable({
            paging:true, pageLength:10, lengthChange:false, ordering:true, order:[[0,'desc']], info:true,
            autoWidth:false,
            dom: '<"d-flex justify-content-between align-items-center mb-2"B>t<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [{
                extend:'excelHtml5', text:'Excel', title:'admins',
                exportOptions:{
                    columns:[0,1,2,3,4],
                    format:{ body:(data, row, col, node)=> {
                            if ($(node).hasClass('status-cell')) return ($(node).data('value')||'');
                            return $(data).text ? $(data).text() : String(data).replace(/<[^>]*>?/gm,'');
                        }}
                },
                className:'btn btn-outline-primary'
            }],
            language:{ info:"_START_–_END_ / _TOTAL_", zeroRecords:"Nəticə yoxdur", paginate:{previous:"Əvvəlki",next:"Növbəti"} }
        });

        // Global helpers
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const Toast = window.AdminToast ? window.AdminToast() : null;

        // Toolbar search
        $('#admins_q').on('keyup change', function(){ table.search(this.value).draw(); });

        // Status filter
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
            const selected = $('#admins_status').val();
            if(!selected) return true;
            const cell = $(table.row(dataIndex).node()).find('td.status-cell');
            const val  = (cell.data('value')||'').toString();
            return val === selected;
        });
        $('#admins_status').on('change', function(){ table.draw(); });

        // Status toggle
        $('#admins_table').on('change','.js-toggle-status', async function(){
            const $sw = $(this), url = $sw.data('url'), active = $sw.is(':checked') ? 1 : 0;
            try {
                const res = await fetch(url,{
                    method:'PATCH',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
                    body: JSON.stringify({active})
                });
                const json = await res.json();
                if(!json.ok) throw new Error(json.message || 'Xəta');
                $sw.closest('td.status-cell').data('value', json.status);
                if (Toast) Toast.fire({icon:'success', title: active ? 'Aktiv edildi' : 'Deaktiv edildi'});
            } catch(e){
                $sw.prop('checked', !$sw.is(':checked'));
                Swal.fire({icon:'error', title:'Xəta', text: e.message || 'Status yenilənmədi'});
            }
        });

        // SweetAlert confirm delete
        document.addEventListener('submit', function(e){
            const form = e.target;
            if (form.matches('form.js-delete')) {
                e.preventDefault();
                Swal.fire({
                    title: 'Əminsiniz?',
                    text: 'Bu əməliyyat geri qaytarılmayacaq.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Bəli, sil',
                    cancelButtonText: 'İmtina'
                }).then((r)=>{ if(r.isConfirmed) form.submit(); });
            }
        });
    });
})();
