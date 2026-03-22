<?php
session_start();

require_once 'auth/require_login_redirect.php';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
<style>
  .btn-loading { opacity:0.7; cursor:not-allowed; pointer-events:none; }
  .btn-loading #btnText { opacity:0.9; }
</style>

    <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

<main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <div class="row g-4">

    <div class="col-md-6 d-flex flex-column gap-4">
      <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Customer Details</span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Passenger Name</label>
            <select class="form-select" id="customerNameSelect"
              style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
              onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
              onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
              <option value="">Select passenger from list</option>
            </select>
            <input type="hidden" id="customerId" />
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Phone Number</label>
            <div class="input-group" style="height:38px;">
              <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.845rem; color:#71717A; height:38px;">+353</span>
              <input type="tel" class="form-control" id="phoneNumber" placeholder="Enter phone number"
                style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Service Type</label>
            <select class="form-select" id="serviceType"
              style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
              onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
              onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
              <option value="Economy">Economy</option>
              <option value="Economy XL">Economy XL</option>
              <option value="Business">Business</option>
              <option value="Business Plus">Business Plus</option>
              <option value="Limousine">Limousine</option>
              <option value="Wheelchair accessible">Wheelchair Accessible</option>
            </select>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Date</label>
              <input type="date" class="form-control" id="rideDate"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Time</label>
              <input type="time" class="form-control" id="rideTime"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
          </div>

        </div>
      </div>

      <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Ride Details</span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Pickup</label>
            <div class="input-group" style="height:38px;">
              <span class="input-group-text" style="background:#fff; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; height:38px;">
                <i class="bi bi-geo-alt-fill" style="color:#f37a20; font-size:14px;"></i>
              </span>
              <input type="text" class="form-control" id="pickupLocation" placeholder="Enter pickup location"
                style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#fff;"
                onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Drop Off</label>
            <div class="input-group" style="height:38px;">
              <span class="input-group-text" style="background:#fff; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; height:38px;">
                <i class="bi bi-geo-alt" style="color:#f37a20; font-size:14px;"></i>
              </span>
              <input type="text" class="form-control" id="dropoffLocation" placeholder="Enter drop-off location"
                style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#fff;"
                onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
            </div>
          </div>

          <div class="row g-2 mb-4">
            <div class="col-4">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Est. Fare</label>
              <input type="text" class="form-control" id="estimatedFare" placeholder="Auto" readonly
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B; font-weight:600;" />
            </div>
            <div class="col-4">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Est. Time</label>
              <input type="text" class="form-control" id="estimatedTime" placeholder="Auto" readonly
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B;" />
            </div>
            <div class="col-4">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Distance</label>
              <input type="text" class="form-control" id="distance" placeholder="Auto" readonly
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B;" />
            </div>
          </div>

          <div class="mb-3" style="border-top:1px solid #EBEBEB;"></div>

          <div class="mb-4">
            <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Extras</span>
            <div class="d-flex flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
                <input class="form-check-input m-0" type="checkbox" id="creditCard" style="accent-color:#f37a20; width:15px; height:15px; flex-shrink:0;" />
                <label class="form-check-label" for="creditCard" style="font-size:0.8rem; color:#52525B; cursor:pointer; white-space:nowrap;">Accept Credit Card</label>
              </div>
              <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
                <input class="form-check-input m-0" type="checkbox" id="extraLuggage" style="accent-color:#f37a20; width:15px; height:15px; flex-shrink:0;" />
                <label class="form-check-label" for="extraLuggage" style="font-size:0.8rem; color:#52525B; cursor:pointer; white-space:nowrap;">Extra Luggage</label>
              </div>
              <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
                <input class="form-check-input m-0" type="checkbox" id="petsAllowed" style="accent-color:#f37a20; width:15px; height:15px; flex-shrink:0;" />
                <label class="form-check-label" for="petsAllowed" style="font-size:0.8rem; color:#52525B; cursor:pointer; white-space:nowrap;">Pets Allowed</label>
              </div>
              <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
                <input class="form-check-input m-0" type="checkbox" id="delivery" style="accent-color:#f37a20; width:15px; height:15px; flex-shrink:0;" />
                <label class="form-check-label" for="delivery" style="font-size:0.8rem; color:#52525B; cursor:pointer; white-space:nowrap;">Delivery</label>
              </div>
            </div>
          </div>

          <button class="btn w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold" id="assignDriverBtn"
            style="height:40px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.875rem; box-shadow:0 4px 14px rgba(243,122,32,0.35);"
            onmouseover="this.style.background='#d96010';"
            onmouseout="this.style.background='#f37a20';">
            <i class="bi bi-person-check" style="font-size:15px;"></i>
            <span id="btnText">Assign Driver</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
          </button>

        </div>
      </div>

    </div>

    <div class="col-md-6 d-flex flex-column gap-4">

      <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Assign Driver</span>
          </div>

          <div>
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Select Driver</label>
            <select class="form-select" id="driverSelect"
              style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
              onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
              onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
              <option value="" disabled selected>Select a driver from the list</option>
            </select>
          </div>

        </div>
      </div>

      <div class="rounded-3 overflow-hidden flex-grow-1" style="border:1.5px solid #EBEBEB; min-height:420px; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div id="map" style="width:100%; height:100%; min-height:420px; border:0;"></div>
      </div>

    </div>
  </div>
