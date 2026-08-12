/*
 * Lightweight AJAX/pjax-style navigation between dashboard pages.
 *
 * Loaded once from the persistent shell (modules/head.php). Sidebar and
 * navbar never reload; only <main id="app-content"> is replaced. The first
 * page load is always a normal PHP-rendered request — this is a pure
 * progressive enhancement layered on top of it, not a requirement for the
 * app to function.
 *
 * Per-page JS (order.js, liveorder.js, etc.) is only ever loaded ONCE per
 * browser session — every one of those files declares top-level let/const
 * globals, so re-injecting the same <script src> a second time throws
 * "Identifier already declared". Each page instead registers a reusable
 * { init, cleanup } pair on window.SPA_PAGES the first time it loads (see
 * the bottom of each page's own JS file); revisits just call init() again
 * without re-injecting the script.
 */
(function () {
  'use strict';

  var KNOWN_PAGES = [
    'home.php', 'fleetRegistry.php', 'order.php', 'orderassigned.php',
    'liveorder.php', 'map.php', 'application_rides.php', 'profile.php',
  ];
  var EXCLUDED_IDS = { logoutBtn: true, logoutBtnProfile: true };

  window.SPA_PAGES = window.SPA_PAGES || {};

  var currentPageKey = basenameOf(window.location.pathname);
  var loadingScripts = {}; // pageKey -> Promise, while a first-time <script> load is in flight
  var scrollPositions = {}; // url -> {x, y}, best-effort back/forward restoration

  function basenameOf(pathname) {
    var parts = pathname.split('/');
    return parts[parts.length - 1] || 'home.php';
  }

  function isEligibleLink(a) {
    if (!a || !a.href) return null;
    if (a.target === '_blank' || a.hasAttribute('download')) return null;
    if (a.hasAttribute('data-no-spa')) return null;
    if (a.rel && a.rel.indexOf('external') !== -1) return null;
    if (EXCLUDED_IDS[a.id]) return null;

    var url;
    try {
      url = new URL(a.href, window.location.href);
    } catch (e) {
      return null;
    }
    if (url.origin !== window.location.origin) return null;
    if (url.protocol === 'mailto:' || url.protocol === 'tel:') return null;

    var page = basenameOf(url.pathname);
    if (KNOWN_PAGES.indexOf(page) === -1) return null;

    return url;
  }

  function showProgress() {
    var bar = document.getElementById('spaProgressBar');
    if (bar) bar.classList.add('is-active');
    var content = document.getElementById('app-content');
    if (content) content.classList.add('spa-loading');
  }

  function hideProgress() {
    var bar = document.getElementById('spaProgressBar');
    if (bar) bar.classList.remove('is-active');
    var content = document.getElementById('app-content');
    if (content) content.classList.remove('spa-loading');
  }

  function loadPageScript(page) {
    if (loadingScripts[page]) return loadingScripts[page];
    loadingScripts[page] = new Promise(function (resolve, reject) {
      var src;
      if (page === 'fleetRegistry.php') {
        // Two-file bundle — app.js must load first, fleetRegistry.js depends on it.
        var s1 = document.createElement('script');
        s1.src = 'js/app.js';
        s1.onload = function () {
          var s2 = document.createElement('script');
          s2.src = 'js/fleetRegistry.js';
          s2.onload = resolve;
          s2.onerror = reject;
          document.body.appendChild(s2);
        };
        s1.onerror = reject;
        document.body.appendChild(s1);
        return;
      }
      var nameMap = {
        'home.php': 'home.js',
        'order.php': 'order.js',
        'orderassigned.php': 'orderassigned.js',
        'liveorder.php': 'liveorder.js',
        'map.php': 'map.js',
        'application_rides.php': 'application_rides.js',
        'profile.php': 'profile.js',
      };
      src = 'js/' + nameMap[page];
      if (!src) { resolve(); return; }
      var s = document.createElement('script');
      s.src = src;
      s.onload = resolve;
      s.onerror = reject;
      document.body.appendChild(s);
    });
    return loadingScripts[page];
  }

  // Inline PHP-bridge <script data-spa-inline> blocks (order.php's
  // rideTypeMultipliers, application_rides.php's IS_DISPATCHER_ROLE) don't
  // execute when set via innerHTML — re-create them as real <script>
  // elements so they run on every swap-in, not just the true first load.
  function runInlineBridgeScripts(container) {
    var scripts = container.querySelectorAll('script[data-spa-inline]');
    scripts.forEach(function (old) {
      var fresh = document.createElement('script');
      fresh.textContent = old.textContent;
      document.body.appendChild(fresh);
      fresh.remove();
    });
  }

  async function ensurePageReady(page) {
    if (!window.SPA_PAGES[page]) {
      await loadPageScript(page);
    }
    var entry = window.SPA_PAGES[page];
    if (entry && typeof entry.init === 'function') {
      entry.init();
    }
  }

  function saveScrollPosition() {
    scrollPositions[window.location.href] = { x: window.scrollX, y: window.scrollY };
  }

  async function navigate(url, opts) {
    opts = opts || {};
    var push = opts.push !== false;
    var isPopstate = !!opts.isPopstate;
    var targetUrl = url.toString();

    if (targetUrl === window.location.href && push) return; // no-op, same URL

    var outgoingPage = currentPageKey;
    showProgress();

    var response;
    try {
      response = await fetch(targetUrl, { credentials: 'same-origin' });
    } catch (err) {
      hideProgress();
      window.location.href = targetUrl; // network error — fall back to a real navigation
      return;
    }

    // The page-level session guard does header('Location: /') on an expired
    // session; fetch() silently follows it, so a 200 here can still mean
    // "you're not logged in." A real navigation re-triggers PHP's own
    // redirect properly instead of rendering the login HTML into #app-content.
    if (response.redirected || !response.ok) {
      hideProgress();
      window.location.href = targetUrl;
      return;
    }

    var html = await response.text();
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var newContent = doc.getElementById('app-content');
    var currentContent = document.getElementById('app-content');

    if (!newContent || !currentContent) {
      // Unexpected response shape — never leave the user on a broken partial page.
      hideProgress();
      window.location.href = targetUrl;
      return;
    }

    if (!isPopstate) saveScrollPosition();

    // Clean up the page we're leaving (stop polling/rAF loops) before swapping.
    var outgoingEntry = window.SPA_PAGES[outgoingPage];
    if (outgoingEntry && typeof outgoingEntry.cleanup === 'function') {
      try { outgoingEntry.cleanup(); } catch (e) { console.debug('spa-navigation: cleanup failed', e); }
    }

    currentContent.innerHTML = newContent.innerHTML;
    runInlineBridgeScripts(currentContent);

    var newTitle = doc.querySelector('title');
    if (newTitle && newTitle.textContent) {
      document.title = newTitle.textContent;
      var pageTitleEl = document.getElementById('pageTitle');
      if (pageTitleEl) {
        // Strip the " | Powercabs Dispatcher" suffix for the compact navbar heading.
        pageTitleEl.textContent = newTitle.textContent.split('|')[0].trim();
      }
    }

    if (push) {
      history.pushState({ spa: true }, '', targetUrl);
    }

    var newPage = basenameOf(new URL(targetUrl, window.location.href).pathname);
    currentPageKey = newPage;

    if (typeof window.highlightActiveSidebarLink === 'function') {
      window.highlightActiveSidebarLink();
    }

    try {
      await ensurePageReady(newPage);
    } catch (err) {
      console.error('spa-navigation: failed to initialize', newPage, err);
    }

    hideProgress();

    if (isPopstate && scrollPositions[targetUrl]) {
      window.scrollTo({ top: scrollPositions[targetUrl].y, left: scrollPositions[targetUrl].x, behavior: 'instant' });
    } else {
      window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    }
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a');
    if (!a) return;

    var url = isEligibleLink(a);
    if (!url) return;

    // Same-document hash-only navigation (e.g. sidebar Live Orders sub-links
    // while already on liveorder.php) — let the browser's native hash
    // handling + liveorder.js's own hashchange listener own this instead.
    if (url.pathname === window.location.pathname && url.search === window.location.search) {
      return;
    }

    e.preventDefault();
    navigate(url);
  }, true);

  window.addEventListener('popstate', function () {
    navigate(window.location.href, { push: false, isPopstate: true });
  });

  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }
})();
