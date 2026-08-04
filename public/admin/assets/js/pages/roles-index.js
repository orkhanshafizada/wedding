(function(){
    const $ = window.jQuery;
    if (!$) return;

    $(function(){
        const t = $('#roles_table').DataTable({
            paging:true, pageLength:10, lengthChange:false, order:[[0,'desc']],
            dom:'<"d-flex justify-content-between align-items-center mb-2"B>t<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons:[{extend:'excelHtml5', text:'Excel', title:'roles', exportOptions:{columns:[0,1,2]}, className:'btn btn-outline-primary'}],
            language:{info:"_START_–_END_ / _TOTAL_", zeroRecords:"Nəticə yoxdur", paginate:{previous:"Əvvəlki", next:"Növbəti"}}
        });

        $('#roles_q').on('keyup change', function(){ t.search(this.value).draw(); });

        // SweetAlert confirm delete
        document.addEventListener('submit', function(e){
            const form = e.target;
            if (form.matches('form.js-delete')) {
                e.preventDefault();
                Swal.fire({title:'Əminsiniz?', text:'Geri qaytarılmayacaq', icon:'warning', showCancelButton:true,
                    confirmButtonText:'Bəli, sil', cancelButtonText:'İmtina'}).then(r=>{ if(r.isConfirmed) form.submit(); });
            }
        });
    });
})();
