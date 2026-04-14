/**
 * ClinicAll v2 — Global JavaScript
 */

'use strict';

var ClinicAllTheme = (function () {
    var STORAGE_KEY = 'clinicall-theme';
    var DEFAULT_THEME = 'light';
    var THEMES = ['light', 'dark'];

    function getStoredTheme() {
        try {
            var stored = localStorage.getItem(STORAGE_KEY);
            return THEMES.indexOf(stored) !== -1 ? stored : DEFAULT_THEME;
        } catch (e) {
            return DEFAULT_THEME;
        }
    }

    function applyTheme(theme) {
        var safeTheme = THEMES.indexOf(theme) !== -1 ? theme : DEFAULT_THEME;
        document.documentElement.setAttribute('data-theme', safeTheme);

        document.querySelectorAll('[data-theme-label]').forEach(function (label) {
            label.textContent = safeTheme === 'dark' ? 'Dark' : 'Light';
        });

        document.querySelectorAll('[data-theme-icon]').forEach(function (icon) {
            icon.classList.remove('fa-moon', 'fa-sun');
            icon.classList.add(safeTheme === 'dark' ? 'fa-sun' : 'fa-moon');
        });

        document.querySelectorAll('[data-theme-toggle]').forEach(function (toggle) {
            toggle.setAttribute('aria-pressed', safeTheme === 'dark' ? 'true' : 'false');
            toggle.setAttribute('title', safeTheme === 'dark' ? 'Switch to Light theme' : 'Switch to Dark theme');
        });

        try {
            localStorage.setItem(STORAGE_KEY, safeTheme);
        } catch (e) {
            // Ignore storage errors
        }
    }

    function toggleTheme() {
        applyTheme(getStoredTheme() === 'dark' ? 'light' : 'dark');
    }

    function initSidebarToggle() {
        var body = document.body;
        var toggles = document.querySelectorAll('[data-sidebar-toggle]');
        var sidebar = document.getElementById('classic-sidebar');

        if (!toggles.length || !sidebar) {
            return;
        }

        function syncState(isOpen) {
            body.classList.toggle('sidebar-open', isOpen);
            toggles.forEach(function (toggle) {
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                syncState(!body.classList.contains('sidebar-open'));
            });
        });

        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    syncState(false);
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (window.innerWidth >= 992 || !body.classList.contains('sidebar-open')) {
                return;
            }

            var clickedInsideSidebar = sidebar.contains(event.target);
            var clickedToggle = Array.prototype.some.call(toggles, function (toggle) {
                return toggle.contains(event.target);
            });

            if (!clickedInsideSidebar && !clickedToggle) {
                syncState(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                syncState(false);
            }
        });
    }

    function initProgressiveLoad() {
        var page = document.querySelector('.marketing-page');
        if (!page) {
            return;
        }

        page.classList.add('progressive-load');

        var skeletons = document.querySelectorAll('.progressive-load-skeleton');
        skeletons.forEach(function (el, index) {
            window.setTimeout(function () {
                el.classList.add('progressive-load-hidden');
            }, 220 + index * 120);
        });

        window.setTimeout(function () {
            page.classList.add('progressive-load-ready');
        }, 260);

        var reveals = document.querySelectorAll('.reveal-on-scroll');
        if (!reveals.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            reveals.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.14,
            rootMargin: '0px 0px -40px 0px'
        });

        reveals.forEach(function (el) {
            observer.observe(el);
        });
    }

    function init() {
        applyTheme(getStoredTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                toggleTheme();
            });
        });

        initSidebarToggle();
        initProgressiveLoad();
    }

    return {
        init: init,
        applyTheme: applyTheme
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    ClinicAllTheme.init();
});

// ── Auto-dismiss flash alerts ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert.alert-success').forEach(function (el) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) { bsAlert.close(); }
        }, 5000);
    });
});

// ── Confirm before dangerous forms ────────────────────────────────────────
document.addEventListener('submit', function (e) {
    var form = e.target;
    var msg  = form.dataset.confirmMsg;
    if (msg && !confirm(msg)) {
        e.preventDefault();
    }
});

// ── Check-all / check-one helpers (used by list pages) ────────────────────
window.listCheckAll = function (master) {
    document.querySelectorAll('.row-chk').forEach(function (c) {
        c.checked = master.checked;
    });
};

// ── Tooltip init ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var tips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tips.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});

// ── Date field: set today as default if empty ──────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[type="date"][data-default-today]').forEach(function (el) {
        if (!el.value) { el.value = today; }
    });
});
