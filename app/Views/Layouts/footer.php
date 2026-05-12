<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── RESPONSIVE SIDEBAR ──
    (function () {
        const sidebar   = document.getElementById('sidebar');
        const backdrop  = document.getElementById('sidebar-backdrop');
        const toggleBtn = document.getElementById('toggleSidebar');
        const mainContent = document.getElementById('main-content');
        const isMobile  = () => window.innerWidth < 768;

        function closeMobileSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('active');
        }

        function openMobileSidebar() {
            sidebar.classList.add('show');
            backdrop.classList.add('active');
        }

        function toggleDesktopSidebar() {
            const collapsed = sidebar.classList.toggle('hidden');
            if (mainContent) mainContent.classList.toggle('expanded', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        }

        // Restaurar estado de sidebar en desktop al cargar
        if (!isMobile()) {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                sidebar.classList.add('hidden');
                if (mainContent) mainContent.classList.add('expanded');
            }
        }

        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isMobile()) {
                sidebar.classList.contains('show') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                toggleDesktopSidebar();
            }
        });

        backdrop.addEventListener('click', closeMobileSidebar);

        window.addEventListener('resize', () => {
            if (!isMobile()) {
                // Al pasar a desktop, limpiar estado mobile
                sidebar.classList.remove('show');
                backdrop.classList.remove('active');
                // Restaurar preferencia guardada
                const collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                sidebar.classList.toggle('hidden', collapsed);
                if (mainContent) mainContent.classList.toggle('expanded', collapsed);
            } else {
                // Al pasar a mobile, limpiar estado desktop
                sidebar.classList.remove('hidden');
                if (mainContent) mainContent.classList.remove('expanded');
            }
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
