<?php
// ============================================================
//  index.php — Homepage & Main Feed
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Event.php';

$pageTitle  = 'Accueil';
$activePage = 'feed';

// Pagination
$page     = max(1, (int)($_GET['page'] ?? 1));
$postModel = new Post();
$total    = $postModel->countAll();
$posts    = $postModel->getFeed($page, POSTS_PER_PAGE);
$pages    = (int)ceil($total / POSTS_PER_PAGE);

// Category filter (client-side, but we pass active filter)
$catFilter = $_GET['cat'] ?? '';

// Upcoming events (sidebar)
$eventModel    = new Event();
$upcomingEvents = $eventModel->getUpcoming(3);

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!isLoggedIn()): ?>
<style>

    /* ============================================================
   Fashionista University — Main Stylesheet
   Design: Modern, Instagram-inspired, elegant
   ============================================================ */

/* ── CSS Custom Properties ─────────────────────────────────── */
:root {
  --clr-primary:     #c9184a;
  --clr-primary-dk:  #a01037;
  --clr-secondary:   #ff6b9d;
  --clr-accent:      #ffd166;
  --clr-dark:        #1a1a2e;
  --clr-dark-2:      #16213e;
  --clr-surface:     #ffffff;
  --clr-surface-2:   #f8f9fa;
  --clr-border:      #e9ecef;
  --clr-text:        #212529;
  --clr-muted:       #6c757d;

  --font-heading:    'Playfair Display', serif;
  --font-body:       'Inter', sans-serif;

  --radius-sm:  8px;
  --radius-md:  16px;
  --radius-lg:  24px;
  --radius-xl:  32px;

  --shadow-sm:  0 1px 4px rgba(0,0,0,.06);
  --shadow-md:  0 4px 20px rgba(0,0,0,.1);
  --shadow-lg:  0 8px 40px rgba(0,0,0,.15);

  --transition: .25s ease;
}

/* ── Reset & Base ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

body {
  font-family: var(--font-body);
  color: var(--clr-text);
  background: var(--clr-surface-2);
  min-height: 100vh;
  overflow-x: hidden;
}

h1, h2, h3, h4, h5 {
  font-family: var(--font-heading);
  font-weight: 700;
}

a { color: var(--clr-primary); text-decoration: none; }
a:hover { color: var(--clr-primary-dk); }

img { max-width: 100%; object-fit: cover; }

.container,
.container-fluid {
  width: 100%;
}

/* ── Navbar ────────────────────────────────────────────────── */
.navbar-fashion {
  background: rgba(255,255,255,.95);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);

  border-bottom: 1px solid var(--clr-border);
  padding: .75rem 0;
  box-shadow: var(--shadow-sm);
}

.navbar-brand {
  font-family: var(--font-heading);
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--clr-primary) !important;
  letter-spacing: -.5px;
}

.navbar-mobile-actions {
  display: flex;
  align-items: center;
  gap: .55rem;
  margin-left: auto;
}

.navbar-mobile-bell {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--clr-border);
  border-radius: 14px;
  background: #fff;
  color: var(--clr-text) !important;
  padding: 0 !important;
}

.navbar-fashion .nav-link {
  color: var(--clr-text) !important;
  font-weight: 500;
  font-size: .9rem;
  padding: .5rem .85rem;
  border-radius: var(--radius-sm);
  transition: background var(--transition), color var(--transition);
}

.navbar-fashion .nav-link:hover,
.navbar-fashion .nav-link.active {
  background: rgba(201,24,74,.08);
  color: var(--clr-primary) !important;
}

/* Search bar */
.search-input {
  border-radius: var(--radius-xl) 0 0 var(--radius-xl) !important;
  border-color: var(--clr-border);
  font-size: .85rem;
  width: 220px;
}
.btn-search {
  border-radius: 0 var(--radius-xl) var(--radius-xl) 0 !important;
  background: var(--clr-primary);
  color: #fff;
  border: 1px solid var(--clr-primary);
}
.btn-search:hover { background: var(--clr-primary-dk); }

/* Notification badge */
.badge-notif {
  position: absolute;
  top: 2px; right: 2px;
  background: var(--clr-primary);
  color: #fff;
  border-radius: 50%;
  font-size: .6rem;
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

/* Avatar in navbar */
.nav-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--clr-primary);
}

/* User menu button */
.user-menu { gap: .5rem; }

