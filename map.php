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

  <div class="d-flex gap-3 mt-4" style="height:calc(100vh - 140px);">

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

</main>

<style>
  .orange-switch:checked {
    background-color: #f37a20 !important;
    border-color: #f37a20 !important;
  }
  .orange-switch:focus {
    box-shadow: 0 0 0 3px rgba(243,122,32,0.15) !important;
  }
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
      let allDrivers = [];
      const UPDATE_INTERVAL = 5000; // 5-second polling for live feel
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
              clearInterval(driverAnimTimers[driverId]);
              delete driverAnimTimers[driverId];
            }
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

          if (driverMarkers[driverId]) {
            const marker  = driverMarkers[driverId];
            const fromPos = marker.getPosition();
            const fromLat = fromPos.lat();
            const fromLng = fromPos.lng();

            // Only animate if position actually changed
            if (fromLat !== lat || fromLng !== lng) {
              marker.setIcon(icon);

              if (driverAnimTimers[driverId]) {
                clearInterval(driverAnimTimers[driverId]);
                driverAnimTimers[driverId] = null;
              }
              let step = 0;
              const STEPS = 20; // 20 × 50 ms = 1 s
              // Capture timer ID locally so the callback always clears its own
              // timer even if a new poll replaces driverAnimTimers[driverId].
              const animTimer = setInterval(() => {
                step++;
                const f = step / STEPS;
                marker.setPosition({
                  lat: fromLat + (lat - fromLat) * f,
                  lng: fromLng + (lng - fromLng) * f,
                });
                if (step >= STEPS) {
                  clearInterval(animTimer);
                  if (driverAnimTimers[driverId] === animTimer) driverAnimTimers[driverId] = null;
                }
              }, 50);
              driverAnimTimers[driverId] = animTimer;
            }

            if (!marker.getMap()) marker.setMap(map);
            if (marker.infoWindow) marker.infoWindow.setContent(buildDriverInfoContent(driver));
          } else {
            const marker = new google.maps.Marker({
              position: { lat, lng },
              map,
              icon,
              title: driverName,
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
          if (driverAnimTimers[id]) clearInterval(driverAnimTimers[id]);
        });
        driverAnimTimers = {};
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

      // Initialize map when page loads
      document.addEventListener('DOMContentLoaded', () => {
        initMap();
        setupSearch();
      });

      // Clean up on page unload
      window.addEventListener('beforeunload', () => {
        stopPolling();
      });
    </script>
  </body>
</html>
