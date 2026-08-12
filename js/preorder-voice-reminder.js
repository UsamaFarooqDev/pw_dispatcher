/*
 * Global background voice reminders for rides that still have no driver.
 *
 * Loaded on every authenticated page via modules/navbar.php. It reads the
 * shared rides feed (js/rides-poller.js) and tracks three categories:
 *
 *   - searching : status 'searching'                                   (Unassigned tab)
 *   - pending   : status 'pending', no driver yet                      (Pre-Order tab, corporate-sourced)
 *   - preorder  : status upcoming/scheduled/awaiting_assignment,       (Pre-Order tab, app/dispatcher-sourced)
 *                 no driver yet
 *
 * Every ANNOUNCE_INTERVAL_MS it speaks ONE category — round-robin through
 * searching -> pending -> preorder, in that fixed order, skipping whichever
 * are currently empty — so two or three categories waiting at once are
 * never collapsed into a single sentence; each gets its own turn. A
 * category simply drops out of the rotation the moment every ride in it
 * has a driver (count reaches zero).
 *
 * The toggle (#voiceReminderToggle) is a switch in the navbar; the on/off
 * choice is persisted in localStorage under 'preorderVoiceReminders'. The
 * rotation cursor and "last spoken" timestamp are also shared via
 * localStorage so both the cadence and the ordering stay steady across
 * page navigations instead of resetting on every page load.
 */
