/*
 * Global "searching ride" detector → feeds the BeepMonitor from ANY page.
 *
 * Previously only the Live Orders (preorder) page detected rides in
 * 'searching' status and started the audible beep. This detects it on every
 * authenticated page so that a ride entering 'searching' (e.g. after being
 * unassigned) triggers the beep regardless of which page the dispatcher is
 * on. The actual sound + 47s delay + cross-page persistence is handled by
 * js/beep-monitor.js (window.BeepMonitor); this only supplies the current
 * set of searching ride ids.
 *
 * Ride data comes from the shared js/rides-poller.js (one poll per cycle,
 * shared with preorder-voice-reminder.js) instead of polling independently.
 */
(function () {
  'use strict';

  var prevStatuses = {}; // rideId -> last-seen status (to detect new 'searching')

  function detect(rides) {
    if (!window.BeepMonitor) return; // beep-monitor.js not ready / absent

    var current = {};
    var beepingIds = [];
    var newSearching = []; // rides that just entered 'searching' (for the delay timer)

    rides.forEach(function (r) {
      if (!r || !r.id) return;
      var status = (r.status || '').toLowerCase();
      current[r.id] = status;
      if (status === 'searching') {
        beepingIds.push(r.id);
        if (prevStatuses[r.id] !== 'searching') {
          // Time the 47s grace from when the ride actually entered searching
          // (updated_at), not from page load — so long-waiting rides beep promptly.
          var ts = r.updated_at ? new Date(r.updated_at).getTime() : Date.now();
          if (isNaN(ts)) ts = Date.now();
          newSearching.push({ rideId: r.id, timestamp: ts });
        }
      }
    });

    prevStatuses = current;

    if (beepingIds.length > 0) {
      window.BeepMonitor.setBeepingRideIds(beepingIds, newSearching);
    } else {
      window.BeepMonitor.setBeepingRideIds([], []); // none searching → stop
    }
  }

  if (window.RidesPoller) {
    window.RidesPoller.subscribe(detect);
  }
})();