/* Dropdown */
.dropdown-fashion {
  border: none;
  box-shadow: var(--shadow-lg);
  border-radius: var(--radius-md);
  padding: .5rem;
  min-width: 200px;
}
.dropdown-fashion .dropdown-item {
  border-radius: var(--radius-sm);
  padding: .55rem 1rem;
  font-size: .9rem;
  transition: background var(--transition);
}
.dropdown-fashion .dropdown-item:hover {
  background: rgba(201,24,74,.07);
  color: var(--clr-primary);
}

/* ── Buttons ────────────────────────────────────────────────── */
.btn-fashion {
  background: linear-gradient(135deg, var(--clr-primary), var(--clr-secondary));
  color: #fff;
  border: none;
  border-radius: var(--radius-xl);
  font-weight: 600;
  padding: .5rem 1.4rem;
  transition: transform var(--transition), box-shadow var(--transition);
}
.btn-fashion:hover {
  color: #fff;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(201,24,74,.35);
}

.btn-outline-fashion {
  border: 2px solid var(--clr-primary);
  color: var(--clr-primary);
  background: transparent;
  border-radius: var(--radius-xl);
  font-weight: 600;
  padding: .45rem 1.4rem;
  transition: all var(--transition);
}
.btn-outline-fashion:hover {
  background: var(--clr-primary);
  color: #fff;
}

/* ── Main Content ───────────────────────────────────────────── */
.main-content { min-height: calc(100vh - 140px); }

/* ── Post Cards (Instagram style) ──────────────────────────── */
.post-card {
  background: var(--clr-surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--clr-border);
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition), box-shadow var(--transition);
  overflow: hidden;
}
.post-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.post-card-img-wrap {
  position: relative;
  overflow: hidden;
  aspect-ratio: 1 / 1;
  background: var(--clr-surface-2);
}
.post-card-img-wrap img {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform .4s ease;
}
.post-card:hover .post-card-img-wrap img { transform: scale(1.04); }

/* Hover overlay */
.post-overlay {
  position: absolute;
  inset: 0;
  background: rgba(26,26,46,.55);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  opacity: 0;
  transition: opacity var(--transition);
}
.post-card:hover .post-overlay { opacity: 1; }

.post-overlay-stat {
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-weight: 700;
}
.post-overlay-stat i { font-size: 1.4rem; }

.post-card-body { padding: 1rem; }

.post-card-author {
  display: flex;
  align-items: center;
  gap: .6rem;
  margin-bottom: .7rem;
}
.post-author-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--clr-primary);
}
.post-author-name {
  font-weight: 600;
  font-size: .9rem;
  color: var(--clr-text);
}
.post-time {
  font-size: .75rem;
  color: var(--clr-muted);
}

.post-title {
  font-family: var(--font-heading);
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: .4rem;
  color: var(--clr-text);
}
.post-desc {
  font-size: .85rem;
  color: var(--clr-muted);
  margin-bottom: .75rem;
}

.post-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-top: .65rem;
  border-top: 1px solid var(--clr-border);
}
.btn-like, .btn-comment-toggle {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: .35rem;
  font-size: .85rem;
  font-weight: 600;
  color: var(--clr-muted);
  padding: .3rem .5rem;
  border-radius: var(--radius-sm);
  transition: color var(--transition), background var(--transition);
}
.btn-like:hover, .btn-like.liked { color: var(--clr-primary); }
.btn-like.liked i::before { content: "\f415"; /* bi-heart-fill */ }
.btn-comment-toggle:hover { color: var(--clr-dark); background: var(--clr-surface-2); }

/* Comment section */
.comment-section {
  border-top: 1px solid var(--clr-border);
  padding: .75rem;
  background: var(--clr-surface-2);
  display: none;
}
.comment-section.open { display: block; }

.comment-item {
  display: flex;
  gap: .6rem;
  margin-bottom: .6rem;
}
.comment-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.comment-bubble {
  background: var(--clr-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--radius-sm);
  padding: .45rem .7rem;
  font-size: .82rem;
  flex: 1;
}
.comment-author { font-weight: 700; font-size: .8rem; color: var(--clr-primary); }

.comment-form-wrap {
  display: flex;
  gap: .5rem;
  margin-top: .6rem;
}
.comment-input {
  flex: 1;
  border-radius: var(--radius-xl);
  border: 1px solid var(--clr-border);
  padding: .4rem .9rem;
  font-size: .85rem;
}
.btn-submit-comment {
  background: var(--clr-primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-xl);
  padding: .4rem .9rem;
  font-size: .85rem;
  cursor: pointer;
  transition: background var(--transition);
}
.btn-submit-comment:hover { background: var(--clr-primary-dk); }

