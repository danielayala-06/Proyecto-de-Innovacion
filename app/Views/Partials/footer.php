</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    document.getElementById('toggleSidebar').addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('expanded');
});

    function setActive(el) {
        document.querySelectorAll('.nav-link').forEach(a => a.classList.remove('active'));
        a.addEventListener("click",function (e){
            a.classList().add('active');
        })
        //el.classList.add('active');
    }
</script>
</body>
</html>
