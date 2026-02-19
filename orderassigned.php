<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
      .text-orange {
        color: #fd7e14;
      }

      .bg-light {
        background-color: #f8f9fa !important;
      }

      .editable {
        background-color: #fff !important;
        border: 1px solid #dee2e6 !important;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
      }

      .readonly {
        background-color: #f8f9fa !important;
        border: 1px solid transparent;
      }
    </style>
  </head>
  <body>
    
    <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
      <div
        class="d-flex flex-column flex-md-row justify-content-md-between align-items-center my-2 gap-3 mb-4"
      >
        <div
          class="d-flex gap-3 w-100 w-md-auto justify-content-center justify-content-md-start"
        >
          <a
            href="order.php"
            class="btn glowing-btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
            style="
              background: #f37a20;
              color: #fff;
              border-radius: 4px;
              box-shadow: 0 0 15px rgba(243, 122, 32, 0.5);
              min-width: 120px;
            "
          >
            New Order
          </a>
          <a
            href="map.php"
            class="btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
            style="
              background: #fff;
              color: #000;
              border: 1px solid #000;
              border-radius: 4px;
              min-width: 120px;
            "
          >
            Open Map
          </a>
        </div>
      </div>

      <div style="background-color: #f8f9fa">
        <div class="row g-4">
          <div class="col-md-6">
            <div
              id="customerDetailsSection"
              class="bg-white p-4 mb-4 rounded shadow-sm"
            >
              <h6 class="fw-bold mb-3">Customer Details</h6>
                <div class="position-relative mb-3">
              <label class="form-label fw-semibold small">Passenger Name</label>
              <input
                type="text"
                class="form-control text-muted"
                placeholder="Type to search passenger"
                id="customerNameInput"
                autocomplete="off"
              />
              <input type="hidden" id="customerId" />
              <div
                id="customerSuggestions"
                class="list-group position-absolute w-100"
                style="z-index: 10; max-height: 200px; overflow-y: auto; display: none;"
              ></div>
            </div>

              <div class="mb-3">
  <label class="form-label fw-semibold small">Phone Number</label>

  <div class="d-flex gap-2">
    <!-- Static Country Code -->
    <input
      type="text"
      class="form-control"
      value="+353"
      readonly
      style="max-width: 90px; background-color: #f8f9fa;"
    />

    <!-- Phone Number Input -->
    <input
      type="tel"
      class="form-control"
      id="phoneNumber"
      placeholder="Enter phone number"
    />
  </div>
</div>

<div class="mb-3">
  <label class="form-label fw-semibold small">Service Type</label>
  <select class="form-select" id="serviceType">
    <option value="Economy">Economy</option>
    <option value="Economy XL">Economy XL</option>
    <option value="Business">Business</option>
    <option value="Business Plus">Business Plus</option>
    <option value="Limousine">Limousine</option>
    <option value="Wheelchair accessible">Wheelchair accessible</option>
  </select>
</div>

<div class="row g-2">
  <div class="col-md-6">
    <label class="form-label fw-semibold small">Date</label>
    <input
      type="date"
      class="form-control"
      id="rideDate"
    />
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold small">Time</label>
    <input
      type="time"
      class="form-control"
      id="rideTime"
    />
  </div>
</div>
</div>

            <!-- Ride Details -->
