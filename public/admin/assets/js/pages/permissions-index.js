(function(){
    const $ = window.jQuery;
    if (!$) return;

    $(function(){
        const table = $('#perms_table').DataTable({
            paging:true, pageLength:10, lengthChange:false, order:[[0,'desc']],
            dom:'<"d-flex justify-content-between align-items-center mb-2"B>t<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons:[{extend:'excelHtml5', text:'Excel', title:'permissions', exportOptions:{columns:[0,1,2]}, className:'btn btn-outline-primary'}],
            language:{info:"_START_–_END_ / _TOTAL_", zeroRecords:"Nəticə yoxdur", paginate:{previous:"Əvvəlki", next:"Növbəti"}}
        });

        // search
        $('#perms_q').on('keyup change', function(){ table.search(this.value).draw(); });

        // group filter
        $.fn.dataTable.ext.search.push(function(settings, data, idx){
            const g = $('#perms_group').val(); if(!g) return true;
            const rowGroup = $(table.row(idx).node()).find('td.perm-group').text().trim();
            return rowGroup === g;
        });
        $('#perms_group').on('change', function(){ table.draw(); });

        // confirm delete
        document.addEventListener('submit', function(e){
            const form = e.target;
            if (form.matches('form.js-delete')) {
                e.preventDefault();
                Swal.fire({title:'Əminsiniz?', text:'Geri qaytarılmayacaq', icon:'warning',
                    showCancelButton:true, confirmButtonText:'Bəli, sil', cancelButtonText:'İmtina'
                }).then(r=>{ if(r.isConfirmed) form.submit(); });
            }
        });
    });
})();
