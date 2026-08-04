// E-Commerce Admin — Global JS (Bootstrap 5)

// Sidebar: desktop collapse / mobile slide-in
(function(){
    const SIDEBAR_COLLAPSE_KEY = 'admin.sidebar.collapsed';
    const sidebar  = document.getElementById('adminSidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const btnMobile   = document.querySelector('[data-sidebar-mobile]');
    const btnCollapse = document.querySelector('[data-sidebar-collapse]');

    if (!sidebar) return;

    const isLgUp = () => window.matchMedia('(min-width: 992px)').matches;

    const setCollapsed = (collapsed) => {
        if (collapsed) {
            sidebar.classList.add('is-collapsed');
            document.body.classList.add('sidebar-collapsed');
            // close open submenus
            sidebar.querySelectorAll('.collapse.show').forEach(c=>{
                const inst = bootstrap.Collapse.getInstance(c) || new bootstrap.Collapse(c,{toggle:false});
                inst.hide();
                const toggler = sidebar.querySelector(`[data-bs-target="#${c.id}"], a[href="#${c.id}"]`);
                if (toggler) toggler.setAttribute('aria-expanded','false');
            });
        } else {
            sidebar.classList.remove('is-collapsed');
            document.body.classList.remove('sidebar-collapsed');
        }
        try { localStorage.setItem(SIDEBAR_COLLAPSE_KEY, collapsed ? '1' : '0'); } catch(_) {}
    };

    const setMobile = (show) => {
        if (show) { sidebar.classList.add('show');  backdrop && backdrop.classList.add('show'); }
        else      { sidebar.classList.remove('show');backdrop && backdrop.classList.remove('show'); }
    };

    // init from storage
    let wantCollapsed = false;
    try { wantCollapsed = localStorage.getItem(SIDEBAR_COLLAPSE_KEY) === '1'; } catch(_) {}
    setCollapsed(isLgUp() ? wantCollapsed : false);

    btnCollapse && btnCollapse.addEventListener('click', () => {
        setCollapsed(!sidebar.classList.contains('is-collapsed'));
    });
    btnMobile && btnMobile.addEventListener('click', () => setMobile(!sidebar.classList.contains('show')));
    backdrop  && backdrop.addEventListener('click', () => setMobile(false));

    window.addEventListener('resize', () => {
        if (isLgUp()) {
            setMobile(false);
            let c = false; try { c = localStorage.getItem(SIDEBAR_COLLAPSE_KEY) === '1'; } catch(_) {}
            setCollapsed(c);
        } else {
            setCollapsed(false);
        }
    });

    // Mobile: any nav link click closes sidebar
    sidebar.addEventListener('click', (e) => {
        const a = e.target.closest('a.nav-link');
        if (a && !isLgUp()) setMobile(false);
    });
})();

// SweetAlert2 Toast helper — global
window.AdminToast = function () {
    return Swal.mixin({
        toast:true, position:'top-end', showConfirmButton:false,
        timer:2000, timerProgressBar:true,
        didOpen:(t)=>{t.addEventListener('mouseenter',Swal.stopTimer); t.addEventListener('mouseleave',Swal.resumeTimer);}
    });
};