<div id="rideDetailsSection" class="bg-white p-4 rounded shadow-sm">
  <h6 class="fw-bold mb-3">Ride Details</h6>

  <!-- Pickup -->
  <div class="mb-3">
    <label class="form-label fw-semibold small">Pickup</label>
    <div class="d-flex align-items-center">
      <i class="bi bi-geo-alt-fill text-orange me-2"></i>
      <input
        type="text"
        class="form-control"
        id="pickupLocation"
        placeholder="Enter pickup location"
      />
    </div>
  </div>

  <!-- Drop Off -->
  <div class="mb-3">
    <label class="form-label fw-semibold small">Drop Off</label>
    <div class="d-flex align-items-center">
      <i class="bi bi-geo-alt-fill text-danger me-2"></i>
      <input
        type="text"
        class="form-control"
        id="dropoffLocation"
        placeholder="Enter drop-off location"
      />
    </div>
  </div>

  <!-- Estimated Fare & Time -->
  <div class="row g-2 mb-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold small">Estimated Fare</label>
      <input
        type="text"
        class="form-control"
        id="estimatedFare"
        placeholder="Auto calculated"
        readonly
      />
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold small">Estimated Time</label>
      <input
        type="text"
        class="form-control"
        id="estimatedTime"
        placeholder="Auto calculated"
        readonly
      />
    </div>
  </div>

  <!-- Distance -->
  <div class="mb-3">
    <label class="form-label fw-semibold small">Distance</label>
    <input
      type="text"
      class="form-control"
      id="distance"
      placeholder="Auto calculated"
      readonly
    />
  </div>

  <!-- Extras -->
  <div class="mb-3">
    <label class="form-label fw-semibold small">Extras</label>
    <div class="d-flex flex-wrap gap-2">
      <div class="form-check">
        <input
          class="form-check-input"
          type="checkbox"
          id="creditCard"
        />
        <label class="form-check-label" for="creditCard">
          Accept Credit Card
        </label>
      </div>

      <div class="form-check">
        <input
          class="form-check-input"
          type="checkbox"
          id="extraLuggage"
        />
        <label class="form-check-label" for="extraLuggage">
          Extra Luggage Space
        </label>
      </div>

        <div class="form-check">
        <input
          class="form-check-input"
          type="checkbox"
          id="extraLuggage"
        />
        <label class="form-check-label" for="extraLuggage">
          Pets Allowed
        </label>
      </div>

        <div class="form-check">
        <input
          class="form-check-input"
          type="checkbox"
          id="extraLuggage"
        />
        <label class="form-check-label" for="extraLuggage">
          Delivery
        </label>
      </div>
    </div>
  </div>

  <!-- Action Button -->
  <div class="d-flex">
    <button
      class="btn w-100"
      id="assignDriverBtn"
      style="background: #f37a20; color: #fff; border-radius: 4px"
    >
      Assign Driver
    </button>
  </div>
</div>
          </div>

          <div class="col-md-6">
           <div
    id="driverAssignSection"
    class="bg-white p-4 mb-4 rounded shadow-sm"
  >
    <h6 class="fw-bold mb-3">Assign Driver</h6>

    <div class="mb-3">
      <label class="form-label fw-semibold small">
        Select Driver
      </label>

      <select class="form-select" id="driverSelect">
        <option selected disabled>
          Select a driver from the list
        </option>
      </select>
    </div>
  </div>

            <div
              class="bg-white rounded shadow-sm overflow-hidden"
              style="height: 400px; position: relative"
            >
              <div id="map" style="height: 100%; width: 100%; border-radius: 8px;"></div>
            </div>
          </div>
        </div>
            </div>
          </div>
        </div>
          </div>
        </div>
      </div>

    </main>

<div
  class="modal fade"
  id="driverAssignedModal"
  tabindex="-1"
  aria-hidden="true"
  data-bs-backdrop="static"
>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 p-4 border-0 shadow">
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i
            class="bi bi-check-circle-fill text-success"
            style="font-size: 2.8rem"
          ></i>
        </div>
        <h3 class="fw-bold mb-2">Driver Assigned</h3>
        <p class="text-muted mb-0">
          The driver has been assigned successfully.
        </p>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-3">
        <button
          type="button"
          class="btn fw-semibold rounded-2 px-4"
          style="background: #f37a20; color: white"
          data-bs-dismiss="modal"
          id="goToPreorderBtn"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9ea0A-mjnD5iHfT9X8Dn5YYH4_KZopLI&libraries=places,geometry" async defer></script>
   <script>
// Global variables for Google Maps
let map, directionsService, directionsRenderer;
let currentRideId = null;
let currentDistance = null;
let currentDuration = null;
let currentFare = null;

// Wait for Google Maps API to load
function initGoogleMaps() {
  if (typeof google === 'undefined' || !google.maps) {
    console.error('Google Maps API not loaded');
    return;
  }

  // Initialize map
  const mapElement = document.getElementById('map');
  if (!mapElement) {
    console.error('Map element not found');
    return;
  }

  map = new google.maps.Map(mapElement, {
    center: { lat: 53.349805, lng: -6.26031 }, // Dublin default
    zoom: 13,
  });

  directionsService = new google.maps.DirectionsService();
  directionsRenderer = new google.maps.DirectionsRenderer();
  directionsRenderer.setMap(map);
}

