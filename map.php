<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-center my-2 gap-3">
  <div class="d-flex gap-3 w-100 w-md-auto justify-content-center justify-content-md-start">
    <a
      href="order.php"
      class="btn glowing-btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
      style="background: #f37a20; color: #fff; border-radius: 4px; box-shadow: 0 0 15px rgba(243, 122, 32, 0.5); min-width: 120px;"
    >
      New Order
    </a>
    <a
      href="map.php"
      class="btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
      style="background: #fff; color: #000; border: 1px solid #000; border-radius: 4px; min-width: 120px;"
    >
      Open Map
    </a>
  </div>
</div>

<div class="container-fluid p-2" style="height: 100vh; display: flex;">

  <div class="col-md-9 p-3 position-relative" style="background-color: #f8f9fa; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <div class="w-100 h-100 rounded-3 overflow-hidden shadow-sm">
      <div id="map" style="width: 100%; height: 100%;"></div>
    </div>

    <div class="position-absolute bottom-0 start-0 m-3">
      <div class="btn-group shadow-sm" role="group">
        <button class="btn btn-light btn-sm rounded-start">Traffic</button>
        <button class="btn btn-light btn-sm rounded-end">Heatmap</button>
      </div>
    </div>
  </div>

  <!-- Filter Panel (Right) -->
  <div class="col-md-3 bg-white p-3 border-start shadow-sm" style="max-height: 100vh; overflow-y: auto; border-radius: 0 12px 12px 0;">
    
    <!-- Search Bar -->
    <div class="input-group mb-3">
      <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
      <input type="text" id="driverSearchInput" class="form-control border-start-0 rounded-end" placeholder="Search for drivers" aria-label="Search">
    </div>

    <!-- Drivers Section -->
    <div class="mb-4">
      <h6 class="fw-bold mb-3">Drivers</h6>
      
      <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
  <span class="text-muted">Busy</span>
  <div class="form-check form-switch">
    <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
    <label class="form-check-label text-muted" for="busyToggle">0</label>
  </div>
</div>
      
      <!-- Available -->
      <div class="d-flex justify-content-between align-items-center p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span class="text-muted">Available</span>
        <div class="form-check form-switch">
           <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
          <label class="form-check-label text-muted" for="availableToggle">1</label>
        </div>
      </div>
    </div>

    <div>
      <h6 class="fw-bold mb-3">Service Types</h6>
      <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center">
              <span class="me-2" style="width: 16px; height: 16px; background-color: #dc3545; border-radius: 2px;"></span>
          <span>Economy</span>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
          <label class="form-check-label text-muted" for="economyToggle">0</label>
        </div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center">
             <span class="me-2" style="width: 16px; height: 16px; background-color: #007bff; border-radius: 2px;"></span>
          <span>Business</span>
        </div>
        <div class="form-check form-switch">
           <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
          <label class="form-check-label text-muted" for="businessToggle">0</label>
        </div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center">
           <span class="me-2" style="width: 16px; height: 16px; background-color: #28a745; border-radius: 2px;"></span>
          <span>Limo</span>
        </div>
        <div class="form-check form-switch">
        <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
          <label class="form-check-label text-muted" for="limoToggle">0</label>
        </div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center p-2 bg-white border rounded" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="d-flex align-items-center">
           <span class="me-2" style="width: 16px; height: 16px; background-color: #212529; border-radius: 2px;"></span>
          <span>Premium Black</span>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input orange-switch" type="checkbox" id="busyToggle" checked>
          <label class="form-check-label text-muted" for="premiumToggle">1</label>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
</div>
    </main>
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