</main>

<div class="modal fade" id="driverAssignedModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-4 text-center" style="border-radius:14px; max-width:400px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
      <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
        style="width:60px; height:60px; background:#F0FDF4; border-radius:50%;">
        <i class="bi bi-check2-circle" style="font-size:1.8rem; color:#22C55E;"></i>
      </div>
      <h5 class="fw-bold mb-1" style="color:#18181B;">Driver Assigned</h5>
      <p class="mb-4" style="font-size:0.845rem; color:#71717A;">The driver has been successfully assigned to this order.</p>
      <button type="button" class="btn fw-semibold px-5 mx-auto"
        style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.875rem;"
        onmouseover="this.style.background='#d96010';"
        onmouseout="this.style.background='#f37a20';"
        data-bs-dismiss="modal" id="goToPreorderBtn">
        OK
      </button>
    </div>
  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9ea0A-mjnD5iHfT9X8Dn5YYH4_KZopLI&libraries=places,geometry&callback=initGoogleMaps" async defer></script>
   <script>
// Global variables for Google Maps
let map, directionsService, directionsRenderer;
let currentRideId = null;
let currentDistance = null;
let currentDuration = null;
let currentFare = null;
let hasDbFare = false;
let passengersList = [];

// Wait for Google Maps API to load (called by script callback when API is ready)
function initGoogleMaps() {
  if (typeof google === 'undefined' || !google.maps) {
    setTimeout(initGoogleMaps, 200);
    return;
  }

  const mapElement = document.getElementById('map');
  if (!mapElement) {
    setTimeout(initGoogleMaps, 100);
    return;
  }

  map = new google.maps.Map(mapElement, {
    center: { lat: 53.349805, lng: -6.26031 }, // Dublin default
    zoom: 13,
  });

  directionsService = new google.maps.DirectionsService();
  directionsRenderer = new google.maps.DirectionsRenderer();
  directionsRenderer.setMap(map);

  // Attach Places Autocomplete when API is ready (fixes suggestions not appearing)
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');
  if (pickupInput && dropoffInput && google.maps.places) {
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

  // Load passengers first so dropdown is ready for prefill
  await loadPassengers();
  setupPassengerSelect();

  // Fetch and prefill ride data if ID is present in URL
  const urlParams = new URLSearchParams(window.location.search);
  const rideId = urlParams.get('id');

  if (rideId) {
    try {
      const response = await fetch(`api/get_ride.php?id=${encodeURIComponent(rideId)}`);
      if (response.status === 401) { window.location.href = '/'; return; }
      if (!response.ok) {
        throw new Error('Failed to fetch ride data');
      }

      const result = await response.json();
      if (result.success && result.data) {
        const ride = result.data;
        
        // Prefill Customer Details (passenger from dropdown only)
        const customerNameSelect = document.getElementById('customerNameSelect');
        if (customerNameSelect && ride.user_id) {
          customerNameSelect.value = String(ride.user_id);
        }
        const customerId = document.getElementById('customerId');
        if (customerId) customerId.value = ride.user_id || '';

        const phoneNumber = document.getElementById('phoneNumber');
        if (phoneNumber && ride.passenger_phone) {
          // Remove country code if present, as it's already in the static field
          let phone = String(ride.passenger_phone);
          if (phone.startsWith('+353')) {
            phone = phone.substring(4).trim();
          } else if (phone.startsWith('353')) {
            phone = phone.substring(3).trim();
          }
          phoneNumber.value = phone;
        }

        const serviceType = document.getElementById('serviceType');
        if (serviceType && (ride.ride_type || ride.service_type)) {
          const value = (ride.ride_type || ride.service_type).trim();
          const hasOption = Array.from(serviceType.options).some((o) => o.value === value);
          if (!hasOption && value) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            serviceType.appendChild(opt);
          }
          serviceType.value = value;
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

        // Prefill Fare Details (use stored fare from DB for app-booked rides)
        const estimatedFare = document.getElementById('estimatedFare');
        if (estimatedFare) {
          const fare = ride.fare_eur || ride.estimate_fare || null;
          if (fare !== null) {
            const numericFare = parseFloat(fare);
            estimatedFare.value = `€${numericFare.toFixed(2)}`;
            currentFare = numericFare;
            hasDbFare = true;
          }
        }

        // Prefill Distance if available
        const distance = document.getElementById('distance');
        if (distance && ride.distance_km) {
          distance.value = `${ride.distance_km} km`;
          currentDistance = parseFloat(ride.distance_km);
        }

        // Prefill Estimated Time if available
        const estimatedTime = document.getElementById('estimatedTime');
        if (estimatedTime && (ride.duration_min || ride.estimated_duration_minutes)) {
          const minutes = ride.duration_min || ride.estimated_duration_minutes;
          const hours = Math.floor(minutes / 60);
          const mins = minutes % 60;
          currentDuration = minutes;
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

  // initGoogleMaps is called by Maps API callback when script is ready (autocomplete set up there)

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

  // Recalculate fare when service type changes (similar to passenger app behaviour)
  const serviceTypeSelect = document.getElementById('serviceType');
  if (serviceTypeSelect) {
    serviceTypeSelect.addEventListener('change', () => {
      if (currentDistance == null || currentDuration == null) {
        return;
      }
      const rideDateInput = document.getElementById('rideDate');
      const rideTimeInput = document.getElementById('rideTime');
      let pickupTimeStr = null;
      if (rideDateInput && rideTimeInput && rideDateInput.value && rideTimeInput.value) {
        pickupTimeStr = rideDateInput.value + 'T' + rideTimeInput.value;
      } else {
        const now = new Date();
        pickupTimeStr = now.toISOString().slice(0, 16);
      }
      const rideType =
        serviceTypeSelect.value && serviceTypeSelect.value.trim()
          ? serviceTypeSelect.value.trim()
          : 'Economy';
      const newFare = calculateFare(currentDistance, currentDuration, pickupTimeStr, rideType);
      currentFare = newFare;
      hasDbFare = false;
      const estimatedFareElem = document.getElementById('estimatedFare');
      if (estimatedFareElem) {
        estimatedFareElem.value = `€${newFare.toFixed(2)}`;
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
      
      // Only (re)calculate fare from distance/time when we are not using a DB fare
      if (!hasDbFare) {
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

        const rideType =
          document.getElementById('serviceType')?.value?.trim() || 'Economy';
        const fareAmount = calculateFare(
          distanceInKm,
          durationInMin,
          pickupTimeStr,
          rideType
        );

        if (estimatedFareElem) {
          estimatedFareElem.value = `€${fareAmount.toFixed(2)}`;
        }
        currentFare = fareAmount;
      }
      
      // Store values for assignment
      currentDistance = distanceInKm;
      currentDuration = durationInMin;
    } else {
      console.error('DirectionsService failed:', status);
    }
  });
}

// Calculate fare based on distance, duration and time 
function calculateFare(distanceInKm, durationInMin, pickupTimeStr, rideType) {
  const pickupDate = new Date(pickupTimeStr);
  const hour = pickupDate.getHours();
  const initialFare = 3.0;
  let baseFare, ratePerKm, ratePerMinute;
  if (hour >= 8 && hour < 20) {
    baseFare = 4.4;
    ratePerKm = 1.32;
    ratePerMinute = 0.20;
  } else {
    baseFare = 5.4;
    ratePerKm = 1.81;
    ratePerMinute = 0.30;
  }
  const rawFare = initialFare + baseFare + (distanceInKm * ratePerKm) + ((durationInMin || 0) * ratePerMinute);
  const multipliers = {
    'Economy': 1.0,
    'Economy XL': 1.2,
    'Business': 1.0,
    'Business Plus': 1.2,
    'Limousine': 2.0,
    'Wheelchair accessible': 1.1,
    'Wheelchair Taxi': 1.1,
    'Pets Taxi': 1.15,
    'Courier / Parcel': 0.9
  };
  const multiplier = multipliers[rideType] ?? 1.0;
  return Math.round((rawFare * multiplier) * 100) / 100;
}

// Load passengers and populate dropdown (select only, no custom typing)
async function loadPassengers() {
  try {
    const response = await fetch('api/get_passengers.php?limit=500');
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) return;
    const result = await response.json();
    if (result.success && result.data) {
      passengersList = result.data;
      const select = document.getElementById('customerNameSelect');
      if (!select) return;
      select.innerHTML = '<option value="">Select passenger from list</option>';
      passengersList.forEach((p) => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.name || p.full_name || 'Unknown';
        select.appendChild(opt);
      });
    }
  } catch (err) {
    console.error('Error loading passengers', err);
  }
}

function setupPassengerSelect() {
  const select = document.getElementById('customerNameSelect');
  const phoneInput = document.getElementById('phoneNumber');
  const customerIdInput = document.getElementById('customerId');
  if (!select || !phoneInput) return;
  select.addEventListener('change', () => {
    const id = select.value;
    if (!id) {
      phoneInput.value = '';
      if (customerIdInput) customerIdInput.value = '';
      return;
    }
    const passenger = passengersList.find((p) => String(p.id) === String(id));
    if (passenger) {
      let phone = passenger.phone || passenger.phone_number || '';
      phone = String(phone).replace(/^\+353/, '').replace(/^353/, '').trim();
      phoneInput.value = phone;
      if (customerIdInput) customerIdInput.value = id;
    }
  });
}

// Load drivers from database
async function loadDrivers() {
  try {
    const response = await fetch('api/get_drivers.php');
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) {
      throw new Error('Failed to fetch drivers');
    }
    
    const result = await response.json();
    const driverSelect = document.getElementById('driverSelect');
    
    if (result.success && result.data && driverSelect) {
      // Clear existing options except the first one
      driverSelect.innerHTML = '<option value="" selected disabled>Select a driver from the list</option>';
      
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

function setButtonLoading(isLoading, customText = null) {
  const btn = document.getElementById('assignDriverBtn');
  const btnText = document.getElementById('btnText');
  const btnSpinner = document.getElementById('btnSpinner');
  
  if (isLoading) {
    btn.classList.add('btn-loading');
    btn.disabled = true;
    btnSpinner.style.display = 'inline-block';
    btnText.textContent = customText || 'Assigning Driver...';
  } else {
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btnSpinner.style.display = 'none';
    btnText.textContent = 'Assign Driver';
  }
}

// Validate required fields (alerts for now; replace with toast later)
function validateOrderAssignedForm() {
  const passengerSelect = document.getElementById('customerNameSelect');
  const phoneInput = document.getElementById('phoneNumber');
  const rideDate = document.getElementById('rideDate');
  const rideTime = document.getElementById('rideTime');
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');

  if (!passengerSelect?.value?.trim()) {
    showToast('Please select a passenger.');
    return false;
  }
  if (!phoneInput?.value?.trim()) {
    showToast('Please enter a phone number.');
    return false;
  }
  if (!rideDate?.value?.trim()) {
    showToast('Please select a date.');
    return false;
  }
  if (!rideTime?.value?.trim()) {
    showToast('Please select a time.');
    return false;
  }
  if (!pickupInput?.value?.trim()) {
    showToast('Please enter a pickup location.');
    return false;
  }
  if (!dropoffInput?.value?.trim()) {
    showToast('Please enter a drop-off location.');
    return false;
  }
  return true;
}

function showToast(message) {
  const toastEl = document.getElementById('globalToast');
  const toastMsg = document.getElementById('toastMessage');
  if (!toastEl || !toastMsg) return;

  toastMsg.textContent = message;
  toastEl.className = 'toast align-items-center text-white bg-danger border-0';
  
  const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, {
    autohide: true,
    delay: 3000
  });
  bsToast.show();
}

// Function to collect form data
function getFormData() {
  return {
    customerId: document.getElementById('customerId')?.value || '',
    customerName: document.getElementById('customerNameSelect')?.value || '',
    phoneNumber: document.getElementById('phoneNumber')?.value || '',
    serviceType: document.getElementById('serviceType')?.value || '',
    rideDate: document.getElementById('rideDate')?.value || '',
    rideTime: document.getElementById('rideTime')?.value || '',
    pickupLocation: document.getElementById('pickupLocation')?.value || '',
    dropoffLocation: document.getElementById('dropoffLocation')?.value || '',
    estimatedFare: document.getElementById('estimatedFare')?.value || '',
    estimatedTime: document.getElementById('estimatedTime')?.value || '',
    distance: document.getElementById('distance')?.value || '',
    creditCard: document.getElementById('creditCard')?.checked || false,
    extraLuggage: document.getElementById('extraLuggage')?.checked || false,
    petsAllowed: document.querySelectorAll('#extraLuggage')[1]?.checked || false,
    delivery: document.querySelectorAll('#extraLuggage')[2]?.checked || false,
    driverId: document.getElementById('driverSelect')?.value || ''
  };
}

// Updated assignDriver function with loading state
async function assignDriver() {
  if (!validateOrderAssignedForm()) {
    return;
  }

  if (!currentRideId) {
    showToast('No order selected. Please open this page from an order (e.g. from the orders list) to assign a driver.');
    return;
  }

  const driverSelect = document.getElementById('driverSelect');
  const selectedDriverId = driverSelect?.value?.trim();

  if (!selectedDriverId) {
    showToast('Please select a driver');
    return;
  }
  
  if (!currentDistance || !currentDuration || !currentFare) {
    showToast('Please ensure route is calculated. Make sure pickup and drop-off locations are filled.');
    return;
  }
  
   const serviceTypeEl = document.getElementById('serviceType');
  const serviceType = serviceTypeEl?.value?.trim() || null;

  // Set loading state before API call
  setButtonLoading(true, 'Assigning Driver...');

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
        service_type: serviceType,
      }),
    });
       if (response.status === 401) { 
      window.location.href = '/'; 
      return; 
    }
    
    const result = await response.json();
    
    if (result.success) {
      const modal = new bootstrap.Modal(
        document.getElementById('driverAssignedModal')
      );
      modal.show();

      // Remove existing event listener to prevent multiple redirects
      const goToPreorderBtn = document.getElementById('goToPreorderBtn');
      const newBtn = goToPreorderBtn.cloneNode(true);
      goToPreorderBtn.parentNode.replaceChild(newBtn, goToPreorderBtn);
      
      newBtn.addEventListener('click', () => {
        window.location.href = 'preorder.php';
      });

    } else {
      showToast('Error assigning driver: ' + (result.error || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error assigning driver:', error);
    showToast('Failed to assign driver. Please try again.');
  } finally {
    // Reset button state after API call completes (success or error)
    setButtonLoading(false);
  }
}

// Initialize event listener when document is ready
document.addEventListener('DOMContentLoaded', function() {
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    // Remove any existing listeners and add new one
    assignBtn.replaceWith(assignBtn.cloneNode(true));
    document.getElementById('assignDriverBtn').addEventListener('click', assignDriver);
  }
});

</script>

<!-- Global Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
  <div id="globalToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
  </body>
</html>
