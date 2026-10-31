/* app.js — countdowns, AJAX bidding, toasts, user menu, watchlist, image delete,
 * PJAX sidebar navigation (no full page refresh on sidebar clicks).
 * Vanilla JS + Fetch API. No frameworks. */

(function () {
  'use strict';

  var APP_BASE = (window.APP_BASE || '').replace(/\/$/, '');

  // ---- Toast helper ----
  function toast(message, type) {
    var container = document.getElementById('toast');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'toast ' + (type || 'info') + ' animate-scale-in';
    el.textContent = message;
    container.appendChild(el);
    setTimeout(function () {
      el.style.opacity = '0';
      el.style.transform = 'translateY(-8px)';
      el.style.transition = 'opacity .3s, transform .3s';
      setTimeout(function () { el.remove(); }, 300);
    }, 4000);
  }
  window.toast = toast;

  // ---- Centered confirm modal (replaces native confirm(), dark chrome) ----
  window.appConfirm = function (message, opts) {
    opts = opts || {};
    return new Promise(function (resolve) {
      var overlay = document.createElement('div');
      overlay.className = 'fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4';
      overlay.innerHTML =
        '<div class="w-full max-w-sm rounded-xl border border-white/10 bg-[#1c1c22] p-5 shadow-2xl shadow-black/60 animate-scale-in">' +
          '<div class="flex items-start gap-3">' +
            '<span class="w-9 h-9 shrink-0 rounded-lg bg-red-500/15 text-red-400 flex items-center justify-center">' +
              '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>' +
            '</span>' +
            '<p class="pt-1.5 text-sm text-zinc-200 leading-relaxed">' + escapeHtml(message) + '</p>' +
          '</div>' +
          '<div class="mt-5 flex justify-end gap-2">' +
            '<button type="button" data-cf="no" class="rounded-lg border border-white/10 px-3.5 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">' + escapeHtml(opts.cancelText || 'Cancel') + '</button>' +
            '<button type="button" data-cf="yes" class="rounded-lg bg-auction px-3.5 py-2 text-sm font-semibold text-white hover:bg-auction-dark transition">' + escapeHtml(opts.confirmText || 'Confirm') + '</button>' +
          '</div>' +
        '</div>';
      function done(v) {
        overlay.remove();
        document.removeEventListener('keydown', onKey);
        resolve(v);
      }
      function onKey(e) { if (e.key === 'Escape') done(false); }
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) { done(false); return; }
        var b = e.target.closest('[data-cf]');
        if (b) done(b.getAttribute('data-cf') === 'yes');
      });
      document.addEventListener('keydown', onKey);
      document.body.appendChild(overlay);
      overlay.querySelector('[data-cf="no"]').focus();
    });
  };

  // ---- SweetAlert-style centered alert (flash messages, notices) ----
  window.appAlert = function (message, type) {
    var meta = ({
      success: ['Success', 'bg-emerald-500/15 text-emerald-400 border-emerald-400/30', 'M5 13l4 4L19 7'],
      error:   ['Error',   'bg-red-500/15 text-red-400 border-red-400/30',           'M6 18L18 6M6 6l12 12'],
      info:    ['Notice',  'bg-blue-500/15 text-blue-400 border-blue-400/30',        'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']
    })[type || 'info'] || ['Notice', 'bg-blue-500/15 text-blue-400 border-blue-400/30', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];

    return new Promise(function (resolve) {
      var overlay = document.createElement('div');
      overlay.className = 'fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4';
      overlay.innerHTML =
        '<div class="w-full max-w-sm rounded-xl border border-white/10 bg-[#1c1c22] p-6 text-center shadow-2xl shadow-black/60 animate-scale-in">' +
          '<span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border ' + meta[1] + '">' +
            '<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="' + meta[2] + '"/></svg>' +
          '</span>' +
          '<h3 class="mt-4 text-lg font-semibold text-white">' + meta[0] + '</h3>' +
          '<p class="mt-1.5 text-sm text-zinc-400 leading-relaxed">' + escapeHtml(message) + '</p>' +
          '<button type="button" data-ok class="mt-5 w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">OK</button>' +
        '</div>';
      function done() {
        overlay.remove();
        document.removeEventListener('keydown', onKey);
        resolve();
      }
      function onKey(e) { if (e.key === 'Escape' || e.key === 'Enter') done(); }
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay || e.target.closest('[data-ok]')) done();
      });
      document.addEventListener('keydown', onKey);
      document.body.appendChild(overlay);
      overlay.querySelector('[data-ok]').focus();
    });
  };

  // Upgrade server-rendered .flash-slide banners into the centered modal.
  // Accepts either live DOM elements or nodes parsed from a PJAX response.
  function flashToModal(nodes) {
    var list = (nodes && nodes.length)
      ? Array.prototype.slice.call(nodes)
      : Array.prototype.slice.call(document.querySelectorAll('.flash-slide'));
    var chain = Promise.resolve();
    list.forEach(function (f) {
      var msg = (f.textContent || '').replace(/\s+/g, ' ').trim();
      var isErr = /bg-red-50|border-red-200/.test(f.className || '');
      if (f.remove) f.remove();
      if (!msg) return;
      chain = chain.then(function () { return appAlert(msg, isErr ? 'error' : 'success'); });
    });
  }

  // ---- HTML escape for AJAX-injected content (defense in depth vs XSS) ----
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // ---- Password visibility toggle ----
  window.togglePassword = function (inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    if (btn) btn.classList.toggle('text-brand');
  };

  // ---- User menu toggle ----
  window.toggleUserMenu = function () {
    var dd = document.getElementById('userMenuDropdown');
    if (dd) dd.classList.toggle('hidden');
  };
  document.addEventListener('click', function (e) {
    var menu = document.getElementById('userMenu');
    var dd = document.getElementById('userMenuDropdown');
    if (dd && menu && !menu.contains(e.target)) dd.classList.add('hidden');
  });

  // ---- Sidebar toggle (mobile) + collapse (desktop) ----
  // Uses event delegation via [data-toggle] attributes (CSP compliant — no
  // inline onclick). Desktop collapse state is persisted in localStorage so
  // it survives PJAX navigation and page reloads.

  var SIDEBAR_KEY = 'car_sidebar_collapsed';

  // Apply the collapsed class + icon rotation based on stored state.
  function applySidebarState() {
    var collapsed = localStorage.getItem(SIDEBAR_KEY) === '1';
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    var icon = document.getElementById('collapseIcon');
    if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : '';
  }

  // Toggle desktop collapse (persisted)
  function toggleCollapseSidebar() {
    var collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0');
    var icon = document.getElementById('collapseIcon');
    if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : '';
  }

  // Toggle mobile slide-in sidebar (not persisted)
  function toggleMobileSidebar() {
    var sb = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (sb) sb.classList.toggle('-translate-x-full');
    if (ov) ov.classList.toggle('hidden');
  }

  // Event delegation for all sidebar toggle buttons
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-toggle]');
    if (!btn) return;
    var action = btn.getAttribute('data-toggle');
    if (action === 'collapse-sidebar') {
      e.preventDefault();
      toggleCollapseSidebar();
    } else if (action === 'mobile-sidebar') {
      e.preventDefault();
      toggleMobileSidebar();
    } else if (action === 'guest-menu') {
      e.preventDefault();
      var gm = document.getElementById('guestMenu');
      if (gm) gm.classList.toggle('hidden');
    }
  });

  // Restore collapsed state on load
  applySidebarState();

  // ---- Site header: subtle shadow once the page is scrolled ----
  var siteHeader = document.getElementById('siteHeader');
  function updateHeaderState() {
    if (!siteHeader) return;
    siteHeader.classList.toggle('is-scrolled', window.scrollY > 8);
  }
  window.addEventListener('scroll', updateHeaderState, { passive: true });
  updateHeaderState();

  // "/" focuses the sidebar search (Linear-style shortcut)
  document.addEventListener('keydown', function (e) {
    if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
    if (e.target.closest('input, textarea, select, [contenteditable="true"]')) return;
    var sb = document.getElementById('sidebarSearch');
    if (sb) { e.preventDefault(); sb.focus(); }
  });

  // ---- Countdown timers (poll DOM every second, picks up new elements) ----
  function fmt(ms) {
    if (ms <= 0) return 'Auction ended';
    var s = Math.floor(ms / 1000);
    var d = Math.floor(s / 86400); s %= 86400;
    var h = Math.floor(s / 3600); s %= 3600;
    var m = Math.floor(s / 60); s %= 60;
    var parts = [];
    if (d) parts.push(d + 'd');
    parts.push(String(h).padStart(2, '0') + 'h');
    parts.push(String(m).padStart(2, '0') + 'm');
    parts.push(String(s).padStart(2, '0') + 's');
    return parts.join(' ');
  }
  function tickCountdowns() {
    var els = document.querySelectorAll('.countdown[data-end]');
    var now = Date.now();
    els.forEach(function (el) {
      var end = Date.parse(el.getAttribute('data-end'));
      var remaining = end - now;
      el.textContent = fmt(remaining);
      if (remaining <= 0) {
        el.classList.add('opacity-60');
        el.textContent = 'Auction ended';
        el.classList.remove('countdown-urgent');
      } else if (remaining < 60000) {
        el.classList.add('countdown-urgent');
      } else {
        el.classList.remove('countdown-urgent');
      }
    });
  }
  tickCountdowns();
  setInterval(tickCountdowns, 1000);

  // ---- Image delete (car form) ----
  // Intercepts submit on [data-delete-image-form] forms. The delete forms live
  // OUTSIDE the main listing form (HTML forbids nested forms) and are linked to
  // their trash buttons via the form="" attribute. If JS fails or is disabled,
  // the form POSTs normally and the controller redirects back (non-AJAX fallback).
  function bindImageDelete() {
    document.addEventListener('submit', function (e) {
      var form = e.target.closest('[data-delete-image-form]');
      if (!form) return;
      e.preventDefault();

      appConfirm('Delete this image?', { confirmText: 'Delete' }).then(function (ok) {
        if (!ok) return;

        // Extract the image ID from the form's id (e.g. "delete-image-form-7")
        var idMatch = form.id && form.id.match(/delete-image-form-(\d+)$/);
        var imageId = idMatch ? idMatch[1] : null;

        // The trash button is elsewhere in the DOM (inside the main form), linked
        // via form="delete-image-form-{id}". Find it by that attribute.
        var btn = imageId
          ? document.querySelector('button[form="delete-image-form-' + imageId + '"]')
          : form.querySelector('button[type="submit"]');
        var wrapper = imageId
          ? document.querySelector('[data-image-wrapper="' + imageId + '"]')
          : form.closest('[data-image-wrapper]');
        var originalHTML = btn ? btn.innerHTML : '';

        // Show a spinner + disable while deleting
        if (btn) {
          btn.disabled = true;
          btn.classList.add('opacity-75', 'cursor-wait');
          btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0110 10" stroke-linecap="round"/></svg>';
        }

        fetch(form.action, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams(new FormData(form)).toString()
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              // Fade out + collapse the thumbnail before removing it
              if (wrapper) {
                wrapper.style.transition = 'opacity .25s ease, transform .25s ease';
                wrapper.style.opacity = '0';
                wrapper.style.transform = 'scale(0.8)';
                setTimeout(function () { wrapper.remove(); }, 250);
              }
              toast('Image deleted', 'success');
            } else {
              toast(data.message || 'Delete failed', 'error');
              if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-wait');
                btn.innerHTML = originalHTML;
              }
            }
          })
          .catch(function () {
            toast('Network error — retrying...', 'error');
            // Fallback: submit the form normally (non-AJAX)
            form.submit();
          });
      });
    });
  }
  bindImageDelete();

  // ---- Live bid polling on detail page (every 5s) ----
  var bidPollTimer = null;
  function startBidPolling() {
    if (bidPollTimer) { clearInterval(bidPollTimer); bidPollTimer = null; }
    if (!window.CAR_DETAIL || !window.CAR_DETAIL.active) return;
    var carId = window.CAR_DETAIL.id;
    bidPollTimer = setInterval(function () {
      fetch(APP_BASE + '/bids/history/' + carId, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) return;
          var cd = document.getElementById('currentBidDisplay');
          if (cd && data.current_bid) {
            cd.innerHTML = '&#8369;' + Number(data.current_bid).toLocaleString(undefined, { minimumFractionDigits: 2 });
          }
          var ul = document.getElementById('bidHistory');
          if (ul && data.history) {
            ul.innerHTML = '';
            data.history.forEach(function (b) {
              var li = document.createElement('li');
              li.className = 'flex justify-between border-b border-slate-100 pb-2';
              li.innerHTML = '<span class="font-medium text-slate-700">' + escapeHtml(b.bidder_name) + '</span>' +
                             '<span class="text-auction-dark font-semibold">&#8369;' + Number(b.bid_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
              ul.appendChild(li);
            });
          }
        })
        .catch(function () {});
    }, 5000);
  }

  // ---- Component binders (re-callable after PJAX content swap) ----

  function bindWatchlist() {
    var watchBtn = document.getElementById('watchlistBtn');
    if (!watchBtn || watchBtn._bound) return;
    watchBtn._bound = true;
    watchBtn.addEventListener('click', function () {
      var carId = watchBtn.getAttribute('data-car');
      var csrf = watchBtn.getAttribute('data-csrf');
      fetch(APP_BASE + '/watchlist/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'car_id=' + encodeURIComponent(carId) + '&_csrf_token=' + encodeURIComponent(csrf)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            watchBtn.setAttribute('data-watching', data.watching ? '1' : '0');
            watchBtn.innerHTML = data.watching ? '\u2605 Watching' : '\u2606 Add to watchlist';
            toast(data.watching ? 'Added to watchlist' : 'Removed from watchlist', 'success');
          } else {
            toast(data.message || 'Action failed', 'error');
          }
        })
        .catch(function () { toast('Network error', 'error'); });
    });
  }

  function bindMarkRead() {
    var markReadBtn = document.getElementById('markAllRead');
    if (!markReadBtn || markReadBtn._bound) return;
    markReadBtn._bound = true;
    markReadBtn.addEventListener('click', function () {
      var csrf = markReadBtn.getAttribute('data-csrf');
      fetch(APP_BASE + '/notifications/mark-read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf_token=' + encodeURIComponent(csrf)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            document.querySelectorAll('.notif-dot').forEach(function (d) { d.remove(); });
            document.querySelectorAll('.notif-row').forEach(function (r) {
              r.classList.remove('bg-white/[0.03]');
              r.classList.add('opacity-60');
              var p = r.querySelector('p');
              if (p) { p.classList.remove('text-zinc-100', 'font-medium'); p.classList.add('text-zinc-400'); }
            });
            markReadBtn.remove();
            toast('All notifications marked read', 'success');
            updateNotifBadge(0);
          }
        });
    });
  }

  // Single-notification "Mark read" — stays on the page, dims the row.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.notif-view');
    if (!btn) return;
    e.preventDefault();
    fetch(APP_BASE + '/notifications/read/' + btn.getAttribute('data-id'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: '_csrf_token=' + encodeURIComponent(btn.getAttribute('data-csrf'))
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) { toast(data.message || 'Could not mark read', 'error'); return; }
        var row = btn.closest('.notif-row');
        if (row) {
          row.classList.remove('bg-white/[0.03]');
          row.classList.add('opacity-60');
          var p = row.querySelector('p');
          if (p) { p.classList.remove('text-zinc-100', 'font-medium'); p.classList.add('text-zinc-400'); }
          var dot = row.querySelector('.notif-dot');
          if (dot) dot.remove();
        }
        btn.remove();
        var pill = document.getElementById('notifUnreadPill');
        if (pill) {
          var left = data.unread != null ? data.unread : 0;
          if (left > 0) { pill.textContent = left + ' unread'; }
          else {
            pill.remove();
            var all = document.getElementById('markAllRead');
            if (all) all.remove();
          }
        }
        updateNotifBadge(data.unread != null ? data.unread : 0);
      })
      .catch(function () { toast('Network error', 'error'); });
  });

  // Header date pickers: sync into the filter form and apply.
  document.addEventListener('change', function (e) {
    var inp = e.target.closest('[data-report-date]');
    if (!inp) return;
    var form = document.querySelector('form[data-autosubmit]');
    if (!form) return;
    var target = form.querySelector('[name="' + inp.getAttribute('data-report-date') + '"]');
    if (target) { target.value = inp.value; submitViaPjax(form); }
  });

  // Report range picker: fills the filter form's from/to dates and applies.
  document.addEventListener('change', function (e) {
    var sel = e.target.closest('select[data-print-range]');
    if (!sel) return;
    var form = document.querySelector('form[data-autosubmit]');
    if (!form) return;
    var fmt = function (d) {
      var p = function (n) { return String(n).padStart(2, '0'); };
      return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
    };
    var today = new Date(), from = '', to = '';
    if (sel.value === 'today') { from = to = fmt(today); }
    else if (sel.value === '7' || sel.value === '30') {
      var d = new Date(today); d.setDate(d.getDate() - parseInt(sel.value, 10) + 1);
      from = fmt(d); to = fmt(today);
    } else if (sel.value === 'month') {
      from = fmt(new Date(today.getFullYear(), today.getMonth(), 1)); to = fmt(today);
    }
    var fi = form.querySelector('[name="from"]'), ti = form.querySelector('[name="to"]');
    if (fi) fi.value = from;
    if (ti) ti.value = to;
    submitViaPjax(form);
  });

  // Theme toggle: [data-theme-toggle] flips light/dark, persists to localStorage.
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-theme-toggle]');
    if (!t) return;
    var on = document.documentElement.classList.toggle('light-mode');
    localStorage.setItem('car_theme', on ? 'light' : 'dark');
    document.querySelectorAll('.theme-sun').forEach(function (i) { i.classList.toggle('hidden', on); });
    document.querySelectorAll('.theme-moon').forEach(function (i) { i.classList.toggle('hidden', !on); });
  });
  // Init icon state for the current theme
  (function () {
    var on = document.documentElement.classList.contains('light-mode');
    document.querySelectorAll('.theme-sun').forEach(function (i) { i.classList.toggle('hidden', on); });
    document.querySelectorAll('.theme-moon').forEach(function (i) { i.classList.toggle('hidden', !on); });
  })();

  // Print buttons: [data-print] opens the browser print dialog.
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-print]')) window.print();
  });

  // Links marked data-pjax navigate content-only (no chrome reload).
  document.addEventListener('click', function (e) {
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a[data-pjax]');
    if (!a) return;
    e.preventDefault();
    pjaxNavigate(a.href, true);
  });

  // Auto-submit filter forms: selects apply instantly; text/number inputs
  // apply after a short typing pause. GET submits go through PJAX so only
  // <main> swaps — header/sidebar don't reload.
  function submitViaPjax(form) {
    if (form.method.toLowerCase() === 'get' && typeof pjaxNavigate === 'function') {
      var params = new URLSearchParams();
      new FormData(form).forEach(function (v, k) { if (String(v).trim() !== '') params.append(k, v); });
      var q = params.toString();
      // NOTE: use getAttribute('action') — a field named "action" (logs filter)
      // shadows form.action with the element itself.
      var action = form.getAttribute('action') || window.location.pathname;
      pjaxNavigate(action + (q ? '?' + q : ''), true);
    } else {
      form.submit();
    }
  }
  var _autoSubmitTimer = null;
  document.addEventListener('change', function (e) {
    var form = e.target.closest('form[data-autosubmit]');
    if (form) submitViaPjax(form);
  });
  document.addEventListener('input', function (e) {
    var form = e.target.closest('form[data-autosubmit]');
    if (!form || !e.target.matches('input[type="text"], input[type="number"], input[type="search"]')) return;
    clearTimeout(_autoSubmitTimer);
    _autoSubmitTimer = setTimeout(function () { submitViaPjax(form); }, 600);
  });
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-autosubmit]');
    if (!form) return;
    e.preventDefault();
    clearTimeout(_autoSubmitTimer);
    submitViaPjax(form);
  });

  // Forms with data-confirm="msg" -> centered appConfirm modal before submit.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm]');
    if (!form || form._confirmed) return;
    e.preventDefault();
    appConfirm(form.getAttribute('data-confirm'), {
      confirmText: form.getAttribute('data-confirm-text') || 'Confirm'
    }).then(function (ok) {
      if (!ok) return;
      form._confirmed = true;
      form.submit();
    });
  });

  // "View" on a dead notification link: stay put, show the alert modal.
  document.addEventListener('click', function (e) {
    var dead = e.target.closest('.notif-dead');
    if (!dead) return;
    e.preventDefault();
    appAlert('This item is no longer available.', 'info');
  });

  // "View" on an unread notification: silently mark it read, then navigate.
  document.addEventListener('click', function (e) {
    var link = e.target.closest('a.notif-goto');
    if (!link || !link.getAttribute('data-unread')) return;
    e.preventDefault();
    var go = function () { window.location.href = link.href; };
    fetch(APP_BASE + '/notifications/read/' + link.getAttribute('data-id'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: '_csrf_token=' + encodeURIComponent(link.getAttribute('data-csrf'))
    }).then(go).catch(go);
    setTimeout(go, 800); // never trap the user if the request hangs
  });

  function bindBidForm() {
    var bidForm = document.getElementById('bidForm');
    if (!bidForm || bidForm._bound) return;
    bidForm._bound = true;
    bidForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = document.getElementById('bidSubmit');
      var input = document.getElementById('bid_amount');
      var amount = input.value;
      var csrf = (bidForm.querySelector('[name="_csrf_token"]') || {}).value;
      btn.disabled = true;
      btn.textContent = 'Placing bid...';

      fetch(bidForm.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify({ bid_amount: parseFloat(amount) })
      })
        .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
        .then(function (res) {
          var d = res.data;
          if (d.success) {
            toast(d.message, 'success');
            if (d.current_bid) {
              var cd = document.getElementById('currentBidDisplay');
              if (cd) cd.innerHTML = '&#8369;' + Number(d.current_bid).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }
            if (d.bid_count) {
              var ul = document.getElementById('bidHistory');
              if (ul) {
                ul.innerHTML = '';
                d.bid_count.forEach(function (b) {
                  var li = document.createElement('li');
                  li.className = 'flex justify-between border-b border-slate-100 pb-2';
                  li.innerHTML = '<span class="font-medium text-slate-700">' + escapeHtml(b.bidder_name) + '</span>' +
                                 '<span class="text-auction-dark font-semibold">&#8369;' + Number(b.bid_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
                  ul.appendChild(li);
                });
              }
            }
            bidForm.reset();
          } else {
            toast(d.message || 'Bid failed', 'error');
          }
        })
        .catch(function () { toast('Network error', 'error'); })
        .finally(function () {
          btn.disabled = false;
          btn.textContent = 'Place Bid';
        });
    });
  }

  // ---- Re-initialize dynamic components after content swap ----
  function initPage() {
    bindWatchlist();
    bindMarkRead();
    bindBidForm();
    startBidPolling();
    tickCountdowns();
    updateHeaderState();
    flashToModal();
  }

  // ---- Notification badge updater ----
  function updateNotifBadge(count) {
    var bell = document.querySelector('a[aria-label="Notifications"]');
    if (!bell) return;
    var badge = bell.querySelector('span.absolute');
    if (count > 0) {
      if (badge) {
        badge.textContent = count;
      } else {
        badge = document.createElement('span');
        badge.className = 'absolute -top-0.5 -right-0.5 bg-auction text-white text-[10px] font-semibold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1';
        badge.textContent = count;
        bell.appendChild(badge);
      }
    } else if (badge) {
      badge.remove();
    }
  }

  // ===========================================================
  // PJAX: sidebar navigation without full page refresh
  // ===========================================================

  var pjaxEnabled = !!window.history && !!window.history.pushState;

  // Floating loader — dark pill with spinner, centered under the header
  var pageLoader = document.createElement('div');
  pageLoader.id = 'pageLoader';
  pageLoader.className = 'fixed inset-x-0 top-20 z-[70] hidden justify-center pointer-events-none';
  pageLoader.innerHTML =
    '<div class="flex items-center gap-2.5 rounded-full border border-white/10 bg-[#1c1c22] px-4 py-2 shadow-xl shadow-black/50 animate-fade-in">' +
      '<svg class="w-4 h-4 animate-spin text-brand" fill="none" viewBox="0 0 24 24">' +
        '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>' +
        '<path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/>' +
      '</svg>' +
      '<span class="text-xs font-medium text-zinc-300">Loading…</span>' +
    '</div>';
  document.body.appendChild(pageLoader);

  function showLoading() {
    pageLoader.classList.remove('hidden');
    pageLoader.classList.add('flex');
  }
  function hideLoading() {
    setTimeout(function () {
      pageLoader.classList.add('hidden');
      pageLoader.classList.remove('flex');
    }, 200);
  }
  // Global API — use anywhere: showLoading() / hideLoading()
  window.showLoading = showLoading;
  window.hideLoading = hideLoading;

  // Extract <main> content from a full HTML document string
  function extractMain(html) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var subtitleMeta = doc.querySelector('meta[name="page-subtitle"]');
    return {
      main: doc.querySelector('main'),
      title: doc.querySelector('title') ? doc.querySelector('title').textContent : '',
      subtitle: subtitleMeta ? subtitleMeta.getAttribute('content') : '',
      flash: doc.querySelectorAll('.flash-slide, [class*="bg-green-50"][class*="border-green-200"], [class*="bg-red-50"][class*="border-red-200"]'),
      notifBadge: doc.querySelector('a[aria-label="Notifications"] span.absolute'),
    };
  }

  // Re-execute inline scripts inside the swapped content
  function reexecuteScripts(container) {
    var scripts = container.querySelectorAll('script');
    scripts.forEach(function (oldScript) {
      var newScript = document.createElement('script');
      // Copy attributes (including nonce)
      for (var i = 0; i < oldScript.attributes.length; i++) {
        newScript.setAttribute(oldScript.attributes[i].name, oldScript.attributes[i].value);
      }
      newScript.textContent = oldScript.textContent;
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }

  // Update navbar page indicator (title + subtitle) after PJAX navigation
  function updateNavPageTitle(title, subtitle) {
    var titleEl = document.getElementById('navPageTitle');
    var subtitleEl = document.getElementById('navPageSubtitle');
    if (titleEl && title) {
      // The <title> format is "PageTitle · AppName" — extract just the page part
      var parts = title.split('\u00b7'); // &middot; = ·
      var pageTitle = parts[0].trim();
      var firstDiv = titleEl.querySelector('div:first-child');
      if (firstDiv) firstDiv.textContent = pageTitle;
    }
    if (subtitleEl) {
      subtitleEl.textContent = subtitle || '';
    }
  }

  // Update active sidebar link
  function updateActiveLink(path) {
    var links = document.querySelectorAll('#sidebar nav a');
    links.forEach(function (a) {
      var href = a.getAttribute('href');
      var linkPath = href ? href.replace(APP_BASE, '') : '';
      // Normalize: strip trailing slashes for comparison
      linkPath = linkPath.replace(/\/$/, '') || '/';
      var currentPath = path.replace(/\/$/, '') || '/';
      if (linkPath === currentPath) {
        a.classList.add('text-white', 'bg-white/10');
        a.classList.remove('text-zinc-400');
      } else {
        a.classList.remove('text-white', 'bg-white/10');
        a.classList.add('text-zinc-400');
      }
    });
  }

  // Perform PJAX navigation
  function pjaxNavigate(url, pushState) {
    showLoading();
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.text();
      })
      .then(function (html) {
        var parsed = extractMain(html);
        if (!parsed.main) {
          // No <main> found — fall back to full page load
          window.location.href = url;
          return;
        }

        var mainEl = document.querySelector('main');
        if (!mainEl) { window.location.href = url; return; }

        // Swap content
        mainEl.innerHTML = parsed.main.innerHTML;

        // Re-execute inline scripts (e.g. window.CAR_DETAIL on detail page)
        reexecuteScripts(mainEl);

        // Flash banners from the PJAX response -> centered alert modal
        flashToModal(parsed.flash);

        // Update page title
        if (parsed.title) document.title = parsed.title;

        // Update navbar page indicator (title + subtitle)
        updateNavPageTitle(parsed.title, parsed.subtitle);

        // Update URL
        if (pushState !== false) {
          history.pushState({ pjax: true, url: url }, '', url);
        }

        // Update active sidebar link
        var path = url.replace(APP_BASE, '') || '/';
        updateActiveLink(path);

        // Update notification badge from the parsed response
        if (parsed.notifBadge) {
          updateNotifBadge(parseInt(parsed.notifBadge.textContent, 10) || 0);
        } else {
          updateNotifBadge(0);
        }

        // Re-initialize dynamic components
        initPage();

        // Scroll to top
        mainEl.scrollTop = 0;
        window.scrollTo(0, 0);

        hideLoading();
      })
      .catch(function () {
        hideLoading();
        // Fall back to full page load on error
        window.location.href = url;
      });
  }

  // Intercept sidebar link clicks
  if (pjaxEnabled) {
    document.addEventListener('click', function (e) {
      // Only handle left-clicks without modifier keys
      if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

      var link = e.target.closest('#sidebar nav a[href]');
      if (!link) return;

      var href = link.getAttribute('href');
      if (!href || href === '#' || href.startsWith('javascript:')) return;

      // Only handle same-origin GET links
      if (link.hasAttribute('download')) return;
      var url = href;
      // Resolve relative to APP_BASE
      if (url.startsWith(APP_BASE)) {
        // ok, full URL within app
      } else if (url.startsWith('/')) {
        url = APP_BASE + url;
      } else if (!url.startsWith('http')) {
        url = APP_BASE + '/' + url;
      }
      // Skip external links
      if (url.startsWith('http') && !url.startsWith(APP_BASE) && !url.startsWith(window.location.origin)) return;

      e.preventDefault();
      pjaxNavigate(url, true);
    });

    // Handle browser back/forward
    window.addEventListener('popstate', function (e) {
      var url = window.location.href;
      if (e.state && e.state.pjax) {
        pjaxNavigate(url, false);
      } else {
        // Initial page or non-PJAX state — do a normal load
        window.location.reload();
      }
    });

    // Set initial state
    history.replaceState({ pjax: true, url: window.location.href }, '', window.location.href);
  }

  // ---- Initial page initialization ----
  initPage();
})();
