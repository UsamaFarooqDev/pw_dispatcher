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

    <div class="d-flex flex-column rounded-3 overflow-hidden" style="width:270px; flex-shrink:0; background:#fff; border:1.5px solid #EBEBEB; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow-y:auto;">
      <div class="p-3 d-flex flex-column gap-4">

        <div class="position-relative">
          <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
          <input type="text" id="driverSearchInput" placeholder="Search drivers…" class="form-control"
            style="height:36px; border:1.5px solid #EBEBEB; border-radius:8px; padding-left:32px; font-size:0.8125rem; background:#FAFAFA; color:#18181B;"
            onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
        </div>

        <div>
          <div class="mb-2 pb-1" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.04em; text-transform:uppercase; color:#A1A1AA;">Drivers</span>
          </div>

          <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mb-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
            <div class="d-flex align-items-center gap-2">
              <span style="width:8px; height:8px; border-radius:50%; background:#EF4444; flex-shrink:0;"></span>
              <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Busy</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span id="busyCount" style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">0</span>
              <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                <input class="form-check-input m-0 orange-switch" type="checkbox" id="busyToggle" checked style="width:34px; height:18px; cursor:pointer;" />
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
            <div class="d-flex align-items-center gap-2">
              <span style="width:8px; height:8px; border-radius:50%; background:#22C55E; flex-shrink:0;"></span>
              <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Available</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span id="availableCount" style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">1</span>
              <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                <input class="form-check-input m-0 orange-switch" type="checkbox" id="availableToggle" checked style="width:34px; height:18px; cursor:pointer;" />
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="mb-2 pb-1" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.04em; text-transform:uppercase; color:#A1A1AA;">Service Types</span>
          </div>

          <div class="d-flex flex-column gap-2">

            <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px; height:10px; background:#EF4444; border-radius:3px; flex-shrink:0;"></span>
                <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Economy</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">0</span>
                <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                  <input class="form-check-input m-0 orange-switch" type="checkbox" id="economyToggle" checked style="width:34px; height:18px; cursor:pointer;" />
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px; height:10px; background:#3B82F6; border-radius:3px; flex-shrink:0;"></span>
                <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Business</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">0</span>
                <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                  <input class="form-check-input m-0 orange-switch" type="checkbox" id="businessToggle" checked style="width:34px; height:18px; cursor:pointer;" />
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px; height:10px; background:#22C55E; border-radius:3px; flex-shrink:0;"></span>
                <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Limo</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">0</span>
                <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                  <input class="form-check-input m-0 orange-switch" type="checkbox" id="limoToggle" checked style="width:34px; height:18px; cursor:pointer;" />
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between rounded-2 px-3 py-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px; height:10px; background:#18181B; border-radius:3px; flex-shrink:0;"></span>
                <span style="font-size:0.845rem; color:#18181B; font-weight:500;">Premium Black</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="font-size:0.78rem; color:#71717A; font-weight:600; min-width:16px; text-align:right;">1</span>
                <div class="form-check form-switch m-0 p-0" style="min-height:auto;">
                  <input class="form-check-input m-0 orange-switch" type="checkbox" id="premiumToggle" checked style="width:34px; height:18px; cursor:pointer;" />
                </div>
              </div>
            </div>

          </div>
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
      const UPDATE_INTERVAL = 10000; // Update every 10 seconds
      let updateIntervalId = null;
      let currentSearchQuery = '';

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

        // Default center (Dublin coordinates - adjust as needed)
        const defaultCenter = { lat: 53.349805, lng: -6.26031 };

        map = new google.maps.Map(mapElement, {
          center: defaultCenter,
          zoom: 12,
          mapTypeControl: true,
          streetViewControl: true,
          fullscreenControl: true,
        });

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

      // Fetch driver locations from API
      async function loadDriverLocations() {
        try {
          const response = await fetch('api/get_drivers.php');
          const data = await response.json();

          if (data.success && data.data) {
            allDrivers = data.data; // Store all drivers
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

      // Update markers on the map
      function updateDriverMarkers(drivers) {
        const validDrivers = drivers.filter(
          (driver) =>
            driver.current_lat != null &&
            driver.current_lng != null &&
            !isNaN(parseFloat(driver.current_lat)) &&
            !isNaN(parseFloat(driver.current_lng))
        );

        // Get all driver IDs from the current dataset (for cleanup)
        const allDriverIds = new Set(drivers.map((d) => d.id));
        const visibleDriverIds = new Set(validDrivers.map((d) => d.id));

        // Remove markers for drivers that no longer exist in the database
        Object.keys(driverMarkers).forEach((driverId) => {
          if (!allDriverIds.has(driverId)) {
            driverMarkers[driverId].setMap(null);
            delete driverMarkers[driverId];
          }
        });

        // Hide markers for drivers that don't match search or don't have valid locations
        Object.keys(driverMarkers).forEach((driverId) => {
          if (!visibleDriverIds.has(driverId)) {
            // Hide marker if it exists but shouldn't be shown
            driverMarkers[driverId].setMap(null);
            // Don't delete it, just hide it so we can show it again later
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
            // Update existing marker position and show it
            driverMarkers[driverId].setPosition(position);
            driverMarkers[driverId].setMap(map);
          } else {
            // Create new marker
            const marker = new google.maps.Marker({
              position: position,
              map: map,
              icon: carIcon,
              title: driverName,
              animation: google.maps.Animation.DROP,
            });

            // Create info window with driver details
            const infoWindow = new google.maps.InfoWindow({
              content: `
                <div style="padding: 8px;">
                  <strong>${driverName}</strong><br>
                  ${driver.phone ? `Phone: ${driver.phone}<br>` : ''}
                  ${driver.vehicle_number ? `Vehicle: ${driver.vehicle_number}<br>` : ''}
                  ${driver.status ? `Status: ${driver.status}` : ''}
                </div>
              `,
            });

            // Add click listener to show info window
            marker.addListener('click', () => {
              // Close other info windows
              Object.values(driverMarkers).forEach((m) => {
                if (m.infoWindow) {
                  m.infoWindow.close();
                }
              });
              infoWindow.open(map, marker);
            });

            marker.infoWindow = infoWindow;
            driverMarkers[driverId] = marker;
          }
        });

        // Update map bounds to show all visible drivers if there are any
        if (validDrivers.length > 0) {
          const bounds = new google.maps.LatLngBounds();
          validDrivers.forEach((driver) => {
            bounds.extend({
              lat: parseFloat(driver.current_lat),
              lng: parseFloat(driver.current_lng),
            });
          });
          // Fit bounds to show all visible drivers
          if (validDrivers.length > 0) {
            map.fitBounds(bounds);
          }
        }
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
