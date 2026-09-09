document.addEventListener('DOMContentLoaded', function () {

    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.site-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('submit', function (event) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var password = form.querySelector('input[name="password"]');
            var confirmPassword = form.querySelector('input[name="confirm_password"]');

            if (password && password.value.length < 6) {
                event.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }

            if (confirmPassword && password.value !== confirmPassword.value) {
                event.preventDefault();
                alert('The two passwords do not match.');
            }
        });
    });

    var avatarMenu = document.getElementById('avatarMenu');
    if (avatarMenu) {
        var avatarBtn = avatarMenu.querySelector('.avatar-btn');
        avatarBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            var open = avatarMenu.classList.toggle('open');
            avatarBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (event) {
            if (!avatarMenu.contains(event.target)) {
                avatarMenu.classList.remove('open');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                avatarMenu.classList.remove('open');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function closeRowMenus() {
        document.querySelectorAll('.row-menu.open').forEach(function (m) { m.classList.remove('open'); });
    }
    document.querySelectorAll('.row-menu').forEach(function (menu) {
        var btn = menu.querySelector('.row-menu-btn');
        var list = menu.querySelector('.row-menu-list');
        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            var wasOpen = menu.classList.contains('open');
            closeRowMenus();
            if (wasOpen) { return; }
            menu.classList.add('open');
            var r = btn.getBoundingClientRect();
            list.style.top = (r.bottom + 6) + 'px';
            list.style.left = (r.right - list.offsetWidth) + 'px';
        });
    });
    document.addEventListener('click', closeRowMenus);
    window.addEventListener('scroll', closeRowMenus, true);

    document.querySelectorAll('.check-all').forEach(function (master) {
        var table = master.closest('table');
        master.addEventListener('change', function () {
            table.querySelectorAll('.row-check').forEach(function (box) {
                box.checked = master.checked;
            });
        });
    });

    var alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity .4s ease';
            alertBox.style.opacity = '0';
            setTimeout(function () { alertBox.remove(); }, 400);
        }, 4500);
    }
});
