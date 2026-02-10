/**
 * Global Beep Monitor
 * Monitors rides with 'searching' status and plays beep sound across all pages
 */

(function () {
  'use strict';

  // Configuration
  const POLLING_INTERVAL_MS = 10 * 1000;
  const BEEP_DURATION_SECONDS = 0.2;
  const BEEP_INTERVAL_MS = 35000; // 35 seconds between beeps
  const BEEP_DELAY_MS = 35000; // Wait 35 seconds after ride enters searching before first beep
  const STORAGE_KEY_BEEPING_RIDES = 'beeping_ride_ids';
  const STORAGE_KEY_BEEP_ACTIVE = 'beep_active';
  const STORAGE_KEY_RIDE_SEARCHING_TIMESTAMPS = 'ride_searching_timestamps';

  let beepInterval = null;
  let audioContext = null;
  let beepingRideIds = new Set();
  let rideSearchingTimestamps = new Map(); // Track when each ride entered searching status

  // Create beep sound using Web Audio API
  function createBeepSound() {
    try {
      if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
      }

      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);

      oscillator.frequency.value = 800; // Beep frequency in Hz
      oscillator.type = 'sine';

      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      // Fade out slightly at the end for smoother sound
      gainNode.gain.exponentialRampToValueAtTime(
        0.01,
        audioContext.currentTime + BEEP_DURATION_SECONDS
      );

      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + BEEP_DURATION_SECONDS);
    } catch (e) {
      console.error('Error creating beep sound:', e);
    }
  }

  function startBeep(skipImmediateBeep = false) {
    if (beepInterval) {
      return; // Already beeping
    }
    // Play beep at specified interval
    beepInterval = setInterval(() => {
      createBeepSound();
    }, BEEP_INTERVAL_MS);
    // Only play immediately if not resuming from page load
    if (!skipImmediateBeep) {
      createBeepSound();
    }
    saveBeepingState();
  }

  function stopBeep() {
    if (beepInterval) {
      clearInterval(beepInterval);
      beepInterval = null;
    }
    beepingRideIds.clear();
    saveBeepingState();
  }

  // Load beeping state from localStorage
  function loadBeepingState() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY_BEEPING_RIDES);
      if (stored) {
        beepingRideIds = new Set(JSON.parse(stored));
      }
      // Load ride searching timestamps
      const timestampsStored = localStorage.getItem(
        STORAGE_KEY_RIDE_SEARCHING_TIMESTAMPS
      );
      if (timestampsStored) {
        const timestamps = JSON.parse(timestampsStored);
        rideSearchingTimestamps = new Map(Object.entries(timestamps));
      }

      const beepActive =
        localStorage.getItem(STORAGE_KEY_BEEP_ACTIVE) === 'true';
      // Resume beep only if it was already active (don't play immediately on page load)

      if (beepActive && beepingRideIds.size > 0) {
        startBeep(true);
      }
    } catch (e) {
      console.error('Error loading beeping state:', e);
    }
  }

  // Save beeping state to localStorage
  function saveBeepingState() {
    try {
      localStorage.setItem(
        STORAGE_KEY_BEEPING_RIDES,
        JSON.stringify(Array.from(beepingRideIds))
      );
      localStorage.setItem(
        STORAGE_KEY_BEEP_ACTIVE,
        beepInterval ? 'true' : 'false'
      );
      // Save ride searching timestamps
      const timestampsObj = Object.fromEntries(rideSearchingTimestamps);
      localStorage.setItem(
        STORAGE_KEY_RIDE_SEARCHING_TIMESTAMPS,
        JSON.stringify(timestampsObj)
      );
    } catch (e) {
      console.error('Error saving beeping state:', e);
    }
  }

  // Check for beeping rides by monitoring localStorage
  function checkBeepingState() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY_BEEPING_RIDES);
      const beepActive =
        localStorage.getItem(STORAGE_KEY_BEEP_ACTIVE) === 'true';

      if (stored) {
        const storedIds = new Set(JSON.parse(stored));
        beepingRideIds = storedIds;

        if (beepActive === 'true' && storedIds.size > 0) {
          if (!beepInterval) {
            startBeep(true);
          }
        } else if (storedIds.size === 0 && beepInterval) {
          stopBeep();
        }
      } else if (beepInterval) {
        stopBeep();
      }
    } catch (e) {
      console.error('Error checking beeping state:', e);
    }
  }

  function shouldStartBeepingForRide(rideId) {
    if (!rideSearchingTimestamps.has(rideId)) {
      return false;
    }

    const timestamp = rideSearchingTimestamps.get(rideId);
    const now = Date.now();
    const elapsed = now - timestamp;

    return elapsed >= BEEP_DELAY_MS;
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      loadBeepingState();
      setInterval(checkBeepingState, 2000);
    });
  } else {
    loadBeepingState();
    setInterval(checkBeepingState, 2000);
  }

  // Expose functions globally for use in preorder.php
  window.BeepMonitor = {
    startBeep: startBeep,
    stopBeep: stopBeep,
    saveBeepingState: saveBeepingState,
    loadBeepingState: loadBeepingState,
    setBeepingRideIds: function (ids, rideTimestamps) {
      beepingRideIds = new Set(ids);
      if (rideTimestamps && Array.isArray(rideTimestamps)) {
        rideTimestamps.forEach(({ rideId, timestamp }) => {
          if (!rideSearchingTimestamps.has(rideId)) {
            rideSearchingTimestamps.set(rideId, timestamp);
          }
        });
      }

      rideSearchingTimestamps.forEach((timestamp, rideId) => {
        if (!beepingRideIds.has(rideId)) {
          rideSearchingTimestamps.delete(rideId);
        }
      });
      saveBeepingState();
      if (beepingRideIds.size === 0) {
        stopBeep();
        return;
      }

      if (beepInterval) {
        return;
      }
      const shouldBeep = Array.from(beepingRideIds).some((rideId) =>
        shouldStartBeepingForRide(rideId)
      );

      if (shouldBeep) {
        startBeep(false);
      } else {
        const earliestTimestamp = Math.min(
          ...Array.from(beepingRideIds).map(
            (id) => rideSearchingTimestamps.get(id) || Date.now()
          )
        );
        const timeSinceEarliest = Date.now() - earliestTimestamp;
        const remainingDelay = Math.max(0, BEEP_DELAY_MS - timeSinceEarliest);

        setTimeout(() => {
          if (beepingRideIds.size > 0 && !beepInterval) {
            startBeep(false);
          }
        }, remainingDelay);
      }
    },
    getBeepingRideIds: function () {
      return Array.from(beepingRideIds);
    },
  };
})();
