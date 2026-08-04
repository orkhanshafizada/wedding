{{-- SweetAlert2 — Session flash və Validation xətaları --}}
@if (session('success') || session('error') || session('warning') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = window.AdminToast ? window.AdminToast() : Swal.mixin({
                toast:true, position:'top-end', showConfirmButton:false, timer:2200, timerProgressBar:true,
                didOpen:(t)=>{t.addEventListener('mouseenter',Swal.stopTimer); t.addEventListener('mouseleave',Swal.resumeTimer);}
            });

            @if (session('success'))   Toast.fire({ icon:'success', title: @json(session('success')) }); @endif
            @if (session('error'))     Toast.fire({ icon:'error',   title: @json(session('error'))   }); @endif
            @if (session('warning'))   Toast.fire({ icon:'warning', title: @json(session('warning')) }); @endif
            @if (session('info'))      Toast.fire({ icon:'info',    title: @json(session('info'))    }); @endif
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Xəta',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'Bağla'
            });
        });
    </script>
@endif
