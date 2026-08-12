/*
 * Global background counts for the sidebar's Live Orders sub-tab badges.
 *
 * Previously these badges only ever resolved once js/liveorder.js itself
 * ran a fetch — which only happens while actually on liveorder.php — so on
 * every other page they were stuck showing the loading-dots animation
 * forever (never settling to a real number, or to 0). This keeps them live
 * everywhere, independent of whether the Live Orders dropdown is open or
 * closed: the dropdown's open/closed state (js/sidebar.js) is purely
 * visual and has no bearing on whether the counts have loaded — a badge
 * should show its real count and stop loading as soon as data is in,
 * whether or not the dispatcher has the dropdown open to see it.
 *
 * Six of the seven counts mirror liveorder.js's own tab-membership rules
 * exactly (so a badge always agrees with the tab it points to) and come
 * from the shared rides feed (js/rides-poller.js, one fetch per cycle for
 * the whole app). Meet & Greet rides live in a separate table/endpoint
 * (api/get_meet_and_greet_rides.php), so it's polled on its own, same
 * cadence, independent of the shared feed.
 */
(function () {
  'use strict';

  var MEETGREET_POLL_MS = 10 * 1000;

  var SCHEDULED_STATUSES = ['upcoming', 'scheduled', 'pending', 'awaiting_assignment'];
  var ENROUTE_STATUSES = ['enroute', 'en_route', 'en-route'];

  var stopped = false; // set true on auth failure (via RidesPoller's onStop)
  var meetGreetTimer = null;

  function rideHasDriver(ride) {
    return !!(ride && ride.driver_id != null && String(ride.driver_id).trim() !== '');
  }

  function setBadge(id, count) {
    var badge = document.getElementById(id);
    if (!badge) return;
    badge.textContent = count;
    badge.classList.remove('is-loading');
  }

  function updateFromRides(rides) {
    if (stopped) return;
    var counts = { unassigned: 0, assigned: 0, enroute: 0, scheduled: 0, cancelled: 0, completed: 0 };

    (rides || []).forEach(function (r) {
      var status = (r && r.status ? r.status : '').toLowerCase();
      var scheduled = SCHEDULED_STATUSES.indexOf(status) !== -1;
      var hasDriver = rideHasDriver(r);

      if (status === 'searching') {
        counts.unassigned++;
      } else if (status === 'assigned' || (scheduled && hasDriver)) {
        counts.assigned++;
      } else if (ENROUTE_STATUSES.indexOf(status) !== -1) {
        counts.enroute++;
      } else if (scheduled && !hasDriver) {
        counts.scheduled++;
      } else if (status === 'cancelled' || status === 'canceled') {
        counts.cancelled++;
      } else if (status === 'completed' || status === 'finished') {
        counts.completed++;
      }
      // On-trip and any other status aren't shown as a sidebar sub-link.
    });

    setBadge('count-unassigned', counts.unassigned);
    setBadge('count-assigned', counts.assigned);
    setBadge('count-enroute', counts.enroute);
    setBadge('count-scheduled', counts.scheduled);
    setBadge('count-cancelled', counts.cancelled);
    setBadge('count-finished', counts.completed);
  }

  async function pollMeetGreetCount() {
    if (stopped) return;
    try {
      var res = await fetch('api/get_meet_and_greet_rides.php?page=1&limit=1000');
      if (res.status === 401) return; // navbar/session handling covers the redirect
      if (!res.ok) return;
      var json = await res.json();
      if (!json || !json.success) return;
      // Exclude cancelled — matches liveorder.js's own Meet & Greet count.
      var rides = (json.data || []).filter(function (r) {
        var s = (r.status || '').toLowerCase();
        return s !== 'cancelled' && s !== 'canceled';
      });
      setBadge('count-meet-greet', rides.length);
    } catch (e) {
      console.debug('sidebar-counts: meet & greet poll failed', e);
    }
  }

  function stop() {
    stopped = true;
    if (meetGreetTimer) clearInterval(meetGreetTimer);
  }

  function start() {
    if (window.RidesPoller) {
      window.RidesPoller.subscribe(updateFromRides, stop);
    }
    pollMeetGreetCount();
    meetGreetTimer = setInterval(pollMeetGreetCount, MEETGREET_POLL_MS);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
