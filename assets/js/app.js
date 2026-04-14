/**
 * ClinicAll v2 — Global JavaScript
 */

'use strict';

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