document.addEventListener('DOMContentLoaded', async () => {

  /* Make all readonly inputs editable */
  document.querySelectorAll('input[readonly]').forEach(input => {
    input.removeAttribute('readonly');
    input.classList.remove('bg-light');
    input.classList.add('editable');
  });

  /* Make contenteditable fields editable */
  document.querySelectorAll('.editable-field').forEach(field => {
    field.setAttribute('contenteditable', 'true');
    field.classList.remove('bg-light');
    field.classList.add('editable');
  });

  /* Show editable customer fields */
  const phoneReadonly = document.getElementById('phoneReadonly');
  const phoneEditable = document.getElementById('phoneEditable');
  if (phoneReadonly) phoneReadonly.style.display = 'none';
  if (phoneEditable) phoneEditable.style.display = 'flex';

  const serviceReadonly = document.getElementById('serviceReadonly');
  const serviceEditable = document.getElementById('serviceEditable');
  if (serviceReadonly) serviceReadonly.style.display = 'none';
  if (serviceEditable) serviceEditable.style.display = 'block';

  const dateReadonly = document.getElementById('dateReadonly');
  const dateEditable = document.getElementById('dateEditable');
  if (dateReadonly) dateReadonly.style.display = 'none';
  if (dateEditable) dateEditable.style.display = 'block';

  const timeReadonly = document.getElementById('timeReadonly');
  const timeEditable = document.getElementById('timeEditable');
  if (timeReadonly) timeReadonly.style.display = 'none';
  if (timeEditable) timeEditable.style.display = 'block';

  /* Enable extras */
  const creditCard = document.getElementById('creditCard');
  const extraLuggage = document.getElementById('extraLuggage');
  if (creditCard) creditCard.disabled = false;
  if (extraLuggage) extraLuggage.disabled = false;

  /* Driver fields editable */
  const driverServiceReadonly = document.getElementById('driverServiceReadonly');
  const driverServiceEditable = document.getElementById('driverServiceEditable');
  if (driverServiceReadonly) driverServiceReadonly.style.display = 'none';
  if (driverServiceEditable) driverServiceEditable.style.display = 'block';

  const driverPhoneReadonly = document.getElementById('driverPhoneReadonly');
  const driverPhoneEditable = document.getElementById('driverPhoneEditable');
  if (driverPhoneReadonly) driverPhoneReadonly.style.display = 'none';
  if (driverPhoneEditable) driverPhoneEditable.style.display = 'block';

  /* Optional: hide unnecessary buttons */
  document.getElementById('assignToMeBtn')?.remove();
  document.getElementById('goBackBtn')?.remove();

  // Fetch and prefill ride data if ID is present in URL
  const urlParams = new URLSearchParams(window.location.search);
  const rideId = urlParams.get('id');

  if (rideId) {
    try {
      const response = await fetch(`api/get_ride.php?id=${encodeURIComponent(rideId)}`);
      if (!response.ok) {
        throw new Error('Failed to fetch ride data');
      }

      const result = await response.json();
      if (result.success && result.data) {
        const ride = result.data;
        
        // Prefill Customer Details
        const customerName = document.getElementById('customerName');
        if (customerName && ride.passenger_name) {
          customerName.textContent = ride.passenger_name;
        }

        const phoneNumber = document.getElementById('phoneNumber');
        if (phoneNumber && ride.passenger_phone) {
          // Remove country code if present, as it's already in the static field
          let phone = ride.passenger_phone;
          if (phone.startsWith('+353')) {
            phone = phone.substring(4).trim();
          } else if (phone.startsWith('353')) {
            phone = phone.substring(3).trim();
          }
          phoneNumber.value = phone;
        }

        const serviceType = document.getElementById('serviceType');
        if (serviceType && (ride.ride_type || ride.service_type)) {
          serviceType.value = ride.ride_type || ride.service_type;
        }

        // Prefill Date and Time from created_at
        if (ride.created_at) {
          const rideDate = new Date(ride.created_at);
          
          const dateInput = document.getElementById('rideDate');
          if (dateInput) {
            const dateStr = rideDate.toISOString().split('T')[0];
            dateInput.value = dateStr;
          }

          const timeInput = document.getElementById('rideTime');
          if (timeInput) {
            const hours = String(rideDate.getHours()).padStart(2, '0');
            const minutes = String(rideDate.getMinutes()).padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
          }
        }

        // Prefill Pickup and Drop-off locations
        const pickupLocation = document.getElementById('pickupLocation');
        if (pickupLocation) {
          pickupLocation.value = ride.pickup_addr || ride.actual_start_addr || '';
        }

        const dropoffLocation = document.getElementById('dropoffLocation');
        if (dropoffLocation) {
          dropoffLocation.value = ride.dest_addr || ride.actual_end_addr || '';
        }

        // Store ride ID for assignment
        currentRideId = rideId;

        // Calculate route if both locations are available
        if (pickupLocation && dropoffLocation && pickupLocation.value && dropoffLocation.value) {
          // Wait a bit for map to initialize, then calculate route
          setTimeout(() => {
            calculateRouteAndFare(pickupLocation.value, dropoffLocation.value);
          }, 1000);
        }

        // Prefill Fare Details
        const estimatedFare = document.getElementById('estimatedFare');
        if (estimatedFare) {
          const fare = ride.fare_eur || ride.estimate_fare || null;
          if (fare !== null) {
            estimatedFare.value = `€${parseFloat(fare).toFixed(2)}`;
          }
        }

        // Prefill Distance if available
        const distance = document.getElementById('distance');
        if (distance && ride.distance_km) {
          distance.value = `${ride.distance_km} km`;
        }

        // Prefill Estimated Time if available
        const estimatedTime = document.getElementById('estimatedTime');
        if (estimatedTime && (ride.duration_min || ride.estimated_duration_minutes)) {
          const minutes = ride.duration_min || ride.estimated_duration_minutes;
          const hours = Math.floor(minutes / 60);
          const mins = minutes % 60;
          if (hours > 0) {
            estimatedTime.value = `${hours}h ${mins}m`;
          } else {
            estimatedTime.value = `${mins}m`;
          }
        }
      }
    } catch (error) {
      console.error('Error loading ride data:', error);
    }
  }

  // Initialize Google Maps after a short delay to ensure API is loaded
  setTimeout(() => {
    initGoogleMaps();
  }, 500);

  // Load drivers
  loadDrivers();

  // Setup event listeners for route calculation
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');
  const rideDateInput = document.getElementById('rideDate');
  const rideTimeInput = document.getElementById('rideTime');

  // Recalculate fare when date/time changes
  if (rideDateInput && rideTimeInput) {
    rideDateInput.addEventListener('change', () => {
      if (pickupInput && dropoffInput && pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
    
    rideTimeInput.addEventListener('change', () => {
      if (pickupInput && dropoffInput && pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
  }

  if (pickupInput && dropoffInput) {
    // Use Google Places Autocomplete if available
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
      const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
      const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);
      
      pickupAutocomplete.addListener('place_changed', () => {
        if (dropoffInput.value) {
          calculateRouteAndFare(pickupInput.value, dropoffInput.value);
        }
      });
      
      dropoffAutocomplete.addListener('place_changed', () => {
        if (pickupInput.value) {
          calculateRouteAndFare(pickupInput.value, dropoffInput.value);
        }
      });
    }

    // Also listen for manual input changes
    pickupInput.addEventListener('blur', () => {
      if (pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });

    dropoffInput.addEventListener('blur', () => {
      if (pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
  }

  // Setup assign driver button
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    assignBtn.addEventListener('click', assignDriver);
  }

});

// Calculate route and fare using Google Maps API
function calculateRouteAndFare(pickup, dropoff) {
  if (!directionsService || !directionsRenderer) {
    console.error('Google Maps services not initialized');
    return;
  }

  const request = {
    origin: pickup,
    destination: dropoff,
    travelMode: google.maps.TravelMode.DRIVING,
  };

  directionsService.route(request, function (result, status) {
    if (status === google.maps.DirectionsStatus.OK) {
      directionsRenderer.setDirections(result);
      const leg = result.routes[0].legs[0];
      const distanceInKm = leg.distance.value / 1000;
      const durationInMin = Math.round(leg.duration.value / 60);
      
      // Calculate fare based on current date/time
      const rideDateInput = document.getElementById('rideDate');
      const rideTimeInput = document.getElementById('rideTime');
      let pickupTimeStr = null;
      
      if (rideDateInput && rideTimeInput && rideDateInput.value && rideTimeInput.value) {
        pickupTimeStr = rideDateInput.value + 'T' + rideTimeInput.value;
      } else {
        // Use current time if not specified
        const now = new Date();
        pickupTimeStr = now.toISOString().slice(0, 16);
      }
      
      const fareAmount = calculateFare(distanceInKm, pickupTimeStr);
      
      // Update fields
      const distanceElem = document.getElementById('distance');
      const estimatedTimeElem = document.getElementById('estimatedTime');
      const estimatedFareElem = document.getElementById('estimatedFare');
      
      if (distanceElem) {
        distanceElem.value = `${distanceInKm.toFixed(2)} km`;
      }
      
      if (estimatedTimeElem) {
        const hours = Math.floor(durationInMin / 60);
        const mins = durationInMin % 60;
        if (hours > 0) {
          estimatedTimeElem.value = `${hours}h ${mins}m`;
        } else {
          estimatedTimeElem.value = `${mins}m`;
        }
      }
      
      if (estimatedFareElem) {
        estimatedFareElem.value = `€${fareAmount.toFixed(2)}`;
      }
      
      // Store values for assignment
      currentDistance = distanceInKm;
      currentDuration = durationInMin;
      currentFare = fareAmount;
    } else {
      console.error('DirectionsService failed:', status);
    }
  });
}

// Calculate fare based on distance and time
function calculateFare(distanceInKm, pickupTimeStr) {
  const pickupDate = new Date(pickupTimeStr);
  const hour = pickupDate.getHours();
  
  let baseFare, ratePerKm;
  
  // Daytime rate (8am to 8pm)
  if (hour >= 8 && hour < 20) {
    baseFare = 4.4;
    ratePerKm = 1.32;
  }
  // Nighttime rate (8pm to 8am)
  else {
    baseFare = 5.4;
    ratePerKm = 1.81;
  }
  
  return baseFare + ratePerKm * distanceInKm;
}

// Load drivers from database
async function loadDrivers() {
  try {
    const response = await fetch('api/get_drivers.php');
    if (!response.ok) {
      throw new Error('Failed to fetch drivers');
    }
    
    const result = await response.json();
    const driverSelect = document.getElementById('driverSelect');
    
    if (result.success && result.data && driverSelect) {
      // Clear existing options except the first one
      driverSelect.innerHTML = '<option selected disabled>Select a driver from the list</option>';
      
      result.data.forEach((driver) => {
        const driverName = driver.full_name || driver.name || 'N/A';
        const vehicleMake = driver.vehicle_make || '';
        const option = document.createElement('option');
        option.value = driver.id;
        option.textContent = `${driverName} — ${vehicleMake}`;
        driverSelect.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Error loading drivers:', error);
  }
}

// Assign driver to ride
async function assignDriver() {
  const driverSelect = document.getElementById('driverSelect');
  const selectedDriverId = driverSelect?.value;
  
  if (!selectedDriverId || !currentRideId) {
    alert('Please select a driver');
    return;
  }
  
  if (!currentDistance || !currentDuration || !currentFare) {
    alert('Please ensure route is calculated. Make sure pickup and drop-off locations are filled.');
    return;
  }
  
  try {
    const response = await fetch('api/assign_driver.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ride_id: currentRideId,
        driver_id: selectedDriverId,
        distance_km: currentDistance,
        duration_min: currentDuration,
        fare_eur: currentFare,
      }),
    });
    
    const result = await response.json();
    
    if (result.success) {
     const modal = new bootstrap.Modal(
  document.getElementById('driverAssignedModal')
);
modal.show();

document
  .getElementById('goToPreorderBtn')
  .addEventListener('click', () => {
    window.location.href = 'preorder.php';
  });

    } else {
      alert('Error assigning driver: ' + (result.error || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error assigning driver:', error);
    alert('Failed to assign driver. Please try again.');
  }
}
</script>

  </body>
</html>
