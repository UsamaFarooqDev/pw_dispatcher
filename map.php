<?php
session_start();

require_once 'auth/require_login_redirect.php';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <!-- View toggle -->
  <div class="d-flex align-items-center gap-2 mt-4 mb-3">
    <button type="button" class="map-view-toggle is-active" id="btnTableView" onclick="switchMapView('table')">
      <i class="bi bi-table"></i> <span>Live Driver Coordinates</span>
    </button>
    <button type="button" class="map-view-toggle" id="btnMapView" onclick="switchMapView('map')">
      <i class="bi bi-map"></i> <span>Live Map</span>
    </button>
    <span id="tableOnlineCount" class="d-inline-flex align-items-center gap-1 fw-semibold ms-auto" style="font-size:0.75rem; color:#22C55E; background:#F0FDF4; border:1px solid #DCFCE7; padding:4px 10px; border-radius:999px;">
      <span style="width:6px; height:6px; border-radius:50%; background:#22C55E; display:inline-block;"></span>
      <span id="tableOnlineCountVal">0</span> active drivers
    </span>
  </div>

  <!-- TABLE VIEW (default) -->
  <div id="viewTable" class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
    <div class="p-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #F4F4F5;">
      <div class="position-relative" style="max-width:280px; flex:1;">
        <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
        <input type="text" id="tableSearchInput" placeholder="Search by name, email, phone..." class="form-control"
          style="height:36px; border:1px solid #E4E4E7; border-radius:8px; padding-left:32px; font-size:0.8125rem; background:#FAFAFA; color:#18181B;"
          onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
          onblur="this.style.borderColor='#E4E4E7'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
      </div>
    </div>
    <div class="table-responsive" style="max-height:calc(100vh - 260px); overflow-y:auto;">
      <table class="table mb-0" style="border-collapse:collapse; min-width:900px;">
        <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB; position:sticky; top:0; z-index:2;">
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:160px;">Driver Name</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:180px;">Email</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Phone</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Latitude</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Longitude</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Last Location</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Last Fix</th>
        </tr></thead>
        <tbody id="driverTableBody">
          <tr><td colspan="8" class="text-center py-4 text-muted">Loading drivers...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- MAP VIEW (hidden by default) -->
  <div id="viewMap" style="display:none; height:calc(100vh - 200px);">
  <div class="d-flex gap-3" style="height:100%;">

    <div class="position-relative flex-grow-1 rounded-3 overflow-hidden" style="border:1.5px solid #EBEBEB; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
      <div id="map" style="width:100%; height:100%; border:0;"></div>

      <div class="position-absolute bottom-0 start-0 m-3 d-flex gap-2">
        <button class="btn fw-semibold d-flex align-items-center gap-1"
          style="height:34px; background:#fff; color:#52525B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.8rem; box-shadow:0 2px 8px rgba(0,0,0,0.08);"
          onmouseover="this.style.borderColor='#f37a20'; this.style.color='#f37a20';"
          onmouseout="this.style.borderColor='#EBEBEB'; this.style.color='#52525B';">
          <i class="bi bi-sign-merge-right" style="font-size:13px;"></i> Traffic
        </button>
        <button class="btn fw-semibold d-flex align-items-center gap-1"
          style="height:34px; background:#fff; color:#52525B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.8rem; box-shadow:0 2px 8px rgba(0,0,0,0.08);"
          onmouseover="this.style.borderColor='#f37a20'; this.style.color='#f37a20';"
          onmouseout="this.style.borderColor='#EBEBEB'; this.style.color='#52525B';">
          <i class="bi bi-thermometer-half" style="font-size:13px;"></i> Heatmap
        </button>
      </div>
    </div>

    <div class="d-flex flex-column rounded-3 overflow-hidden" style="width:300px; flex-shrink:0; background:#fff; border:1px solid #E4E4E7; box-shadow:0 1px 3px rgba(0,0,0,0.06);">

      <div class="px-3 pt-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="fw-bold" style="font-size:0.75rem; letter-spacing:0.06em; text-transform:uppercase; color:#71717A;">Live Drivers</span>
          <span id="onlineCountBadge" class="d-inline-flex align-items-center gap-1 fw-semibold" style="font-size:0.7rem; color:#22C55E; background:#F0FDF4; border:1px solid #DCFCE7; padding:3px 8px; border-radius:999px;">
            <span style="width:6px; height:6px; border-radius:50%; background:#22C55E; display:inline-block;"></span>
            <span id="onlineCountValue">0</span> online
          </span>
        </div>
        <div class="position-relative">
          <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
          <input type="text" id="driverSearchInput" placeholder="Search drivers..." class="form-control"
            style="height:36px; border:1px solid #E4E4E7; border-radius:8px; padding-left:32px; font-size:0.8125rem; background:#FAFAFA; color:#18181B;"
            onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#E4E4E7'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
        </div>
      </div>

      <div id="driverCardList" class="p-3 d-flex flex-column gap-2" style="overflow-y:auto; flex:1; min-height:0;">
        <div id="driverListEmpty" class="text-center py-5" style="color:#A1A1AA;">
          <i class="bi bi-car-front" style="font-size:28px; opacity:0.4; display:block; margin-bottom:10px;"></i>
          <div style="font-size:0.8125rem; font-weight:600; color:#71717A;">No drivers online</div>
          <div style="font-size:0.72rem; margin-top:4px;">Cards will appear here as drivers come online.</div>
        </div>
      </div>

    </div>

  </div>
  </div><!-- /viewMap -->

