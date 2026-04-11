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
      let allDrivers = []; // Store all driver data
      let carIcon;
      const UPDATE_INTERVAL = 15000; // Update every 15 seconds
      let updateIntervalId = null;
      let currentSearchQuery = '';
      let activeInfoWindow = null;
      let hasUserInteractedWithMap = false; // Flag: don't auto-fit after user pans/zooms

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

        // Create car icon from SVG
        carIcon = {
          url: 'assets/car.svg',
          scaledSize: new google.maps.Size(40, 40),
          anchor: new google.maps.Point(20, 20),
        };

        // Load drivers and start polling
        loadDriverLocations();
        startPolling();
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

      // Fetch driver locations from API
      async function loadDriverLocations() {
        try {
          const response = await fetch('api/get_drivers.php?limit=200');
          const data = await response.json();

          if (data.success && data.data) {
            // Only keep drivers who are online in their app and have a live location
            allDrivers = data.data.filter(
              (d) => isDriverOnline(d) && d.current_lat != null && d.current_lng != null
            );
            applySearchFilter();
          } else {
            console.error('Error loading drivers:', data.error || 'Unknown error');
          }
        } catch (error) {
          console.error('Error fetching driver locations:', error);
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
        const status = (driver.status || driver.availability || 'Online').toString();
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
          const statusRaw = (driver.status || driver.availability || 'Online').toString().toLowerCase();
          const isBusy = ['busy', 'on_trip', 'on trip', 'engaged'].includes(statusRaw);
          const dotColor = isBusy ? '#F59E0B' : '#22C55E';
          const dotBg = isBusy ? '#FEF3C7' : '#F0FDF4';
          const dotLabel = isBusy ? 'Busy' : 'Online';
          const initials = name.trim().split(/\s+/).map((p) => p[0]).slice(0, 2).join('').toUpperCase() || 'D';

          const locLine = (driver.current_address || '')
            || (driver.current_lat != null && driver.current_lng != null
                ? `${parseFloat(driver.current_lat).toFixed(4)}, ${parseFloat(driver.current_lng).toFixed(4)}`
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

      // Update markers on the map
      function updateDriverMarkers(drivers) {
        const validDrivers = drivers.filter(
          (driver) =>
            driver.current_lat != null &&
            driver.current_lng != null &&
            !isNaN(parseFloat(driver.current_lat)) &&
            !isNaN(parseFloat(driver.current_lng))
        );

        const visibleDriverIds = new Set(validDrivers.map((d) => d.id));

        // Remove / hide markers that should not be visible any more
        Object.keys(driverMarkers).forEach((driverId) => {
          if (!visibleDriverIds.has(driverId)) {
            driverMarkers[driverId].setMap(null);
          }
        });

        // Update or create markers for each driver
        validDrivers.forEach((driver) => {
          const lat = parseFloat(driver.current_lat);
          const lng = parseFloat(driver.current_lng);
          const position = { lat, lng };

          const driverName = driver.full_name || driver.name || 'Driver';
          const driverId = driver.id;

          if (driverMarkers[driverId]) {
            // Update existing marker position silently (no re-drop animation)
            driverMarkers[driverId].setPosition(position);
            if (!driverMarkers[driverId].getMap()) {
              driverMarkers[driverId].setMap(map);
            }
            // Refresh info window content so data stays in sync
            if (driverMarkers[driverId].infoWindow) {
              driverMarkers[driverId].infoWindow.setContent(buildDriverInfoContent(driver));
            }
          } else {
            const marker = new google.maps.Marker({
              position: position,
              map: map,
              icon: carIcon,
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
        });

        // Refresh sidebar cards with the live list
        renderDriverCards(validDrivers);

        // Map always boots centered on Dublin. We intentionally do NOT
        // auto-fit to driver bounds — the user pans/zooms freely from there,
        // and clicking a sidebar card still focuses that specific driver.
      }

      // Start polling for driver location updates
      function startPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
        }
        updateIntervalId = setInterval(loadDriverLocations, UPDATE_INTERVAL);
      }

      // Stop polling
      function stopPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
          updateIntervalId = null;
        }
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
