<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── RESPONSIVE SIDEBAR ──
    (function () {
        const sidebar     = document.getElementById('sidebar');
        const backdrop    = document.getElementById('sidebar-backdrop');
        const toggleBtn   = document.getElementById('toggleSidebar');
        const mainContent = document.getElementById('main-content');

        const isMobile = () => window.innerWidth < 768;
        const isTablet = () => window.innerWidth >= 768 && window.innerWidth < 1024;

        // ── Mobile: overlay con clase .show ──
        function closeMobile() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('active');
        }
        function openMobile() {
            sidebar.classList.add('show');
            backdrop.classList.add('active');
        }

        // ── Desktop/tablet: colapsar con clase .hidden + .expanded en main ──
        function collapseDesktop() {
            sidebar.classList.add('hidden');
            if (mainContent) mainContent.classList.add('expanded');
        }
        function expandDesktop() {
            sidebar.classList.remove('hidden');
            if (mainContent) mainContent.classList.remove('expanded');
        }
        function toggleDesktop() {
            const collapsed = sidebar.classList.toggle('hidden');
            if (mainContent) mainContent.classList.toggle('expanded', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        }

        // ── Inicialización ──
        function initSidebar() {
            if (isMobile()) {
                // Mobile: limpiar estado desktop, sidebar cerrado por defecto
                sidebar.classList.remove('hidden', 'show');
                backdrop.classList.remove('active');
                if (mainContent) mainContent.classList.remove('expanded');
            } else {
                // Desktop/tablet: limpiar estado mobile
                sidebar.classList.remove('show');
                backdrop.classList.remove('active');
                const saved = localStorage.getItem('sidebarCollapsed');
                // Tablet sin preferencia guardada → colapsado por defecto
                const defaultCollapsed = isTablet() && saved === null;
                if (saved === '1' || defaultCollapsed) {
                    collapseDesktop();
                } else if (saved === '0' || (!isTablet() && saved === null)) {
                    expandDesktop();
                }
            }
        }

        initSidebar();

        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isMobile()) {
                sidebar.classList.contains('show') ? closeMobile() : openMobile();
            } else {
                toggleDesktop();
            }
        });

        backdrop.addEventListener('click', closeMobile);

        // Reubicar al cambiar tamaño de ventana
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initSidebar, 80);
        });
    })();

    // ── TOGGLE TEMA OSCURO / CLARO ──
    (function () {
        var html = document.documentElement;
        var icon = document.getElementById('theme-icon');

        function actualizarIcono(tema) {
            if (!icon) return;
            icon.className = tema === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }

        // Icono inicial
        actualizarIcono(html.getAttribute('data-theme') || 'light');

        document.getElementById('btn-theme').addEventListener('click', function () {
            var actual = html.getAttribute('data-theme') || 'light';
            var nuevo  = actual === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme',    nuevo);
            html.setAttribute('data-bs-theme', nuevo);
            localStorage.setItem('theme', nuevo);
            actualizarIcono(nuevo);
        });
    })();
</script>
</body>
</html>