/* ── Category Badges ────────────────────────────────────────── */
.badge-pink       { background: #fce4ec; color: #c9184a; }
.badge-purple     { background: #f3e5f5; color: #7b1fa2; }
.badge-gold       { background: #fff8e1; color: #f57f17; }
.badge-dark       { background: #212529; color: #fff; }
.badge-rose       { background: #fce4ec; color: #ad1457; }
.badge-secondary  { background: #e9ecef; color: #495057; }

/* ── Event Cards ────────────────────────────────────────────── */
.event-card {
  background: var(--clr-surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--clr-border);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
}
.event-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}
.event-card-img {
  width: 100%; height: 180px;
  object-fit: cover;
}
.event-type-badge {
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  padding: .25rem .6rem;
  border-radius: var(--radius-xl);
}
.badge-event-defilé     { background:#fce4ec; color:#c9184a; }
.badge-event-concours   { background:#e8f5e9; color:#2e7d32; }
.badge-event-atelier    { background:#e3f2fd; color:#1565c0; }
.badge-event-exposition { background:#fff3e0; color:#e65100; }
.badge-event-autre      { background:#f3e5f5; color:#6a1b9a; }

.event-date-chip {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  font-size: .8rem;
  color: var(--clr-muted);
  font-weight: 500;
}

/* ── Hero section on homepage ───────────────────────────────── */
.hero-section {
  background: linear-gradient(135deg, var(--clr-dark) 0%, var(--clr-dark-2) 100%);
  color: #fff;
  padding: 5rem 0 4rem;
  text-align: center;
  margin-bottom: 2.5rem;
  position: relative;
  overflow: hidden;
}
.hero-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9184a' fill-opacity='0.07'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.hero-title {
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 700;
  letter-spacing: -1px;
  position: relative;
}
.hero-title span { color: var(--clr-secondary); }
.hero-subtitle {
  font-size: 1.1rem;
  color: rgba(255,255,255,.7);
  max-width: 500px;
  margin: 0 auto 2rem;
  position: relative;
}

/* ── Dashboard Stat Cards ───────────────────────────────────── */
.stat-card {
  background: var(--clr-surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--clr-border);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  gap: 1.2rem;
  transition: box-shadow var(--transition);
}
.stat-card:hover { box-shadow: var(--shadow-md); }
.stat-icon {
  width: 56px; height: 56px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.stat-icon-pink   { background: #fce4ec; color: var(--clr-primary); }
.stat-icon-purple { background: #f3e5f5; color: #7b1fa2; }
.stat-icon-blue   { background: #e3f2fd; color: #1565c0; }
.stat-icon-gold   { background: #fff8e1; color: #f57f17; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: .82rem; color: var(--clr-muted); font-weight: 500; }

/* ── Profile page ───────────────────────────────────────────── */
.profile-header {
  background: linear-gradient(135deg, var(--clr-dark) 0%, var(--clr-dark-2) 60%);
  border-radius: var(--radius-lg);
  padding: 2.5rem;
  color: #fff;
  margin-bottom: 2rem;
  position: relative;
  overflow: hidden;
}
.profile-avatar-wrap {
  position: relative;
  width: 120px; height: 120px;
}
.profile-avatar {
  width: 120px; height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--clr-primary);
  box-shadow: 0 0 0 4px rgba(201,24,74,.3);
}
.profile-name {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: .15rem;
}
.profile-username { color: var(--clr-secondary); font-weight: 500; }
.profile-bio { color: rgba(255,255,255,.8); margin-top: .75rem; max-width: 500px; }

.profile-avatar-col,
.profile-info-col,
.profile-stats-col {
  position: relative;
}

.profile-stats-row {
  width: 100%;
}

.profile-stat {
  text-align: center;
  padding: .5rem 1.5rem;
}
.profile-stat-value {
  font-size: 1.6rem;
  font-weight: 700;
}
.profile-stat-label {
  font-size: .8rem;
  color: rgba(255,255,255,.65);
  text-transform: uppercase;
  letter-spacing: .5px;
}

/* ── Auth forms ─────────────────────────────────────────────── */
.auth-card {
  background: var(--clr-surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--clr-border);
  box-shadow: var(--shadow-lg);
  padding: 2.5rem;
  max-width: 460px;
  margin: 0 auto;
}
.auth-title {
  font-size: 1.8rem;
  text-align: center;
  margin-bottom: .4rem;
}
.auth-subtitle {
  text-align: center;
  color: var(--clr-muted);
  font-size: .9rem;
  margin-bottom: 2rem;
}
.form-label { font-weight: 600; font-size: .88rem; color: var(--clr-text); }
.form-control {
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--clr-border);
  padding: .6rem .9rem;
  font-size: .9rem;
  transition: border-color var(--transition), box-shadow var(--transition);
}
.form-control:focus {
  border-color: var(--clr-primary);
  box-shadow: 0 0 0 3px rgba(201,24,74,.12);
  outline: none;
}
.form-select:focus {
  border-color: var(--clr-primary);
  box-shadow: 0 0 0 3px rgba(201,24,74,.12);
}

/* ── Post Detail page ───────────────────────────────────────── */
.post-detail-img {
  width: 100%;
  max-height: 550px;
  object-fit: cover;
  border-radius: var(--radius-md);
}

/* ── Section headings ───────────────────────────────────────── */
.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: var(--clr-text);
  display: flex;
  align-items: center;
  gap: .6rem;
}
.section-title::after {
  content: '';
  flex: 1;
  height: 2px;
  background: linear-gradient(to right, var(--clr-primary), transparent);
  border-radius: 2px;
}

/* ── Footer ─────────────────────────────────────────────────── */
.site-footer {
  background: var(--clr-dark);
  color: #fff;
  padding-top: 3rem;
}
.site-footer .text-muted { color: rgba(255,255,255,.7) !important; }
.footer-brand { font-family: var(--font-heading); color: var(--clr-secondary); font-size: 1.3rem; }
.footer-tagline { color: var(--clr-secondary); font-size: .85rem; font-weight: 500; }
.footer-heading { color: #fff; font-weight: 700; font-size: .9rem; text-transform: uppercase; letter-spacing: .6px; }
.footer-links { list-style: none; padding: 0; }
.footer-links li { margin-bottom: .4rem; }
.footer-links a { color: rgba(255,255,255,.6); font-size: .88rem; transition: color var(--transition); }
.footer-links a:hover { color: var(--clr-secondary); }
.footer-social { display: flex; gap: .75rem; }
.social-icon {
  width: 38px; height: 38px;
  background: rgba(255,255,255,.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1rem;
  transition: background var(--transition), transform var(--transition);
}
.social-icon:hover { background: var(--clr-primary); transform: translateY(-2px); color: #fff; }
.footer-divider { border-color: rgba(255,255,255,.1); }

/* ── Notifications page ─────────────────────────────────────── */
.notif-item {
  display: flex;
  align-items: flex-start;
  gap: .9rem;
  padding: .9rem 1rem;
  border-radius: var(--radius-sm);
  transition: background var(--transition);
  border-bottom: 1px solid var(--clr-border);
}
.notif-item:last-child { border-bottom: none; }
.notif-item.unread { background: rgba(201,24,74,.04); }
.notif-item:hover { background: var(--clr-surface-2); }
.notif-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.notif-text { flex: 1; font-size: .88rem; }
.notif-time { font-size: .76rem; color: var(--clr-muted); }
.notif-dot {
  width: 8px; height: 8px;
  background: var(--clr-primary);
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 6px;
}

/* ── Utility ─────────────────────────────────────────────────── */
.divider-text {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: var(--clr-muted);
  font-size: .82rem;
  margin: 1rem 0;
}
.divider-text::before, .divider-text::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--clr-border);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--clr-muted);
}
.empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: .4; }
.empty-state p { font-size: 1.05rem; }

/* ── Upload preview ─────────────────────────────────────────── */
#imagePreview {
  width: 100%; max-height: 300px;
  object-fit: cover;
  border-radius: var(--radius-md);
  display: none;
  margin-top: .7rem;
  border: 2px solid var(--clr-primary);
}

/* ── Pagination ─────────────────────────────────────────────── */
.page-link {
  color: var(--clr-primary);
  border-radius: var(--radius-sm) !important;
  margin: 0 2px;
}
.page-item.active .page-link {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
}

/* ── Responsive tweaks ──────────────────────────────────────── */
@media (max-width: 768px) {
  .search-form { display: none !important; }
  .profile-header { padding: 1.5rem; text-align: center; }
  .profile-avatar-wrap { margin: 0 auto; }
  .hero-section { padding: 3rem 0 2rem; }
  .auth-card { padding: 1.75rem 1.25rem; }
}

@media (max-width: 991.98px) {
  .navbar-fashion {
    padding: .65rem 0;
  }

  .navbar-fashion .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .navbar-fashion .navbar-collapse {
    margin-top: .9rem;
    padding: 1rem;
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-md);
    background: rgba(255,255,255,.98);
    box-shadow: var(--shadow-md);
  }

  .navbar-fashion .navbar-nav {
    align-items: stretch !important;
  }

  .navbar-fashion .nav-link,
  .navbar-fashion .btn,
  .navbar-fashion .dropdown-toggle {
    width: 100%;
    justify-content: flex-start;
  }

  .navbar-fashion .search-form .btn-search {
    width: 34px;
    justify-content: center;
  }

  .search-form {
    display: flex !important;
    width: 100%;
    margin: .35rem 0 .45rem !important;
  }

  .search-form .input-group {
    width: 100%;
    align-items: center;
    display: flex;
    flex-wrap: nowrap;
    gap: .3rem;
  }

  .search-form,
  .navbar-fashion .navbar-nav.align-items-center {
    padding-left: .15rem;
    padding-right: .15rem;
  }

  .navbar-fashion .navbar-nav.align-items-center .nav-item {
    width: 100%;
  }

  .navbar-fashion .navbar-nav.align-items-center .btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .search-input {
    width: 100%;
    min-width: 0;
    border-radius: 999px !important;
    border: 1.5px solid #e8a1b5;
    height: 34px;
    padding: .35rem .8rem;
    font-size: .75rem;
  }

  .dropdown-fashion {
    min-width: 100%;
  }

  .section-title {
    font-size: 1.3rem;
    align-items: flex-start;
  }

  .section-title::after {
    display: none;
  }

  .hero-section {
    margin-left: 0 !important;
    margin-right: 0 !important;
    border-radius: var(--radius-lg);
    padding: 4rem 1.5rem 3rem !important;
  }

  .profile-header {
    padding: 2rem;
  }

  .post-detail-img {
    max-height: 440px;
  }

  .sticky-top {
    position: static !important;
    top: auto !important;
  }

  .btn-search {
    border-radius: 999px !important;
    width: 34px;
    min-width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    box-shadow: 0 6px 16px rgba(201,24,74,.18);
  }

  .navbar-fashion .navbar-nav.align-items-center > .nav-item + .nav-item {
    margin-top: .45rem;
  }
}

@media (max-width: 768px) {
  .main-content > .container,
  .site-footer .container,
  .navbar-fashion .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .profile-header {
    padding: 1.5rem;
    text-align: center;
  }

  .profile-header-row {
    justify-content: center;
  }

  .profile-avatar-wrap {
    margin: 0 auto;
  }

  .profile-info-col {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .profile-bio {
    margin-left: auto;
    margin-right: auto;
  }

  .profile-stats-row {
    justify-content: center !important;
  }

  .hero-section {
    padding: 3rem 1.25rem 2.25rem !important;
  }

  .hero-subtitle {
    font-size: .98rem;
  }

  .auth-card {
    padding: 1.75rem 1.25rem;
  }

  .post-card-body,
  .comment-section,
  .card-body.p-4,
  .card-body.p-md-5 {
    padding: 1rem !important;
  }

  .post-actions {
    gap: .5rem;
    flex-wrap: wrap;
  }

  .post-detail-actions {
    gap: .75rem !important;
    flex-wrap: wrap;
  }

  .post-detail-owner-actions {
    width: 100%;
    margin-left: 0 !important;
  }

  .post-detail-delete-btn {
    width: 100%;
    justify-content: center;
  }

  .btn-like,
  .btn-comment-toggle {
    flex: 1 1 auto;
    justify-content: center;
  }

  .comment-form-wrap {
    align-items: center;
  }

  .comment-input {
    min-width: 0;
  }

  .stat-card {
    padding: 1rem;
    gap: .85rem;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .empty-state {
    padding: 2.5rem 1rem;
  }

  .empty-state p {
    font-size: .95rem;
  }

  .notif-item {
    padding: .85rem 0;
    gap: .75rem;
  }

  .page-link {
    padding: .4rem .7rem;
  }

  .footer-social {
    flex-wrap: wrap;
  }

  .navbar-mobile-bell {
    width: 38px;
    height: 38px;
    border-radius: 12px;
  }
}

@media (max-width: 575.98px) {
  .navbar-brand {
    font-size: 1.15rem;
    max-width: calc(100% - 110px);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .navbar-toggler {
    padding: .35rem .55rem;
    border-radius: var(--radius-sm);
  }

  .hero-title {
    font-size: 1.85rem;
    line-height: 1.15;
  }

  .d-flex.gap-2,
  .d-flex.gap-3 {
    row-gap: .65rem;
  }

  .btn-fashion,
  .btn-outline-fashion,
  .btn-outline-light,
  .btn-search,
  .btn-submit-comment {
    min-height: 42px;
  }

  .comment-item {
    gap: .45rem;
  }

  .comment-bubble {
    padding: .45rem .6rem;
    font-size: .78rem;
  }

  .comment-form-wrap {
    gap: .4rem;
  }

  .comment-form-wrap img {
    width: 30px !important;
    height: 30px !important;
  }

  .btn-submit-comment {
    padding: .4rem .8rem;
    flex-shrink: 0;
  }

  .post-detail-img {
    max-height: 300px;
  }

  .profile-header {
    border-radius: var(--radius-md);
  }

  .profile-avatar-wrap,
  .profile-avatar {
    width: 92px;
    height: 92px;
  }

  .profile-name {
    font-size: 1.45rem;
  }

  .profile-stat {
    flex: 1 1 0;
    min-width: 0;
    padding: .35rem .25rem;
  }

  .profile-stats-row {
    gap: 0 !important;
    align-items: stretch;
  }

  .profile-stat-value {
    font-size: 1.3rem;
  }

  .profile-stat-label {
    font-size: .68rem;
    letter-spacing: .35px;
  }

  .auth-card {
    border-radius: var(--radius-md);
    padding: 1.25rem 1rem;
  }

  .section-title {
    font-size: 1.15rem;
    margin-bottom: 1rem;
  }

  .badge.rounded-pill {
    font-size: .78rem !important;
    padding: .55rem .85rem !important;
  }

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
  }

  .site-footer {
    padding-top: 2.25rem;
  }

  .site-footer .d-flex.justify-content-between {
    gap: .5rem;
    align-items: flex-start !important;
  }

  .navbar-mobile-actions {
    gap: .45rem;
  }
}


</style>
<!-- ── Hero (guests only) ─────────────────────────────────── -->
<div class="hero-section" style="margin:-1.5rem -12px 2rem; padding: 5rem 2rem;">
  <h1 class="hero-title">La mode universitaire,<br><span>réinventée</span></h1>
  <p class="hero-subtitle">Publiez vos créations, découvrez les talents de votre campus et participez aux événements.</p>
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="<?= SITE_URL ?>/register.php" class="btn btn-fashion btn-lg">
      <i class="bi bi-stars me-2"></i>Rejoindre la communauté
    </a>
    <a href="<?= SITE_URL ?>/events.php" class="btn btn-outline-light btn-lg border-2">
      <i class="bi bi-calendar-event me-2"></i>Voir les événements
    </a>
  </div>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── Main feed ─────────────────────────────────────────── -->
  <div class="col-lg-8">

    <!-- Feed header & filters -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
      <h2 class="section-title mb-0">
        <i class="bi bi-grid-3x3-gap text-danger me-2"></i>Créations
      </h2>
      <!-- Category pills -->
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= SITE_URL ?>/index.php"
           class="badge rounded-pill <?= $catFilter === '' ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2">
          Tout
        </a>
        <?php foreach (['robe','streetwear','accessoire','haute_couture','costume','autre'] as $cat): ?>
          <a href="<?= SITE_URL ?>/index.php?cat=<?= $cat ?>"
             class="badge rounded-pill <?= $catFilter === $cat ? 'bg-danger' : 'bg-light text-dark border' ?> text-decoration-none px-3 py-2">
            <?= ucfirst(str_replace('_', ' ', $cat)) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (empty($posts)): ?>
      <div class="empty-state">
        <i class="bi bi-images d-block"></i>
        <p>Aucune création publiée pour le moment.</p>
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-fashion mt-2">
            <i class="bi bi-plus-circle me-2"></i>Publier la première
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 g-4" id="postGrid">
        <?php foreach ($posts as $post):
          // Client-side filter: hide non-matching (JS handles it too, but PHP pre-filters)
          if ($catFilter && $post['category'] !== $catFilter) continue;
        ?>
          <div class="col">
            <?php require __DIR__ . '/views/post_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>&cat=<?= h($catFilter) ?>">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
            <?php for ($p = 1; $p <= $pages; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&cat=<?= h($catFilter) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>&cat=<?= h($catFilter) ?>">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>

  </div><!-- /.col-lg-8 -->

  <!-- ── Sidebar ───────────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- CTA card for guests -->
    <?php if (!isLoggedIn()): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4"
           style="background: linear-gradient(135deg,#1a1a2e,#16213e); color:#fff;">
        <div class="card-body p-4 text-center">
          <i class="bi bi-person-plus fs-1 mb-2 d-block" style="color:#ff6b9d"></i>
          <h5 class="fw-bold">Rejoignez Fashionista</h5>
          <p class="small opacity-75 mb-3">Publiez vos créations et connectez-vous avec d'autres étudiants créateurs.</p>
          <a href="<?= SITE_URL ?>/register.php" class="btn btn-fashion w-100 mb-2">S'inscrire</a>
          <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light w-100">Se connecter</a>
        </div>
      </div>
    <?php else: ?>
      <!-- Quick publish CTA -->
      <div class="card border-0 shadow-sm rounded-4 mb-4"
           style="background: linear-gradient(135deg,#c9184a,#ff6b9d); color:#fff;">
        <div class="card-body p-4 text-center">
          <i class="bi bi-camera fs-1 mb-2 d-block"></i>
          <h5 class="fw-bold">Partagez votre création</h5>
          <p class="small opacity-80 mb-3">Montrez votre talent à toute la communauté.</p>
          <a href="<?= SITE_URL ?>/create_post.php" class="btn btn-light fw-bold w-100">
            <i class="bi bi-plus-circle me-2"></i>Publier une création
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Upcoming events -->
    <?php if (!empty($upcomingEvents)): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
          <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-calendar-event text-danger"></i>
            Événements à venir
          </h6>
          <?php foreach ($upcomingEvents as $ev): ?>
            <a href="<?= SITE_URL ?>/event_detail.php?id=<?= (int)$ev['id'] ?>"
               class="d-flex gap-3 align-items-start p-2 rounded-3 hover-bg text-decoration-none text-dark mb-2"
               style="transition:.2s">
              <!-- Date block -->
              <div class="text-center rounded-3 p-2 flex-shrink-0"
                   style="background:#fce4ec; min-width:48px;">
                <div style="font-size:1.2rem; font-weight:800; color:#c9184a; line-height:1">
                  <?= date('d', strtotime($ev['event_date'])) ?>
                </div>
                <div style="font-size:.65rem; font-weight:700; color:#c9184a; text-transform:uppercase">
                  <?= date('M', strtotime($ev['event_date'])) ?>
                </div>
              </div>
              <div>
                <div class="fw-semibold" style="font-size:.88rem"><?= h(truncate($ev['title'], 40)) ?></div>
                <div class="text-muted" style="font-size:.76rem">
                  <i class="bi bi-geo-alt me-1"></i><?= h(truncate($ev['location'] ?? '', 30)) ?>
                </div>
                <div style="font-size:.72rem">
                  <span class="badge badge-event-<?= h($ev['type']) ?> mt-1">
                    <?= h($ev['type']) ?>
                  </span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
          <a href="<?= SITE_URL ?>/events.php"
             class="btn btn-outline-danger btn-sm w-100 mt-2 rounded-pill">
            Tous les événements →
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Stats card -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart text-danger me-2"></i>La plateforme</h6>
        <div class="row g-2 text-center">
          <?php
            $db        = getDB();
            $nPosts    = $db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
            $nUsers    = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $nEvents   = $db->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn();
          ?>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nPosts ?></div>
            <div class="text-muted" style="font-size:.72rem">Créations</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nUsers ?></div>
            <div class="text-muted" style="font-size:.72rem">Étudiants</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5 text-danger"><?= $nEvents ?></div>
            <div class="text-muted" style="font-size:.72rem">Événements</div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.col-lg-4 -->
</div><!-- /.row -->

<!-- CSRF meta tag for JS -->
<meta name="csrf" content="<?= h(csrfToken()) ?>">

<?php require_once __DIR__ . '/includes/footer.php'; ?>
