<?php
session_start();

// Get ride ID from URL path (localhost/preorder/{ride.id})
$rideId = null;

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
if ($path) {
    // Remove leading slash and 'preorder/' prefix
    $pathParts = explode('/', trim($path, '/'));
    // Find 'preorder' in the path and get the next part
    $preorderIndex = array_search('preorder', $pathParts);
    if ($preorderIndex !== false && isset($pathParts[$preorderIndex + 1])) {
        $rideId = $pathParts[$preorderIndex + 1];
    }
}

if (!$rideId) {
    header('Location: ../preorder.php');
    exit;
}

require('../modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="/"> 
  </head>
  <body>

   <?php require_once '../modules/navbar.php'; ?>

    <?php @require('../modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
<div class="container-fluid p-2" style="height: calc(100vh - 80px); display: flex;">

  <div class="col-12 p-3 position-relative" style="background-color: #f8f9fa; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
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
</div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9ea0A-mjnD5iHfT9X8Dn5YYH4_KZopLI&libraries=geometry" async defer></script>
    <script>
      // Global variables
      let map;
      let driverMarker = null;
      let pickupMarker = null;
      let destinationMarker = null;
      let directionsRenderer = null;
      let carIcon;
      let rideId = '<?php echo htmlspecialchars($rideId, ENT_QUOTES, 'UTF-8'); ?>';
      let driverId = null;
      let rideData = null;
      const UPDATE_INTERVAL = 10000; // Update every 10 seconds
      let updateIntervalId = null;

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

        // Default center (Dublin coordinates)
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
          url: '../assets/car.svg',
          scaledSize: new google.maps.Size(40, 40),
          anchor: new google.maps.Point(20, 20),
        };

        // Initialize directions renderer
        directionsRenderer = new google.maps.DirectionsRenderer({
          map: map,
          suppressMarkers: true, // We'll add our own markers
        });

        // Load ride data and initialize map
        loadRideData();
        startPolling();
      }

      // Fetch ride data from API
      async function loadRideData() {
        try {
          const response = await fetch(`../api/get_ride.php?id=${encodeURIComponent(rideId)}`);
          if (!response.ok) {
            throw new Error('Failed to fetch ride data');
          }

          const result = await response.json();
          if (!result.success || !result.data) {
            throw new Error(result.error || 'Ride not found');
          }

          rideData = result.data;
          driverId = rideData.driver_id;

          // Set up markers and route
          setupMapMarkers();

          // Load driver location if driver is assigned (initial load with bounds adjustment)
          if (driverId) {
            loadDriverLocation(true);
          }
        } catch (error) {
          console.error('Error loading ride data:', error);
          alert('Error loading ride data. Please try again.');
        }
      }

      // Set up map markers for pickup and destination
      function setupMapMarkers() {
        if (!rideData) return;

        const pickupLat = parseFloat(rideData.pickup_lat);
        const pickupLng = parseFloat(rideData.pickup_lng);
        const destLat = parseFloat(rideData.dest_lat);
        const destLng = parseFloat(rideData.dest_lng);

        // Create pickup marker (Point A)
        if (!isNaN(pickupLat) && !isNaN(pickupLng)) {
          const pickupPosition = { lat: pickupLat, lng: pickupLng };
          
          pickupMarker = new google.maps.Marker({
            position: pickupPosition,
            map: map,
            label: 'A',
            title: 'Pickup Location: ' + (rideData.pickup_addr || 'N/A'),
          });

          const pickupInfoWindow = new google.maps.InfoWindow({
            content: `
              <div style="padding: 8px;">
                <strong>Pickup Location</strong><br>
                ${rideData.pickup_addr || 'N/A'}
              </div>
            `,
          });

          pickupMarker.addListener('click', () => {
            pickupInfoWindow.open(map, pickupMarker);
          });
        }

        // Create destination marker (Point B)
        if (!isNaN(destLat) && !isNaN(destLng)) {
          const destPosition = { lat: destLat, lng: destLng };
          
          destinationMarker = new google.maps.Marker({
            position: destPosition,
            map: map,
            label: 'B',
            title: 'Destination: ' + (rideData.dest_addr || 'N/A'),
          });

          const destInfoWindow = new google.maps.InfoWindow({
            content: `
              <div style="padding: 8px;">
                <strong>Destination</strong><br>
                ${rideData.dest_addr || 'N/A'}
              </div>
            `,
          });

          destinationMarker.addListener('click', () => {
            destInfoWindow.open(map, destinationMarker);
          });
        }

        // Draw route between pickup and destination
        if (pickupMarker && destinationMarker) {
          const directionsService = new google.maps.DirectionsService();
          directionsService.route({
            origin: { lat: pickupLat, lng: pickupLng },
            destination: { lat: destLat, lng: destLng },
            travelMode: google.maps.TravelMode.DRIVING,
          }, (result, status) => {
            if (status === google.maps.DirectionsStatus.OK) {
              directionsRenderer.setDirections(result);
            } else {
              console.error('Directions request failed:', status);
            }
          });

          // Fit bounds to show both pickup and destination
          const bounds = new google.maps.LatLngBounds();
          bounds.extend({ lat: pickupLat, lng: pickupLng });
          bounds.extend({ lat: destLat, lng: destLng });
          map.fitBounds(bounds);
        } else if (pickupMarker) {
          map.setCenter({ lat: pickupLat, lng: pickupLng });
          map.setZoom(14);
        } else if (destinationMarker) {
          map.setCenter({ lat: destLat, lng: destLng });
          map.setZoom(14);
        }
      }

      // Fetch driver location from API
      async function loadDriverLocation(isInitialLoad = false) {
        if (!driverId) return;

        try {
          const response = await fetch('../api/get_drivers.php');
          if (!response.ok) {
            throw new Error('Failed to fetch driver data');
          }

          const result = await response.json();
          if (!result.success || !result.data) {
            return;
          }

          // Find the driver by ID
          const driver = result.data.find(d => d.id === driverId);
          if (!driver) {
            console.log('Driver not found');
            return;
          }

          const driverLat = parseFloat(driver.current_lat);
          const driverLng = parseFloat(driver.current_lng);

          if (isNaN(driverLat) || isNaN(driverLng)) {
            console.log('Driver location not available');
            return;
          }

          const driverPosition = { lat: driverLat, lng: driverLng };
          const driverName = driver.full_name || driver.name || 'Driver';

          // Update or create driver marker
          if (driverMarker) {
            // Smoothly animate the marker position change
            animateMarker(driverMarker, driverPosition);
          } else {
            driverMarker = new google.maps.Marker({
              position: driverPosition,
              map: map,
              icon: carIcon,
              title: driverName,
            });

            const driverInfoWindow = new google.maps.InfoWindow({
              content: `
                <div style="padding: 8px;">
                  <strong>${driverName}</strong><br>
                  ${driver.phone ? `Phone: ${driver.phone}<br>` : ''}
                  ${driver.vehicle_number ? `Vehicle: ${driver.vehicle_number}<br>` : ''}
                  ${driver.status ? `Status: ${driver.status}` : ''}
                </div>
              `,
            });

            driverMarker.addListener('click', () => {
              driverInfoWindow.open(map, driverMarker);
            });
          }

          // Only adjust map bounds on initial load, not on updates
          if (isInitialLoad) {
            if (pickupMarker || destinationMarker) {
              const bounds = new google.maps.LatLngBounds();
              if (pickupMarker) bounds.extend(pickupMarker.getPosition());
              if (destinationMarker) bounds.extend(destinationMarker.getPosition());
              bounds.extend(driverPosition);
              map.fitBounds(bounds);
            } else {
              map.setCenter(driverPosition);
              map.setZoom(14);
            }
          }
        } catch (error) {
          console.error('Error loading driver location:', error);
        }
      }

      // Animate marker movement smoothly
      function animateMarker(marker, newPosition) {
        const currentPosition = marker.getPosition();
        const startLat = currentPosition.lat();
        const startLng = currentPosition.lng();
        const endLat = newPosition.lat;
        const endLng = newPosition.lng;

        // Calculate distance to determine if we should animate
        const distance = google.maps.geometry.spherical.computeDistanceBetween(
          currentPosition,
          new google.maps.LatLng(newPosition)
        );

        // Only animate if the distance is significant (more than 10 meters)
        if (distance < 10) {
          marker.setPosition(newPosition);
          return;
        }

        const duration = 1000; // Animation duration in milliseconds
        const startTime = Date.now();

        function animate() {
          const elapsed = Date.now() - startTime;
          const progress = Math.min(elapsed / duration, 1);

          // Easing function for smooth animation
          const easeProgress = progress < 0.5
            ? 2 * progress * progress
            : 1 - Math.pow(-2 * progress + 2, 2) / 2;

          const lat = startLat + (endLat - startLat) * easeProgress;
          const lng = startLng + (endLng - startLng) * easeProgress;

          marker.setPosition({ lat, lng });

          if (progress < 1) {
            requestAnimationFrame(animate);
          }
        }

        animate();
      }

      // Start polling for driver location updates
      function startPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
        }
        updateIntervalId = setInterval(() => {
          if (driverId) {
            // Only update driver position, don't reload entire ride data
            loadDriverLocation(false);
          }
        }, UPDATE_INTERVAL);
      }

      // Stop polling
      function stopPolling() {
        if (updateIntervalId) {
          clearInterval(updateIntervalId);
          updateIntervalId = null;
        }
      }

      // Initialize map when page loads
      document.addEventListener('DOMContentLoaded', () => {
        initMap();
      });

      // Clean up on page unload
      window.addEventListener('beforeunload', () => {
        stopPolling();
      });
    </script>
  </body>
</html>
