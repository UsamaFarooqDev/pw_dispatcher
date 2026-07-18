/*
 * Shared background poller for api/get_rides.php (lite mode).
 *
 * Loaded on every authenticated page via modules/head.php, before the
 * global scripts that need a live view of ride statuses
 * (searching-ride-beep.js, preorder-voice-reminder.js). Those used to each
 * run their own independent 10s poll of the same endpoint; this fetches
 * once per cycle and hands the result to every subscriber instead.
 */
(function () {
  'use strict';

  var POLL_INTERVAL_MS = 10 * 1000;
  var subscribers = [];
  var stopSubscribers = [];
  var pollTimer = null;
  var stopped = false;

  async function poll() {
    if (stopped) return;
    try {
      var res = await fetch('api/get_rides.php?limit=1000&mode=lite');
      if (res.status === 401) { stop(); return; } // navbar handles the redirect
      if (!res.ok) return;
      var json = await res.json();
      if (!json || !json.success) return;
      var rides = json.data || [];
      subscribers.forEach(function (fn) {
        try { fn(rides); } catch (e) { console.debug('rides-poller: subscriber failed', e); }
      });
    } catch (err) {
      console.debug('rides-poller: poll failed', err);
    }
  }

  function stop() {
    stopped = true;
    if (pollTimer) clearInterval(pollTimer);
    stopSubscribers.forEach(function (fn) {
      try { fn(); } catch (e) { console.debug('rides-poller: stop subscriber failed', e); }
    });
  }

  function start() {
    poll();
    pollTimer = setInterval(poll, POLL_INTERVAL_MS);
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) poll();
    });
  }

  window.RidesPoller = {
    // fn(rides) is called with the lite ride array on every poll cycle.
    // onStop() (optional) is called once if polling stops (e.g. session
    // expired / 401), so subscribers can stop acting on a now-stale result.
    subscribe: function (fn, onStop) {
      if (typeof fn === 'function') subscribers.push(fn);
      if (typeof onStop === 'function') stopSubscribers.push(onStop);
    }
  };

  start();
})();