(function () {
  'use strict';

  if (!('speechSynthesis' in window)) return; // no TTS support → nothing to do

  // Fixed announce order — also doubles as the set of valid cursor values.
  var CATEGORY_ORDER = ['searching', 'pending', 'preorder'];

  // "Pre-order" tab statuses that are NOT the corporate 'pending' status —
  // must match the SCHEDULED_STATUSES set in liveorder.php minus 'pending'.
  var PREORDER_STATUSES = ['upcoming', 'scheduled', 'awaiting_assignment'];

  var ANNOUNCE_INTERVAL_MS = 20 * 1000;  // one category announced every 20s
  var STORAGE_KEY     = 'preorderVoiceReminders';       // on/off
  var STORAGE_TS_KEY  = 'preorderVoiceReminderLastAt';  // last spoken timestamp (cross-page)
  var STORAGE_CUR_KEY = 'preorderVoiceReminderCursor';  // next category in the rotation (cross-page)

  var counts = { searching: 0, pending: 0, preorder: 0 };
  var announceTimer = null;
  var stopped = false; // set true on auth failure

  function remindersEnabled() {
    // Default ON unless the dispatcher has explicitly muted it.
    return localStorage.getItem(STORAGE_KEY) !== 'off';
  }

  function rideHasDriver(ride) {
    return !!(ride && ride.driver_id != null && String(ride.driver_id).trim() !== '');
  }

  // Which of the three categories (if any) a ride currently belongs to.
  function categoryOf(ride) {
    var status = (ride && ride.status ? ride.status : '').toLowerCase();
    if (status === 'searching') return 'searching';
    if (rideHasDriver(ride)) return null; // already assigned — nothing to nag about
    if (status === 'pending') return 'pending';
    if (PREORDER_STATUSES.indexOf(status) !== -1) return 'preorder';
    return null;
  }

  function updateCountsFromRides(rides) {
    if (stopped) return;
    var next = { searching: 0, pending: 0, preorder: 0 };
    (rides || []).forEach(function (r) {
      var cat = categoryOf(r);
      if (cat) next[cat]++;
    });
    counts = next;
  }

  function flashToggle() {
    var btn = document.getElementById('voiceReminderToggle');
    if (!btn) return;
    btn.classList.add('is-announcing');
    setTimeout(function () { btn.classList.remove('is-announcing'); }, 3000);
  }

  // Shared cadence across page loads: only announce if at least one interval
  // has passed since the last spoken line on ANY page. This stops the voice
  // from restarting every time the dispatcher refreshes or changes pages.
  function dueToAnnounce() {
    var last = parseInt(localStorage.getItem(STORAGE_TS_KEY) || '0', 10);
    if (!last) return true;
    return (Date.now() - last) >= (ANNOUNCE_INTERVAL_MS - 500);
  }

  function getCursor() {
    var idx = CATEGORY_ORDER.indexOf(localStorage.getItem(STORAGE_CUR_KEY));
    return idx === -1 ? 0 : idx;
  }
  function setCursor(idx) {
    localStorage.setItem(STORAGE_CUR_KEY, CATEGORY_ORDER[idx % CATEGORY_ORDER.length]);
  }

  function pluralize(n, singular, plural) { return n === 1 ? singular : plural; }

  // Short, precise per-category lines — no addresses/names, just what and
  // how many, so the announcement stays quick.
  function buildMessage(key, n) {
    switch (key) {
      case 'searching':
        return n + ' ' + pluralize(n, 'ride is', 'rides are') + ' searching for a driver.';
      case 'pending':
        return n + ' pending ' + pluralize(n, 'ride', 'rides') + ' from corporate, waiting for a driver.';
      case 'preorder':
        return n + ' pre-order ' + pluralize(n, 'ride', 'rides') + ' waiting for a driver.';
      default:
        return '';
    }
  }

  // Walks the fixed order starting at the saved cursor and returns the
  // first non-empty category, advancing the cursor past it for next time.
  // Returns null if every category is currently empty.
  function nextDueCategory() {
    var start = getCursor();
    for (var i = 0; i < CATEGORY_ORDER.length; i++) {
      var idx = (start + i) % CATEGORY_ORDER.length;
      var key = CATEGORY_ORDER[idx];
      if (counts[key] > 0) {
        setCursor(idx + 1);
        return key;
      }
    }
    return null;
  }

  function announce(force) {
    if (stopped || !remindersEnabled()) return;
    if (window.speechSynthesis.speaking) return; // don't cut off the last line
    if (!force && !dueToAnnounce()) return;      // respect the cross-page cadence

    var key = nextDueCategory();
    if (!key) return; // nothing waiting in any category right now

    var msg = buildMessage(key, counts[key]);
    if (!msg) return;

    try {
      var u = new SpeechSynthesisUtterance(msg);
      u.rate = 1.0; u.pitch = 1.0; u.lang = 'en-GB';
      window.speechSynthesis.speak(u);
      localStorage.setItem(STORAGE_TS_KEY, String(Date.now()));
      flashToggle();
    } catch (e) {
      console.debug('voice-reminder: speak failed', e);
    }
  }

  // Reflect the on/off state on the navbar switch (#voiceReminderToggle).
  function updateToggle() {
    var btn  = document.getElementById('voiceReminderToggle');
    var icon = document.getElementById('voiceReminderIcon');
    if (!btn) return;
    var on = remindersEnabled();
    btn.classList.toggle('is-on', on);          // drives the .vr-toggle knob/track
    btn.setAttribute('aria-checked', on ? 'true' : 'false');
    if (icon) icon.className = on ? 'bi bi-volume-up-fill vr-toggle__icon'
                                  : 'bi bi-volume-mute-fill vr-toggle__icon';
  }

  function stop() {
    stopped = true;
    if (announceTimer) clearInterval(announceTimer);
    window.speechSynthesis.cancel();
  }

  function start() {
    updateToggle();

    var btn = document.getElementById('voiceReminderToggle');
    if (btn) {
      var onToggle = function () {
        var nowOn = !remindersEnabled();
        localStorage.setItem(STORAGE_KEY, nowOn ? 'on' : 'off');
        updateToggle();
        if (nowOn) {
          // This click is the user gesture that unlocks audio; speak now,
          // bypassing the cadence throttle for immediate feedback.
          announce(true);
        } else {
          window.speechSynthesis.cancel();
        }
      };
      btn.addEventListener('click', onToggle);
      // Keyboard accessibility for the switch (Space / Enter).
      btn.addEventListener('keydown', function (e) {
        if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); onToggle(); }
      });
    }

    // Counts come from the shared rides poller; announce on its own cadence.
    if (window.RidesPoller) {
      window.RidesPoller.subscribe(updateCountsFromRides, stop);
    }
    announceTimer = setInterval(announce, ANNOUNCE_INTERVAL_MS);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
