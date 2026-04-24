/**
 * Fashionista University — Admin Panel JavaScript
 */

'use strict';

/* ── Sidebar toggle (mobile) ─────────────────────────── */
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar       = document.getElementById('adminSidebar');

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  // Close sidebar on outside click (mobile)
  document.addEventListener('click', (e) => {
    if (window.innerWidth < 992
        && !sidebar.contains(e.target)
        && e.target !== sidebarToggle
        && !sidebarToggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}

/* ── Table search filter ─────────────────────────────── */
const tableSearch = document.getElementById('tableSearch');
if (tableSearch) {
  tableSearch.addEventListener('input', function () {
    const q    = this.value.toLowerCase();
    const rows = document.querySelectorAll('.admin-table tbody tr');
    rows.forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

/* ── Confirm delete buttons ──────────────────────────── */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  if (!confirm(btn.dataset.confirm)) {
    e.preventDefault();
    e.stopPropagation();
  }
});

/* ── Auto-dismiss alerts ─────────────────────────────── */
document.querySelectorAll('.alert.alert-dismissible').forEach(el => {
  setTimeout(() => bootstrap.Alert.getOrCreateInstance(el)?.close(), 4000);
});

/* ── Toggle user ban AJAX ────────────────────────────── */
document.querySelectorAll('.btn-toggle-ban').forEach(btn => {
  btn.addEventListener('click', function () {
    const userId  = this.dataset.userId;
    const action  = this.dataset.action;
    const csrf    = document.querySelector('meta[name="csrf"]')?.content || '';

    fetch('/fashionista/admin/user_action.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    'action=' + encodeURIComponent(action)
             + '&user_id=' + encodeURIComponent(userId)
             + '&csrf_token=' + encodeURIComponent(csrf)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) location.reload();
    });
  });
});

/* ── Stat card counter animation ─────────────────────── */
document.querySelectorAll('.admin-stat-value[data-target]').forEach(el => {
  const target   = parseInt(el.dataset.target, 10);
  const duration = 800;
  const step     = Math.ceil(target / (duration / 16));
  let current    = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) { current = target; clearInterval(timer); }
    el.textContent = current.toLocaleString('fr-FR');
  }, 16);
});