</main>

<style>
  .orange-switch:checked {
    background-color: #f37a20 !important;
    border-color: #f37a20 !important;
  }
  .orange-switch:focus {
    box-shadow: 0 0 0 3px rgba(243,122,32,0.15) !important;
  }
  .map-view-toggle {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; font-size: 0.82rem; font-weight: 600;
    border-radius: 8px; border: 1.5px solid #E4E4E7;
    background: #fff; color: #71717A; cursor: pointer;
    transition: all 0.15s;
  }
  .map-view-toggle:hover { border-color: #f37a20; color: #f37a20; }
  .map-view-toggle.is-active { background: #18181B; color: #fff; border-color: #18181B; }
  .map-view-toggle.is-active:hover { background: #27272A; border-color: #27272A; }
  #driverTableBody tr { border-bottom: 1px solid #F4F4F5; transition: background 0.12s; }
  #driverTableBody tr:hover { background: #FAFAFA; }
  #driverTableBody td { padding: 12px 16px; font-size: 0.845rem; color: #18181B; vertical-align: middle; }
  .driver-status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
</style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9ea0A-mjnD5iHfT9X8Dn5YYH4_KZopLI&libraries=geometry" async defer></script>
    <script>
      // Global variables
      let map;
      let driverMarkers = {};
      let driverAnimTimers = {};    // per-driver smooth animation timers
      let driverLastPositions = {}; // previous lat/lng for bearing computation
      let driverBearings = {};      // current bearing (heading) per driver
      let driverRoutes = {};        // per-driver route polyline (the "track" to destination)
      let driverRouteMeta = {};     // per-driver {targetKey, ts, fromLat, fromLng} to throttle Directions calls
      let directionsService = null; // lazily created Google Directions client
      let allDrivers = [];
      const UPDATE_INTERVAL = 3000; // poll cadence — tighter = closer to real-time
      const MARKER_ANIM_MS = UPDATE_INTERVAL; // glide across the whole interval so the car moves continuously
      const TELEPORT_SNAP_METERS = 1000; // a jump larger than this can't be real movement in one interval → snap, don't drive across the map
      const ROUTE_REFRESH_MS = 15000;   // re-route at most this often per driver (Directions API is metered)
      const ROUTE_REDRAW_METERS = 150;  // ...or sooner once the car has moved this far along its track
      let updateIntervalId = null;
      let currentSearchQuery = '';
      let activeInfoWindow = null;
      let hasUserInteractedWithMap = false;

      // Initialize Google Maps
      function initMap() {
        if (typeof google === 'undefined' || !google.maps) {
          setTimeout(initMap, 300);
          return;
        }

        const mapElement = document.getElementById('map');
        if (!mapElement) {
          console.error('Map element not found');
          return;
        }

        // Dublin city center
        const defaultCenter = { lat: 53.349805, lng: -6.26031 };

        map = new google.maps.Map(mapElement, {
          center: defaultCenter,
          zoom: 14,
          minZoom: 3,
          mapTypeControl: true,
          streetViewControl: true,
          fullscreenControl: true,
          gestureHandling: 'greedy',
          clickableIcons: false,
          styles: [
            { featureType: 'poi.business', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', elementType: 'labels.icon', stylers: [{ visibility: 'off' }] }
          ]
        });

        directionsService = new google.maps.DirectionsService();

        // Once the user pans or zooms manually, stop auto-fitting the camera
        // so their selected view is preserved across polling refreshes.
        const markInteracted = () => { hasUserInteractedWithMap = true; };
        map.addListener('dragstart', markInteracted);
        map.addListener('zoom_changed', () => {
          // Ignore the very first programmatic zoom that happens during init
          if (map.__initialized) markInteracted();
        });
        // Mark init complete on next tick so the initial zoom doesn't count
        google.maps.event.addListenerOnce(map, 'idle', () => { map.__initialized = true; });

        // Load drivers and start polling
        loadDriverLocations();
        startPolling();
      }

      // Compute compass bearing (0 = north, clockwise) between two GPS points
      function computeBearing(fromLat, fromLng, toLat, toLng) {
        if (fromLat === toLat && fromLng === toLng) return null;
        const φ1 = fromLat * Math.PI / 180;
        const φ2 = toLat  * Math.PI / 180;
        const Δλ = (toLng - fromLng) * Math.PI / 180;
        const y  = Math.sin(Δλ) * Math.cos(φ2);
        const x  = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
        return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
      }

      // Human-readable "how fresh is this GPS fix" from an ISO timestamp.
      // Returns { text, stale } — stale=true when the fix is old enough that the
      // shown position should not be trusted as the driver's live location.
      // Returns how long ago the driver last sent a GPS fix, plus a stale flag.
      // "just now" covers up to 60 s — driver apps in background send roughly every 30 s.
      // Stale threshold: > 2 min means the driver app is likely closed / offline.
      function formatUpdatedAgo(updatedAt) {
        if (!updatedAt) return null;
        const t = new Date(updatedAt).getTime();
        if (isNaN(t)) return null;
        const secs = Math.max(0, Math.round((Date.now() - t) / 1000));
        const stale = secs > 120;
        let text;
        if (secs < 60)         text = 'just now';
        else if (secs < 3600)  text = Math.round(secs / 60) + 'm ago';
        else if (secs < 86400) text = Math.round(secs / 3600) + 'h ago';
        else                   text = Math.round(secs / 86400) + 'd ago';
        return { text, stale };
      }

      // Build a rotated top-down car SVG icon. Color varies by ride status.
      function buildDriverIcon(bearingDeg, rideStatus) {
        const s   = (rideStatus || '').toLowerCase();
        const col = ['on_trip','started','in_progress','trip_started'].includes(s)         ? '#3B82F6'
                  : ['arrived_at_pickup','driver_arrived','arrived'].includes(s)            ? '#22C55E'
                  : ['assigned','accepted','driver_accepted'].includes(s)                   ? '#f37a20'
                  : '#f37a20';
        const b = Math.round((bearingDeg || 0) % 360);
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44"><g transform="translate(22,22) rotate(${b})"><ellipse cx="0" cy="1" rx="13" ry="11" fill="rgba(0,0,0,0.18)"/><rect x="-9" y="-12" width="18" height="24" rx="5" fill="${col}" stroke="white" stroke-width="1.5"/><rect x="-7" y="-10" width="14" height="8" rx="3" fill="rgba(255,255,255,0.45)"/><rect x="-6" y="6" width="12" height="5" rx="2" fill="rgba(255,255,255,0.25)"/><rect x="-14" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="-14" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><polygon points="0,-19 -5,-12 5,-12" fill="white" opacity="0.9"/></g></svg>`;
        return {
          url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
          scaledSize: new google.maps.Size(44, 44),
          anchor: new google.maps.Point(22, 22),
        };
      }

      // Helper: is a driver currently online/available from their app?
      function isDriverOnline(driver) {
        const onlineFlag = driver.is_online ?? driver.online ?? driver.is_active ?? null;
        if (onlineFlag === true || onlineFlag === 1 || onlineFlag === '1' || onlineFlag === 'true') {
          return true;
        }
        const status = (driver.status || driver.driver_status || driver.availability || '')
          .toString()
          .toLowerCase();
        if (['online', 'available', 'active', 'free', 'on_duty', 'on duty'].includes(status)) {
          return true;
        }
        // If we have no online hint at all but we do have fresh lat/lng, treat as online.
        if (onlineFlag === null && !status && driver.current_lat != null && driver.current_lng != null) {
          return true;
        }
        return false;
      }

      // Where is this driver headed? Returns the live leg's target (pickup before
      // pickup, destination during the trip) or null for idle/available drivers,
      // who have no destination and therefore no track to draw.
      function tripDestinationFor(driver) {
        const s = (driver.status || '').toLowerCase();
        const onTrip    = ['on_trip', 'started', 'in_progress', 'trip_started'].includes(s);
        const prePickup = ['assigned', 'accepted', 'driver_accepted', 'arrived_at_pickup', 'driver_arrived', 'arrived'].includes(s);
        const num = (v) => (v != null && v !== '' && !isNaN(parseFloat(v))) ? parseFloat(v) : null;

        let lat = null, lng = null, kind = null;
        if (onTrip) {
          lat = num(driver.dest_lat);   lng = num(driver.dest_lng);   kind = 'dest';
          if (lat === null || lng === null) { lat = num(driver.pickup_lat); lng = num(driver.pickup_lng); kind = 'pickup'; }
        } else if (prePickup) {
          lat = num(driver.pickup_lat); lng = num(driver.pickup_lng); kind = 'pickup';
          if (lat === null || lng === null) { lat = num(driver.dest_lat); lng = num(driver.dest_lng); kind = 'dest'; }
        } else {
          return null;
        }
        if (lat === null || lng === null) return null;
        return { lat, lng, kind };
      }

      // Remove a driver's drawn track (and its throttle state).
      function clearDriverRoute(driverId) {
        if (driverRoutes[driverId]) {
          driverRoutes[driverId].setMap(null);
          delete driverRoutes[driverId];
        }
        delete driverRouteMeta[driverId];
      }

      // Draw / refresh the road route from the driver's current position to where
      // they're going. Throttled so we don't hammer the (metered) Directions API:
      // we re-route only when the destination changes, periodically, or once the
      // car has advanced far enough along the existing track.
      function maybeUpdateDriverRoute(driverId, fromLat, fromLng, driver) {
        const target = tripDestinationFor(driver);
        if (!target) { clearDriverRoute(driverId); return; }
        if (!directionsService) return;

        const targetKey = target.kind + ':' + target.lat.toFixed(5) + ',' + target.lng.toFixed(5);
        const meta = driverRouteMeta[driverId];
        const now  = Date.now();
        let needs = false;
        if (!meta || meta.targetKey !== targetKey) {
          needs = true;                                   // first route or destination changed
        } else if (now - meta.ts > ROUTE_REFRESH_MS) {
          needs = true;                                   // periodic refresh
        } else if (google.maps.geometry && google.maps.geometry.spherical) {
          const moved = google.maps.geometry.spherical.computeDistanceBetween(
            new google.maps.LatLng(meta.fromLat, meta.fromLng),
            new google.maps.LatLng(fromLat, fromLng)
          );
          if (moved > ROUTE_REDRAW_METERS) needs = true;  // car advanced along the track
        }
        if (!needs) return;

        // Reserve the throttle slot up front so overlapping polls don't double-fire.
        driverRouteMeta[driverId] = { targetKey, ts: now, fromLat, fromLng };

        directionsService.route({
          origin:      { lat: fromLat, lng: fromLng },
          destination: { lat: target.lat, lng: target.lng },
          travelMode:  google.maps.TravelMode.DRIVING,
        }, (result, status) => {
          if (status !== 'OK' || !result.routes || !result.routes[0]) return;
          // Driver may have gone idle / left while the request was in flight.
          if (!tripDestinationFor(driver) && !driverMarkers[driverId]) { clearDriverRoute(driverId); return; }
          const path = result.routes[0].overview_path;
          if (driverRoutes[driverId]) {
            driverRoutes[driverId].setPath(path);
          } else {
            driverRoutes[driverId] = new google.maps.Polyline({
              path,
              map,
              strokeColor:   '#f37a20',
              strokeOpacity: 0.85,
              strokeWeight:  4,
              zIndex:        1,
            });
          }
        });
      }

      // Fetch all active drivers: on-trip (from rides table) + idle online (from drivers table)
      async function loadDriverLocations() {
        try {
          const response = await fetch('api/get_all_active_drivers.php', { cache: 'no-store' });
          if (response.status === 401) { window.location.href = '/'; return; }
          const data = await response.json();

          if (data.success && data.data) {
            allDrivers = data.data.filter((d) => d.lat != null && d.lng != null);
            applySearchFilter();
          } else {
            console.error('Error loading live drivers:', data.error || 'Unknown error');
          }
        } catch (error) {
          console.error('Error fetching live driver locations:', error);
        }
      }

      // Filter drivers based on search query
      function filterDriversBySearch(drivers, query) {
        if (!query || query.trim() === '') {
          return drivers;
        }

        const searchTerm = query.toLowerCase().trim();
        return drivers.filter((driver) => {
          const name = (driver.full_name || driver.name || '').toLowerCase();
          const phone = (driver.phone || '').toLowerCase();
          const vehicleNumber = (driver.vehicle_number || '').toLowerCase();
          const email = (driver.email || '').toLowerCase();

          return (
            name.includes(searchTerm) ||
            phone.includes(searchTerm) ||
            vehicleNumber.includes(searchTerm) ||
            email.includes(searchTerm)
          );
        });
      }

      // Apply search filter and update markers
      function applySearchFilter() {
        const filteredDrivers = filterDriversBySearch(allDrivers, currentSearchQuery);
        updateDriverMarkers(filteredDrivers);
      }

      // Build the HTML shown inside an InfoWindow for a driver
      function buildDriverInfoContent(driver) {
        const name = driver.full_name || driver.name || 'Driver';
        const phone = driver.phone || '';
        const vehicle = driver.vehicle_number || '';
        const vehicleMake = driver.vehicle_make || '';
        const service = driver.service_type || driver.ride_type || '';
        const rating = driver.rating ? parseFloat(driver.rating).toFixed(1) : null;
        const completed = driver.total_completed_rides ?? null;
        const rawStatus = (driver.status || driver.ride_status || 'Online').toString();
        const status = rawStatus.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        const isOnTrip = ['on_trip','started','in_progress','trip_started','arrived_at_pickup','driver_arrived','arrived','assigned','accepted'].includes(rawStatus.toLowerCase());
        const initials = name.trim().split(/\s+/).map(p => p[0]).slice(0, 2).join('').toUpperCase() || 'D';

        const row = (icon, label, value) => value
          ? `<div style="display:flex; align-items:center; gap:8px; margin-top:6px; font-size:12px; color:#52525B;">
               <i class="bi ${icon}" style="font-size:13px; color:#A1A1AA; width:14px;"></i>
               <span style="color:#71717A;">${label}:</span>
               <span style="color:#18181B; font-weight:600;">${value}</span>
             </div>`
          : '';

        return `
          <div style="min-width:220px; padding:4px 2px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;">
            <div style="display:flex; align-items:center; gap:10px; padding-bottom:10px; border-bottom:1px solid #EBEBEB;">
              <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">${initials}</div>
              <div style="min-width:0;">
                <div style="font-weight:700; color:#18181B; font-size:14px; letter-spacing:-0.01em;">${name}</div>
                <div style="display:inline-flex; align-items:center; gap:5px; margin-top:2px;">
                  <span style="width:7px; height:7px; border-radius:50%; background:#22C55E;"></span>
                  <span style="font-size:11px; color:#22C55E; font-weight:600; text-transform:capitalize;">${status}</span>
                </div>
              </div>
            </div>
            <div style="padding-top:6px;">
              ${row('bi-telephone-fill', 'Phone', phone)}
              ${row('bi-car-front-fill', 'Vehicle', vehicle || vehicleMake)}
              ${row('bi-tag-fill', 'Service', service)}
              ${rating !== null ? row('bi-star-fill', 'Rating', `${rating} / 5`) : ''}
              ${completed !== null ? row('bi-check2-circle', 'Completed', `${completed} rides`) : ''}
              ${isOnTrip && driver.dest_addr ? row('bi-geo-alt-fill', 'Heading to', driver.dest_addr) : ''}
              ${(() => {
                const f = formatUpdatedAgo(driver.updated_at);
                if (!f) return '';
                const c = f.stale ? '#EF4444' : '#16A34A';
                return `<div style="display:flex; align-items:center; gap:8px; margin-top:6px; font-size:12px;">
                  <i class="bi bi-broadcast-pin" style="font-size:13px; color:#A1A1AA; width:14px;"></i>
                  <span style="color:#71717A;">Last fix:</span>
                  <span style="color:${c}; font-weight:600;">${f.text}${f.stale ? ' (stale)' : ''}</span>
                </div>`;
              })()}
            </div>
          </div>
        `;
      }

      // Render the sidebar card list from the live driver set.
      // Cards auto-appear when a driver comes online and auto-disappear when they go offline,
      // because the parent poll only hands us currently-online drivers.
      function renderDriverCards(drivers) {
        const list = document.getElementById('driverCardList');
        const empty = document.getElementById('driverListEmpty');
        const badge = document.getElementById('onlineCountValue');
        if (!list) return;

        if (badge) badge.textContent = drivers.length;

        // Remove the old cards (not the empty-state placeholder)
        Array.from(list.querySelectorAll('.driver-card')).forEach((el) => el.remove());

        if (!drivers || drivers.length === 0) {
          if (empty) empty.style.display = 'block';
          return;
        }
        if (empty) empty.style.display = 'none';

        drivers.forEach((driver) => {
          const name = driver.full_name || driver.name || 'Driver';
          const phone = driver.phone || '';
          const vehicle = driver.vehicle_number || driver.vehicle_make || '';
          const service = driver.service_type || driver.ride_type || '';
          const statusRaw = (driver.status || driver.ride_status || driver.availability || 'Online').toString().toLowerCase();
          const isBusy = ['busy', 'on_trip', 'started', 'in_progress', 'trip_started', 'arrived_at_pickup', 'driver_arrived', 'arrived', 'assigned', 'accepted', 'driver_accepted', 'on trip', 'engaged'].includes(statusRaw);
          const dotColor = isBusy ? '#F59E0B' : '#22C55E';
          const dotBg = isBusy ? '#FEF3C7' : '#F0FDF4';
          const dotLabel = isBusy ? 'Busy' : 'Online';
          const initials = name.trim().split(/\s+/).map((p) => p[0]).slice(0, 2).join('').toUpperCase() || 'D';

          const locLine = driver.dest_addr || driver.current_address
            || (driver.lat != null && driver.lng != null
                ? `${parseFloat(driver.lat).toFixed(4)}, ${parseFloat(driver.lng).toFixed(4)}`
                : '');

          const freshness = formatUpdatedAgo(driver.updated_at);

          const card = document.createElement('button');
          card.type = 'button';
          card.className = 'driver-card';
          card.dataset.driverId = driver.id;
          card.style.cssText = `
            text-align:left; width:100%; padding:12px; background:#fff;
            border:1px solid #E4E4E7; border-radius:10px; cursor:pointer;
            transition:border-color 0.15s, box-shadow 0.15s;
          `;
          card.onmouseover = () => {
            card.style.borderColor = '#f37a20';
            card.style.boxShadow = '0 4px 14px rgba(243,122,32,0.12)';
          };
          card.onmouseout = () => {
            card.style.borderColor = '#E4E4E7';
            card.style.boxShadow = 'none';
          };

          card.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0; letter-spacing:0.03em;">${initials}</div>
              <div style="min-width:0; flex:1;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                  <div style="font-weight:700; color:#18181B; font-size:13px; letter-spacing:-0.01em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${name}</div>
                  <span style="display:inline-flex; align-items:center; gap:4px; background:${dotBg}; border-radius:999px; padding:2px 7px; flex-shrink:0;">
                    <span style="width:5px; height:5px; border-radius:50%; background:${dotColor}; display:inline-block;"></span>
                    <span style="font-size:10px; font-weight:700; color:${dotColor}; text-transform:uppercase; letter-spacing:0.04em;">${dotLabel}</span>
                  </span>
                </div>
                ${vehicle ? `<div style="font-size:11px; color:#71717A; font-weight:500; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  <i class="bi bi-car-front-fill" style="font-size:10px; color:#A1A1AA; margin-right:4px;"></i>${vehicle}${service ? ` &middot; ${service}` : ''}
                </div>` : (service ? `<div style="font-size:11px; color:#71717A; font-weight:500; margin-top:3px;">${service}</div>` : '')}
              </div>
            </div>
            ${locLine ? `<div style="margin-top:10px; padding-top:10px; border-top:1px solid #F4F4F5; display:flex; align-items:flex-start; gap:6px;">
              <i class="bi bi-geo-alt-fill" style="font-size:11px; color:#f37a20; margin-top:2px; flex-shrink:0;"></i>
              <div style="font-size:11px; color:#52525B; line-height:1.4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${locLine}</div>
            </div>` : ''}
            ${phone ? `<div style="margin-top:6px; display:flex; align-items:center; gap:6px;">
              <i class="bi bi-telephone-fill" style="font-size:10px; color:#A1A1AA;"></i>
              <span style="font-size:11px; color:#52525B; font-weight:500;">${phone}</span>
            </div>` : ''}
            ${freshness ? `<div style="margin-top:6px; display:flex; align-items:center; gap:6px;">
              <i class="bi bi-broadcast-pin" style="font-size:10px; color:${freshness.stale ? '#EF4444' : '#16A34A'};"></i>
              <span style="font-size:11px; color:${freshness.stale ? '#EF4444' : '#71717A'}; font-weight:500;">Last fix: ${freshness.text}${freshness.stale ? ' · stale' : ''}</span>
            </div>` : ''}
          `;

          card.addEventListener('click', () => focusDriverOnMap(driver.id));
          list.appendChild(card);
        });
      }

      // Pan the map to a driver's marker and open their info window.
      function focusDriverOnMap(driverId) {
        const marker = driverMarkers[driverId];
        if (!marker) return;
        hasUserInteractedWithMap = true;
        map.panTo(marker.getPosition());
        if (map.getZoom() < 15) map.setZoom(15);
        if (activeInfoWindow) activeInfoWindow.close();
        if (marker.infoWindow) {
          marker.infoWindow.open(map, marker);
          activeInfoWindow = marker.infoWindow;
        }
      }

      // Update markers on the map with bearing-based rotation and smooth animation
      function updateDriverMarkers(drivers) {
        const validDrivers = drivers.filter(
          (d) => d.lat != null && d.lng != null &&
                 !isNaN(parseFloat(d.lat)) && !isNaN(parseFloat(d.lng))
        );

        const visibleIds = new Set(validDrivers.map((d) => d.id));

        // Remove markers that are no longer in the active set
        Object.keys(driverMarkers).forEach((driverId) => {
          if (!visibleIds.has(driverId)) {
            driverMarkers[driverId].setMap(null);
            if (driverAnimTimers[driverId]) {
              cancelAnimationFrame(driverAnimTimers[driverId]);
              delete driverAnimTimers[driverId];
            }
            clearDriverRoute(driverId);
            delete driverLastPositions[driverId];
            delete driverBearings[driverId];
          }
        });

        validDrivers.forEach((driver) => {
          const lat       = parseFloat(driver.lat);
          const lng       = parseFloat(driver.lng);
          const driverId  = driver.id;
          const driverName = driver.full_name || driver.name || 'Driver';

          // Determine heading: prefer API heading, fall back to computed bearing
          let bearing = driverBearings[driverId] || 0;
          const apiHeading = parseFloat(driver.heading);
          if (!isNaN(apiHeading) && apiHeading !== 0) {
            bearing = apiHeading;
          } else if (driverLastPositions[driverId]) {
            const last = driverLastPositions[driverId];
            const comp = computeBearing(last.lat, last.lng, lat, lng);
            if (comp !== null) bearing = comp;
          }
          driverBearings[driverId] = bearing;

          const icon = buildDriverIcon(bearing, driver.status);
          // Fade drivers whose last GPS fix is stale so live cars stand out.
          const freshness = formatUpdatedAgo(driver.updated_at);
          const markerOpacity = (freshness && freshness.stale) ? 0.4 : 1.0;

          // Draw / refresh the track to where this driver is headed (on-trip only).
          maybeUpdateDriverRoute(driverId, lat, lng, driver);

          if (driverMarkers[driverId]) {
            const marker  = driverMarkers[driverId];
            const fromPos = marker.getPosition();
            const fromLat = fromPos.lat();
            const fromLng = fromPos.lng();

            // Only animate if position actually changed
            if (fromLat !== lat || fromLng !== lng) {
              marker.setIcon(icon);

              if (driverAnimTimers[driverId]) {
                cancelAnimationFrame(driverAnimTimers[driverId]);
                driverAnimTimers[driverId] = null;
              }

              // Guard against teleports: when a previously-stale driver gets a fresh
              // fix far from its last-known point (or GPS jitters wildly), snap to the
              // new position instead of animating a car "driving" across the city.
              let jumpMeters = 0;
              if (google.maps.geometry && google.maps.geometry.spherical) {
                jumpMeters = google.maps.geometry.spherical.computeDistanceBetween(
                  new google.maps.LatLng(fromLat, fromLng),
                  new google.maps.LatLng(lat, lng)
                );
              }
              if (jumpMeters > TELEPORT_SNAP_METERS) {
                marker.setPosition({ lat, lng });
                if (!marker.getMap()) marker.setMap(map);
                marker.setOpacity(markerOpacity);
                if (marker.infoWindow) marker.infoWindow.setContent(buildDriverInfoContent(driver));
                driverLastPositions[driverId] = { lat, lng };
                return; // skip the glide for this driver this round
              }

              // Glide across the full poll interval with requestAnimationFrame so
              // the car moves continuously (≈60 fps) instead of darting to the new
              // point and freezing until the next poll. A fresh poll cancels this
              // and re-interpolates from the marker's current position.
              const startTs = performance.now();
              const animate = (nowTs) => {
                const f = Math.min((nowTs - startTs) / MARKER_ANIM_MS, 1);
                marker.setPosition({
                  lat: fromLat + (lat - fromLat) * f,
                  lng: fromLng + (lng - fromLng) * f,
                });
                if (f < 1) {
                  driverAnimTimers[driverId] = requestAnimationFrame(animate);
                } else if (driverAnimTimers[driverId]) {
                  driverAnimTimers[driverId] = null;
                }
              };
              driverAnimTimers[driverId] = requestAnimationFrame(animate);
            }

            if (!marker.getMap()) marker.setMap(map);
            marker.setOpacity(markerOpacity);
            if (marker.infoWindow) marker.infoWindow.setContent(buildDriverInfoContent(driver));
          } else {
            const marker = new google.maps.Marker({
              position: { lat, lng },
              map,
              icon,
              title: driverName,
              opacity: markerOpacity,
            });
            const infoWindow = new google.maps.InfoWindow({
              content: buildDriverInfoContent(driver),
              disableAutoPan: false,
            });
            marker.addListener('click', () => {
              if (activeInfoWindow) activeInfoWindow.close();
              infoWindow.open(map, marker);
              activeInfoWindow = infoWindow;
            });
            marker.infoWindow = infoWindow;
            driverMarkers[driverId] = marker;
          }

          driverLastPositions[driverId] = { lat, lng };
        });

        renderDriverCards(validDrivers);
      }

      // Start polling for driver location updates
      function startPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
        }
        updateIntervalId = setInterval(loadDriverLocations, UPDATE_INTERVAL);
      }

      // Stop polling and clean up all animation timers
      function stopPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
          updateIntervalId = null;
        }
        Object.keys(driverAnimTimers).forEach((id) => {
          if (driverAnimTimers[id]) cancelAnimationFrame(driverAnimTimers[id]);
        });
        driverAnimTimers = {};
        Object.keys(driverRoutes).forEach((id) => clearDriverRoute(id));
      }

      // Setup search functionality
      function setupSearch() {
        const searchInput = document.getElementById('driverSearchInput');
        if (!searchInput) return;

        // Search as user types (with debounce)
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            currentSearchQuery = e.target.value;
            applySearchFilter();
          }, 300); // Wait 300ms after user stops typing
        });

        // Clear search on Escape key
        searchInput.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            searchInput.value = '';
            currentSearchQuery = '';
            applySearchFilter();
          }
        });
      }

      // ── View toggle (Table vs Map) ──
      let currentView = 'table';
      let mapInitialized = false;
      let tablePollingId = null;
      let tableSearchQuery = '';

      function switchMapView(view) {
        currentView = view;
        document.getElementById('viewTable').style.display = view === 'table' ? '' : 'none';
        document.getElementById('viewMap').style.display = view === 'map' ? '' : 'none';
        document.getElementById('btnTableView').classList.toggle('is-active', view === 'table');
        document.getElementById('btnMapView').classList.toggle('is-active', view === 'map');

        if (view === 'map' && !mapInitialized) {
          mapInitialized = true;
          initMap();
          setupSearch();
        } else if (view === 'map' && mapInitialized) {
          google.maps.event.trigger(map, 'resize');
        }

        if (view === 'table') {
          loadTableDrivers();
          startTablePolling();
        } else {
          stopTablePolling();
        }
      }

      function startTablePolling() {
        stopTablePolling();
        tablePollingId = setInterval(loadTableDrivers, UPDATE_INTERVAL);
      }
      function stopTablePolling() {
        if (tablePollingId) { clearInterval(tablePollingId); tablePollingId = null; }
      }

      const geocodeCache = {};
      let geocoder = null;

      function reverseGeocode(lat, lng, callback) {
        const key = parseFloat(lat).toFixed(4) + ',' + parseFloat(lng).toFixed(4);
        if (geocodeCache[key]) { callback(geocodeCache[key]); return; }
        if (!geocoder) {
          if (typeof google === 'undefined' || !google.maps) { callback(null); return; }
          geocoder = new google.maps.Geocoder();
        }
        geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, (results, status) => {
          if (status === 'OK' && results && results[0]) {
            geocodeCache[key] = results[0].formatted_address;
            callback(geocodeCache[key]);
          } else {
            callback(null);
          }
        });
      }

      async function loadTableDrivers() {
        try {
          const res = await fetch('api/get_all_active_drivers.php', { cache: 'no-store' });
          if (res.status === 401) { window.location.href = '/'; return; }
          const data = await res.json();
          if (data.success && data.data) {
            const drivers = data.data.filter(d => d.lat != null && d.lng != null);
            renderDriverTable(drivers);
          }
        } catch (e) { console.error('Table driver fetch error:', e); }
      }

      function renderDriverTable(drivers) {
        const tbody = document.getElementById('driverTableBody');
        const countEl = document.getElementById('tableOnlineCountVal');
        if (countEl) countEl.textContent = drivers.length;

        const q = tableSearchQuery.toLowerCase().trim();
        const filtered = q ? drivers.filter(d => {
          const name = (d.full_name || d.name || '').toLowerCase();
          const email = (d.email || '').toLowerCase();
          const phone = (d.phone || '').toLowerCase();
          return name.includes(q) || email.includes(q) || phone.includes(q);
        }) : drivers;

        if (!filtered.length) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No active drivers found</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        filtered.forEach(d => {
          const name = d.full_name || d.name || 'Driver';
          const email = d.email || '—';
          const phone = d.phone || '—';
          const lat = d.lat != null ? parseFloat(d.lat).toFixed(6) : '—';
          const lng = d.lng != null ? parseFloat(d.lng).toFixed(6) : '—';
          const rawStatus = (d.status || 'online').toLowerCase();
          const isBusy = ['on_trip','started','in_progress','trip_started','arrived_at_pickup','driver_arrived','arrived','assigned','accepted','driver_accepted'].includes(rawStatus);
          const statusLabel = isBusy ? 'Busy' : 'Available';
          const dotColor = isBusy ? '#F59E0B' : '#22C55E';
          const dbAddress = d.current_address || d.dest_addr || d.pickup_addr || '';
          const freshness = formatUpdatedAgo(d.updated_at);
          const fixText = freshness ? freshness.text : '—';
          const fixColor = freshness && freshness.stale ? '#EF4444' : '#16A34A';
          const staleTag = freshness && freshness.stale ? ' <span style="color:#EF4444; font-size:0.75rem;">· stale</span>' : '';
          const initials = name.trim().split(/\s+/).map(p => p[0]).slice(0,2).join('').toUpperCase() || 'D';
          const locationCellId = 'loc-' + d.id;

          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>
              <div class="d-flex align-items-center gap-2">
                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px; flex-shrink:0;">${initials}</div>
                <span class="fw-semibold">${name}</span>
              </div>
            </td>
            <td style="color:#71717A; font-size:0.82rem;">${email}</td>
            <td>${phone}</td>
            <td>
              <span class="d-inline-flex align-items-center gap-1">
                <span class="driver-status-dot" style="background:${dotColor};"></span>
                <span style="font-size:0.8rem; font-weight:600; color:${dotColor};">${statusLabel}</span>
              </span>
            </td>
            <td style="font-family:monospace; font-size:0.82rem;">${lat}</td>
            <td style="font-family:monospace; font-size:0.82rem;">${lng}</td>
            <td id="${locationCellId}" style="font-size:0.82rem; max-width:220px; white-space:normal; word-break:break-word;">${dbAddress || '<span style="color:#A1A1AA;">Resolving...</span>'}</td>
            <td>
              <span style="color:${fixColor}; font-weight:600; font-size:0.82rem;">${fixText}</span>${staleTag}
            </td>
          `;
          tbody.appendChild(tr);

          if (!dbAddress && d.lat != null && d.lng != null) {
            reverseGeocode(d.lat, d.lng, (addr) => {
              const cell = document.getElementById(locationCellId);
              if (cell) cell.textContent = addr || (parseFloat(d.lat).toFixed(4) + ', ' + parseFloat(d.lng).toFixed(4));
            });
          }
        });
      }

      // Initialize map when page loads
      document.addEventListener('DOMContentLoaded', () => {
        // Start in table view — map init deferred until user clicks "Live Map"
        loadTableDrivers();
        startTablePolling();

        // Table search
        const tableSearch = document.getElementById('tableSearchInput');
        if (tableSearch) {
          let tst;
          tableSearch.addEventListener('input', e => {
            clearTimeout(tst);
            tst = setTimeout(() => {
              tableSearchQuery = e.target.value;
              loadTableDrivers();
            }, 300);
          });
          tableSearch.addEventListener('keydown', e => {
            if (e.key === 'Escape') { tableSearch.value = ''; tableSearchQuery = ''; loadTableDrivers(); }
          });
        }
      });

      // Clean up on page unload
      window.addEventListener('beforeunload', () => {
        stopPolling();
        stopTablePolling();
      });
    </script>
  </body>
</html>
