        </div>
    </div>
</div>

<script>
    (function () {
        var t = document.querySelector('.dash-menu-toggle');
        var s = document.getElementById('dashSidebar');
        var shell = document.querySelector('.dash-shell');
        if (t && s && shell) {
            t.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 860px)').matches) {
                    s.classList.toggle('open');
                } else {
                    shell.classList.toggle('nav-collapsed');
                }
            });
        }
    })();
</script>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
