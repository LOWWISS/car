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
    el.className = 'toast ' + (type || 'info') + ' animate-slide-in-right';
    el.textContent = message;
    container.appendChild(el);
    setTimeout(function () {
      el.style.opacity = '0';
      el.style.transform = 'translateX(20px)';
      el.style.transition = 'opacity .3s, transform .3s';
      setTimeout(function () { el.remove(); }, 300);
    }, 4000);
  }
  window.toast = toast;

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
    }
  });

  // Restore collapsed state on load
  applySidebarState();

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
      if (!confirm('Delete this image?')) return;

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
            document.querySelectorAll('li .bg-auction').forEach(function (d) { d.classList.remove('bg-auction'); d.classList.add('bg-slate-300'); });
            toast('All notifications marked read', 'success');
            updateNotifBadge(0);
          }
        });
    });
  }

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

  // Loading bar
  var loadingBar = document.createElement('div');
  loadingBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:#4f46e5;z-index:9999;transition:width .3s ease, opacity .3s ease;opacity:0';
  document.body.appendChild(loadingBar);

  function showLoading() {
    loadingBar.style.opacity = '1';
    loadingBar.style.width = '30%';
    setTimeout(function () { loadingBar.style.width = '60%'; }, 100);
  }
  function hideLoading() {
    loadingBar.style.width = '100%';
    setTimeout(function () {
      loadingBar.style.opacity = '0';
      setTimeout(function () { loadingBar.style.width = '0'; }, 300);
    }, 200);
  }

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
        a.classList.add('text-brand', 'bg-slate-100');
        a.classList.remove('text-slate-600');
      } else {
        a.classList.remove('text-brand', 'bg-slate-100');
        a.classList.add('text-slate-600');
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
