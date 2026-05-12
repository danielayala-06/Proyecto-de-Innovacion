<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── RESPONSIVE SIDEBAR ──
    (function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const toggleBtn = document.getElementById('toggleSidebar');
        const isMobile = () => window.innerWidth < 768;

        function closeSidebar() {
            if (isMobile()) {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }
        }

        function openSidebar() {
            if (isMobile()) {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
            }
        }

        function handleResize() {
            if (!isMobile()) {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }
        }

        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isMobile()) {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            }
        });

        backdrop.addEventListener('click', closeSidebar);

        window.addEventListener('resize', handleResize);
        window.addEventListener('orientationchange', handleResize);
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
