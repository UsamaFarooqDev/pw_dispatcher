(function () {
  'use strict';

  const BEEP_INTERVAL_MS = 47000;
  const BEEP_DELAY_MS = 47000;
  const BEEP_DURATION_SECONDS = 0.2;
  const STORAGE_KEY_RIDES = 'beeping_ride_ids';
  const STORAGE_KEY_ACTIVE = 'beep_active';
  const STORAGE_KEY_TIMESTAMPS = 'ride_searching_timestamps';

  let beepInterval = null;
  let beepingRideIds = new Set();
  let rideSearchingTimestamps = new Map();
  let beepAudio = null;

  function initBeepAudio() {
    if (!beepAudio) {
      beepAudio = new Audio('/assets/ride_alert.mpeg');
      beepAudio.preload = 'auto';
      beepAudio.onerror = () => fallbackBeep();
    }
    return beepAudio;
  }

  function fallbackBeep() {
    try {
      if (!window._audioCtx) {
        window._audioCtx = new (
          window.AudioContext || window.webkitAudioContext
        )();
      }
      const ctx = window._audioCtx;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = 800;
      osc.type = 'sine';
      gain.gain.setValueAtTime(0.3, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(
        0.01,
        ctx.currentTime + BEEP_DURATION_SECONDS,
      );
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + BEEP_DURATION_SECONDS);
    } catch (e) {
      console.error('Fallback beep error:', e);
    }
  }

  function playBeepSound() {
    try {
      const audio = initBeepAudio();
      if (!audio.paused) {
        audio.pause();
        audio.currentTime = 0;
      }
      audio.play().catch(() => fallbackBeep());
    } catch (e) {
      fallbackBeep();
    }
  }

  function startBeep(skipImmediate = false) {
    if (beepInterval) return;
    if (!skipImmediate) playBeepSound();
    beepInterval = setInterval(() => {
      if (beepingRideIds.size === 0) {
        stopBeep();
        return;
      }
      playBeepSound();
    }, BEEP_INTERVAL_MS);
    saveState();
  }

  function stopBeep() {
    if (beepInterval) {
      clearInterval(beepInterval);
      beepInterval = null;
    }
    beepingRideIds.clear();
    rideSearchingTimestamps.clear();
    saveState();
  }

  function saveState() {
    try {
      localStorage.setItem(
        STORAGE_KEY_RIDES,
        JSON.stringify([...beepingRideIds]),
      );
      localStorage.setItem(STORAGE_KEY_ACTIVE, beepInterval ? 'true' : 'false');
      localStorage.setItem(
        STORAGE_KEY_TIMESTAMPS,
        JSON.stringify(Object.fromEntries(rideSearchingTimestamps)),
      );
    } catch (e) {}
  }

  function loadState() {
    try {
      const rides = localStorage.getItem(STORAGE_KEY_RIDES);
      const timestamps = localStorage.getItem(STORAGE_KEY_TIMESTAMPS);
      const active = localStorage.getItem(STORAGE_KEY_ACTIVE) === 'true';

      if (rides) beepingRideIds = new Set(JSON.parse(rides));
      if (timestamps)
        rideSearchingTimestamps = new Map(
          Object.entries(JSON.parse(timestamps)),
        );

      if (active && beepingRideIds.size > 0) {
        startBeep(true);
      } else {
        beepingRideIds.clear();
        rideSearchingTimestamps.clear();
        saveState();
      }
    } catch (e) {
      console.error('loadState error:', e);
    }
  }

  function syncFromStorage() {
    try {
      const active = localStorage.getItem(STORAGE_KEY_ACTIVE) === 'true';
      const rides = localStorage.getItem(STORAGE_KEY_RIDES);
      const ids = rides ? new Set(JSON.parse(rides)) : new Set();

      beepingRideIds = ids;

      if (ids.size === 0) {
        if (beepInterval) stopBeep();
        return;
      }

      if (active && !beepInterval) {
        startBeep(true);
      } else if (!active && beepInterval) {
        stopBeep();
      }
    } catch (e) {
      console.error('syncFromStorage error:', e);
    }
  }

  function hasRidePassedDelay(rideId) {
    if (!rideSearchingTimestamps.has(rideId)) return false;
    return Date.now() - rideSearchingTimestamps.get(rideId) >= BEEP_DELAY_MS;
  }

  function init() {
    loadState();
    setInterval(syncFromStorage, 2000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.BeepMonitor = {
    setBeepingRideIds(ids, rideTimestamps) {
      if (!ids || ids.length === 0) {
        stopBeep();
        return;
      }

      beepingRideIds = new Set(ids);

      if (Array.isArray(rideTimestamps)) {
        rideTimestamps.forEach(({ rideId, timestamp }) => {
          if (!rideSearchingTimestamps.has(rideId)) {
            rideSearchingTimestamps.set(rideId, timestamp);
          }
        });
      }

      rideSearchingTimestamps.forEach((_, rideId) => {
        if (!beepingRideIds.has(rideId)) {
          rideSearchingTimestamps.delete(rideId);
        }
      });

      saveState();

      if (beepInterval) return;

      const readyToBeep = [...beepingRideIds].some((id) =>
        hasRidePassedDelay(id),
      );

      if (readyToBeep) {
        startBeep(false);
      } else {
        const earliest = Math.min(
          ...[...beepingRideIds].map(
            (id) => rideSearchingTimestamps.get(id) || Date.now(),
          ),
        );
        const wait = Math.max(0, BEEP_DELAY_MS - (Date.now() - earliest));

        setTimeout(() => {
          if (beepingRideIds.size > 0 && !beepInterval) {
            startBeep(false);
          }
        }, wait);
      }
    },

    stopBeep,

    getBeepingRideIds: () => [...beepingRideIds],

    clearIfNoSearchingRides() {
      if (beepingRideIds.size === 0) stopBeep();
    },
  };
})();
