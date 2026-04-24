/**
 * Fashionista University - Main JavaScript
 * Vanilla JS, no external dependencies beyond Bootstrap
 */

'use strict';

/* ============================================================
   Like / Unlike - AJAX
   ============================================================ */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-like');
  if (!btn) return;

  const postId = btn.dataset.postId;
  const counter = btn.querySelector('.like-count');

  fetch('controllers/like_controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId)
      + '&csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf"]')?.content || '')
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        btn.classList.toggle('liked', data.liked);
        const icon = btn.querySelector('i');
        if (icon) {
          icon.className = data.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
        }
        if (counter) counter.textContent = data.count;
      } else if (data.redirect) {
        window.location.href = data.redirect;
      }
    })
    .catch(() => console.warn('Like request failed'));
});

/* ============================================================
   Toggle comment section
   ============================================================ */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-comment-toggle');
  if (!btn) return;
  const section = document.querySelector('.comment-section[data-post-id="' + btn.dataset.postId + '"]');
  if (section) {
    section.classList.toggle('open');
    if (section.classList.contains('open')) {
      section.querySelector('.comment-input')?.focus();
    }
  }
});

/* ============================================================
   Submit comment - AJAX
   ============================================================ */
document.addEventListener('submit', function (e) {
  const form = e.target.closest('.comment-form');
  if (!form) return;
  e.preventDefault();

  const postId = form.dataset.postId;
  const input = form.querySelector('.comment-input');
  const submitBtn = form.querySelector('.btn-submit-comment');
  const content = input.value.trim();
  if (!content) return;

  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
  if (submitBtn) submitBtn.disabled = true;

  fetch('controllers/comment_controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId)
      + '&content=' + encodeURIComponent(content)
      + '&csrf_token=' + encodeURIComponent(csrf)
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const section = form.closest('.comment-section');
        const list = section?.querySelector('.comment-list') || document.getElementById('commentList');
        const emptyState = list?.querySelector('p.text-muted');
        const html = `
        <div class="comment-item">
          <img src="${escHtml(data.avatar)}" class="comment-avatar" alt="">
          <div class="comment-bubble">
            <span class="comment-author">${escHtml(data.username)}</span>
            <span class="ms-2">${escHtml(data.content)}</span>
            <div class="text-muted" style="font-size:.72rem">a l'instant</div>
          </div>
        </div>`;

        if (emptyState) emptyState.remove();
        if (list) {
          list.insertAdjacentHTML('beforeend', html);
          list.scrollTop = list.scrollHeight;
        }

        input.value = '';

        document.querySelectorAll('.comment-count[data-post-id="' + postId + '"]').forEach(countEl => {
          countEl.textContent = parseInt(countEl.textContent || 0, 10) + 1;
        });

        const detailCountEl = document.querySelector('#commentList')?.closest('.card-body')
          ?.querySelector('.bi-chat + span');
        if (detailCountEl) {
          detailCountEl.textContent = parseInt(detailCountEl.textContent || 0, 10) + 1;
        }
      } else if (data.redirect) {
        window.location.href = data.redirect;
      } else if (data.message) {
        console.warn(data.message);
      }
    })
    .catch(() => console.warn('Comment request failed'))
    .finally(() => {
      if (submitBtn) submitBtn.disabled = false;
    });
});

/* ============================================================
   Image preview on file input
   ============================================================ */
const imageInput = document.getElementById('imageInput');
if (imageInput) {
  imageInput.addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('imagePreview');
    if (!preview) return;
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      preview.style.display = 'none';
    }
  });
}

/* ============================================================
   Participate in event - AJAX
   ============================================================ */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-participate');
  if (!btn) return;

  const eventId = btn.dataset.eventId;
  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';

  btn.disabled = true;

  fetch('controllers/event_controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=participate&event_id=' + encodeURIComponent(eventId)
      + '&csrf_token=' + encodeURIComponent(csrf)
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (data.participating) {
          btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Inscrit(e) !';
          btn.classList.replace('btn-fashion', 'btn-success');
        } else {
          btn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Participer';
          btn.classList.replace('btn-success', 'btn-fashion');
        }
        const countEl = document.querySelector('.participants-count[data-event-id="' + eventId + '"]');
        if (countEl && data.count !== undefined) countEl.textContent = data.count;
      } else if (data.redirect) {
        window.location.href = data.redirect;
      }
      btn.disabled = false;
    })
    .catch(() => { btn.disabled = false; });
});

/* ============================================================
   Mark all notifications as read
   ============================================================ */
const markAllBtn = document.getElementById('markAllRead');
if (markAllBtn) {
  markAllBtn.addEventListener('click', function () {
    fetch('controllers/notification_controller.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=mark_all_read'
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
          document.querySelectorAll('.notif-dot').forEach(el => el.remove());
          const badge = document.querySelector('.badge-notif');
          if (badge) badge.remove();
        }
      });
  });
}

/* ============================================================
   Confirm delete actions
   ============================================================ */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  if (!confirm(btn.dataset.confirm)) {
    e.preventDefault();
  }
});

/* ============================================================
   Auto-dismiss alerts after 4s
   ============================================================ */
document.querySelectorAll('.alert.alert-dismissible').forEach(el => {
  setTimeout(() => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    bsAlert?.close();
  }, 4000);
});

/* ============================================================
   Utility: HTML escape
   ============================================================ */
function escHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML;
}
