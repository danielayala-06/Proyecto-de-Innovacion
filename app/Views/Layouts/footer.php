<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('hidden');
      document.getElementById('main-content').classList.toggle('expanded');
    });

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
