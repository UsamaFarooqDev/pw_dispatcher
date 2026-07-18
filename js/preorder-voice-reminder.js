/*
 * Global background voice reminder for unassigned pre-orders.
 *
 * Loaded on every authenticated page via modules/navbar.php. It polls the
 * rides API for pre-order (scheduled) rides that still have no driver and
 * repeatedly announces the count aloud using the browser's built-in voice,
 * so a dispatcher is reminded even on another page or another browser tab.
 *
 * The toggle (#voiceReminderToggle) is a switch in the navbar; the on/off
 * choice is persisted in localStorage under 'preorderVoiceReminders'. A shared
 * "last spoken" timestamp keeps the cadence steady across page navigations so
 * the voice doesn't restart on every refresh.
 *
 * Ride data comes from the shared js/rides-poller.js (one poll per cycle,
 * shared with searching-ride-beep.js) instead of polling independently.
 */
(function () {
  'use strict';

  if (!('speechSynthesis' in window)) return; // no TTS support → nothing to do

  // Pre-order statuses that count as "scheduled" — must match preorder.php.
  var SCHEDULED_STATUSES = ['upcoming', 'scheduled', 'pending', 'awaiting_assignment'];

  var ANNOUNCE_INTERVAL_MS = 10 * 1000;  // re-announce roughly every 5 seconds
  var STORAGE_KEY    = 'preorderVoiceReminders';      // on/off
  var STORAGE_TS_KEY = 'preorderVoiceReminderLastAt'; // last spoken timestamp (cross-page)

  var cachedCount = 0;
  var announceTimer = null;
  var stopped = false; // set true on auth failure

  function remindersEnabled() {
    // Default ON unless the dispatcher has explicitly muted it.
    return localStorage.getItem(STORAGE_KEY) !== 'off';
  }

  function rideHasDriver(ride) {
    return !!(ride && ride.driver_id != null && String(ride.driver_id).trim() !== '');
  }

  function isUnassignedPreorder(ride) {
    var status = (ride && ride.status ? ride.status : '').toLowerCase();
    return SCHEDULED_STATUSES.indexOf(status) !== -1 && !rideHasDriver(ride);
  }

  function updateCountFromRides(rides) {
    if (stopped) return;
    cachedCount = (rides || []).filter(isUnassignedPreorder).length;
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

  function announce(force) {
    if (stopped || !remindersEnabled()) return;
    var n = cachedCount;
    if (n <= 0) return;                          // nothing to nag about
    if (window.speechSynthesis.speaking) return; // don't cut off the last line
    if (!force && !dueToAnnounce()) return;      // respect the cross-page cadence

    var rideWord   = n === 1 ? 'ride' : 'rides';
    var driverLine = n === 1 ? 'Please assign a driver.' : 'Please assign drivers.';
    var msg = n + ' ' + rideWord + ' unassigned in the pre-order tab. ' + driverLine;

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

    // Count comes from the shared rides poller; announce on its own cadence.
    if (window.RidesPoller) {
      window.RidesPoller.subscribe(updateCountFromRides, stop);
    }
    announceTimer = setInterval(announce, ANNOUNCE_INTERVAL_MS);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
