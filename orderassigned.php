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

  @keyframes slideInRight {
    from { opacity: 0; transform: translateX(16px); }
    to   { opacity: 1; transform: translateX(0); }
  }

  /* ── View mode: assigned ride tracking ──────────────────────────────── */
  main.view-mode-active { padding: 20px !important; background: #F4F4F5 !important; min-height: 0 !important; overflow: hidden !important; }
  main.view-mode-active .row.g-4 { margin: 0 !important; }
  main.view-mode-active #leftFormPanel { display: none !important; }
  main.view-mode-active #rightMapPanel {
    flex: 0 0 100% !important; width: 100% !important;
    max-width: 100% !important; padding: 0 !important;
  }
  main.view-mode-active #assignDriverCard { display: none !important; }
  main.view-mode-active #mapContainer {
    border-radius: 12px !important;
    border: 1.5px solid #EBEBEB !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
    background: #fff !important;
    overflow: hidden !important;
  }
</style>

    <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

<?php $_vmActive = !empty($_GET['id']) && isset($_GET['view']) && $_GET['view']==='1' && !isset($_GET['corp_id']); ?>
<main class="main-content p-4<?php echo $_vmActive ? ' view-mode-active' : ''; ?>" style="background:#F4F4F5; min-height:100vh;">

  <div class="row g-4">

    <div id="leftFormPanel" class="col-md-6 d-flex flex-column gap-4">
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

          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn d-flex align-items-center justify-content-center gap-2 fw-semibold px-3" id="cancelRideBtn"
              style="height:40px; background:#fff; color:#E11D48; border:1.5px solid #E11D48; border-radius:8px; font-size:0.875rem; white-space:nowrap; transition:all 0.15s;"
              onmouseover="this.style.background='#E11D48'; this.style.color='#fff';"
              onmouseout="this.style.background='#fff'; this.style.color='#E11D48';">
              <i class="bi bi-x-circle" style="font-size:15px;"></i>
              <span id="cancelBtnText">Cancel Ride</span>
              <span id="cancelBtnSpinner" class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
            </button>
            <button class="btn flex-grow-1 d-flex align-items-center justify-content-center gap-2 fw-semibold" id="assignDriverBtn"
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

    </div>

    <div id="rightMapPanel" class="col-md-6 d-flex flex-column gap-4">

      <div id="assignDriverCard" class="rounded-3 border" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:visible;">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Assign Driver</span>
          </div>

          <div class="position-relative" style="z-index:1060;">
            <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Select Driver</label>
            <input type="text" id="driverSearchInput" class="form-control" placeholder="Type to search driver..."
              autocomplete="off"
              style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
              onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)'; showDriverDropdown();"
              onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none'; setTimeout(hideDriverDropdown,200);" />
            <input type="hidden" id="driverSelect" value="" />
            <div id="driverDropdownList" class="list-group position-absolute w-100"
              style="z-index:1060; max-height:220px; overflow-y:auto; display:none; border:1px solid #E4E4E7; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.10); background:#fff; margin-top:2px;">
            </div>
          </div>

        </div>
      </div>

      <div id="mapContainer" class="rounded-3 overflow-hidden flex-grow-1" style="position:relative; border:1.5px solid #EBEBEB; min-height:420px; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div id="map" style="width:100%; height:100%; min-height:420px; border:0;"></div>

        <!-- Dispatcher info overlay — shown only in Assigned → View Details mode -->
        <div id="dispatcherOverlay" style="display:none; position:absolute; top:14px; left:14px; z-index:10; background:rgba(255,255,255,0.96); border:1px solid #E4E4E7; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,0.13); padding:14px 16px; min-width:240px; max-width:300px; pointer-events:none; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
          <div style="display:flex; align-items:center; gap:7px; margin-bottom:10px;">
            <span id="liveTrackingDot" style="width:8px; height:8px; border-radius:50%; background:#22C55E; display:inline-block; flex-shrink:0;"></span>
            <span id="liveTrackingLabel" style="font-size:0.7rem; font-weight:700; color:#22C55E; text-transform:uppercase; letter-spacing:0.07em;">Live Tracking</span>
          </div>
          <div id="overlayDriverName" style="font-size:0.88rem; font-weight:700; color:#18181B; margin-bottom:2px;">—</div>
          <div id="overlayVehicle" style="font-size:0.75rem; color:#71717A; margin-bottom:10px;">—</div>
          <div style="border-top:1px solid #F4F4F5; padding-top:10px; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:flex-start; gap:8px;">
              <span style="flex-shrink:0; width:9px; height:9px; border-radius:50%; background:#22C55E; margin-top:3px; display:inline-block;"></span>
              <div>
                <div style="font-size:0.67rem; text-transform:uppercase; color:#A1A1AA; letter-spacing:0.05em; font-weight:600; margin-bottom:1px;">Pickup</div>
                <div id="overlayPickup" style="font-size:0.78rem; color:#18181B; line-height:1.3;">—</div>
              </div>
            </div>
            <div style="display:flex; align-items:flex-start; gap:8px;">
              <span style="flex-shrink:0; width:9px; height:9px; border-radius:50%; background:#E11D48; margin-top:3px; display:inline-block;"></span>
              <div>
                <div style="font-size:0.67rem; text-transform:uppercase; color:#A1A1AA; letter-spacing:0.05em; font-weight:600; margin-bottom:1px;">Dropoff</div>
                <div id="overlayDropoff" style="font-size:0.78rem; color:#18181B; line-height:1.3;">—</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<div class="modal fade" id="cancelRideModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-4 text-center" style="border-radius:14px; max-width:420px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

      <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
        style="width:60px; height:60px; background:#FFF1F2; border-radius:50%;">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:1.7rem; color:#E11D48;"></i>
      </div>
      <h5 class="fw-bold mb-1" style="color:#18181B; font-size:1.0625rem;">Cancel this ride?</h5>
      <p class="mb-4" style="font-size:0.875rem; color:#71717A; line-height:1.5;">This ride will be marked as <strong style="color:#18181B;">Cancelled</strong> and cannot be reverted. The passenger will be notified.</p>

      <div class="d-flex justify-content-center gap-2">
        <button type="button" class="btn fw-semibold px-4"
          style="height:40px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.875rem;"
          onmouseover="this.style.borderColor='#18181B';"
          onmouseout="this.style.borderColor='#EBEBEB';"
          data-bs-dismiss="modal">Keep Ride
        </button>
        <button type="button" class="btn fw-semibold px-4 d-inline-flex align-items-center justify-content-center gap-2" id="confirmCancelRideBtn"
          style="height:40px; background:#E11D48; color:#fff; border:none; border-radius:8px; font-size:0.875rem;"
          onmouseover="this.style.background='#BE123C';"
          onmouseout="this.style.background='#E11D48';">
          <i class="bi bi-x-circle" style="font-size:14px;"></i>
          <span id="confirmCancelText">Yes, Cancel Ride</span>
          <span id="confirmCancelSpinner" class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
        </button>
      </div>

    </div>
  </div>
</div>

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
let corporateEmployeesList = [];
let isCorporateMode = false;
let isCorporateViewMode = false;
let currentCorpId = null;
let currentPickupLat = null;
let currentPickupLng = null;
let currentDropLat = null;
let currentDropLng = null;

// Live driver tracking
let assignedDriverId = null;
let driverLiveMarker = null;
let driverTrackingInterval = null;
let mapReadyForTracking = false;

// View mode (Assigned → View Details)
let isViewMode = false;
let viewModePickupMarker = null;
let viewModeDropoffMarker = null;

// Pending route — set before map loads, consumed by initGoogleMaps
let pendingPickupAddr = null;
let pendingDropoffAddr = null;

// Driver → Pickup road route
let driverRouteRenderer = null;
let lastDriverRouteLat = null;
let lastDriverRouteLng = null;
const DRIVER_ROUTE_THRESHOLD_M = 40; // recalculate only when driver moves ≥ 40 m

// Smooth marker animation — glide across the full poll interval (continuous motion)
let driverAnimTimer = null;
const DRIVER_POLL_MS = 5000;            // driver-location poll cadence
const MARKER_ANIM_MS = DRIVER_POLL_MS;  // animate across the whole interval so the car never freezes

// Route progress visualization (switches on when ride status → on_trip)
let currentRoutePath = [];
let completedPolyline = null;
let remainingPolyline = null;
let routeProgressActive = false;

// Ride status state machine
let currentRideStatus = null;

// Driver heading for marker rotation
let currentDriverBearing = 0;

async function fetchAndUpdateDriverMarker(driverId) {
  if (!map || !driverId) return;
  try {
    let lat, lng, driverName = 'Driver', vehicle = '';

    if (isViewMode && currentRideId) {
      // Active ride: GPS written to rides.driver_lat/lng by the driver app
      const resp = await fetch(`api/get_ride_location.php?ride_id=${encodeURIComponent(currentRideId)}`, { cache: 'no-store' });
      if (!resp.ok) return;
      const result = await resp.json();
      if (!result.success || !result.data || result.data.lat === null) return;
      lat = parseFloat(result.data.lat);
      lng = parseFloat(result.data.lng);

      // Drive status state machine
      const newStatus = (result.data.status || '').toLowerCase();
      if (newStatus && newStatus !== currentRideStatus) {
        handleRideStatusChange(currentRideStatus, newStatus);
        currentRideStatus = newStatus;
        if (driverLiveMarker) driverLiveMarker.setIcon(buildDriverIcon(currentDriverBearing, newStatus));
      }
    } else {
      // Idle/fleet: GPS in drivers.current_lat/lng
      const resp = await fetch(`api/get_live_drivers.php?driver_id=${encodeURIComponent(driverId)}`, { cache: 'no-store' });
      if (!resp.ok) return;
      const result = await resp.json();
      if (!result.success || !result.data || result.data.length === 0) return;
      const loc = result.data[0];
      lat = parseFloat(loc.current_lat ?? loc.lat);
      lng = parseFloat(loc.current_lng ?? loc.lng);
      driverName = loc.full_name || loc.name || 'Driver';
      vehicle    = loc.vehicle_number || loc.vehicle_make || '';
      const apiHeading = parseFloat(loc.heading ?? loc.driver_heading ?? NaN);
      if (!isNaN(apiHeading)) currentDriverBearing = apiHeading;
    }

    if (isNaN(lat) || isNaN(lng)) return;

    if (driverLiveMarker) {
      animateDriverMarker(lat, lng);
    } else {
      driverLiveMarker = new google.maps.Marker({
        position: { lat, lng },
        map,
        icon: buildDriverIcon(currentDriverBearing, currentRideStatus),
        title: driverName,
        zIndex: 20,
      });
      const infoWin = new google.maps.InfoWindow({
        content: `<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;padding:4px 2px;min-width:160px;">
          <div style="font-weight:700;color:#18181B;font-size:13px;">${driverName}</div>
          ${vehicle ? `<div style="font-size:11px;color:#71717A;margin-top:2px;">${vehicle}</div>` : ''}
          <div style="margin-top:4px;display:inline-flex;align-items:center;gap:5px;">
            <span style="width:7px;height:7px;border-radius:50%;background:#22C55E;display:inline-block;"></span>
            <span style="font-size:11px;color:#22C55E;font-weight:600;">Live</span>
          </div>
        </div>`,
      });
      driverLiveMarker.addListener('click', () => infoWin.open(map, driverLiveMarker));
    }

    // Driver→Pickup route: only when not yet on trip
    if (isViewMode && currentPickupLat && currentPickupLng && !routeProgressActive) {
      const needsUpdate = lastDriverRouteLat === null ||
        geoDistanceMeters(lat, lng, lastDriverRouteLat, lastDriverRouteLng) >= DRIVER_ROUTE_THRESHOLD_M;
      if (needsUpdate) updateDriverToPickupRoute(lat, lng);
    }

    // Route progress: active once trip starts
    if (routeProgressActive && currentRoutePath.length) {
      updateRouteProgress(lat, lng);
    }
  } catch (e) {
    console.warn('Driver live tracking error:', e);
  }
}

// Smooth interpolation across the full poll interval, driven by requestAnimationFrame
// (≈60 fps) so the marker glides continuously instead of jumping to the new point and
// freezing until the next poll. Rotates icon to face direction of travel.
function animateDriverMarker(toLat, toLng) {
  if (!driverLiveMarker) return;
  if (driverAnimTimer) { cancelAnimationFrame(driverAnimTimer); driverAnimTimer = null; }

  const fromPos = driverLiveMarker.getPosition();
  if (!fromPos) { driverLiveMarker.setPosition({ lat: toLat, lng: toLng }); return; }

  const fromLat = fromPos.lat();
  const fromLng = fromPos.lng();
  if (fromLat === toLat && fromLng === toLng) return;

  // Compute bearing once at start and update icon direction
  const bearing = computeBearing(fromLat, fromLng, toLat, toLng);
  if (bearing !== null) {
    currentDriverBearing = bearing;
    driverLiveMarker.setIcon(buildDriverIcon(currentDriverBearing, currentRideStatus));
  }

  const startTs = performance.now();
  const animate = (nowTs) => {
    const f = Math.min((nowTs - startTs) / MARKER_ANIM_MS, 1);
    driverLiveMarker.setPosition({
      lat: fromLat + (toLat - fromLat) * f,
      lng: fromLng + (toLng - fromLng) * f,
    });
    if (f < 1) {
      driverAnimTimer = requestAnimationFrame(animate);
    } else {
      driverAnimTimer = null;
    }
  };
  driverAnimTimer = requestAnimationFrame(animate);
}

function startDriverTracking() {
  if (!assignedDriverId || !mapReadyForTracking) return;
  if (driverTrackingInterval) return;  // already running
  if (driverRouteRenderer && map) driverRouteRenderer.setMap(map);
  fetchAndUpdateDriverMarker(assignedDriverId);
  driverTrackingInterval = setInterval(() => fetchAndUpdateDriverMarker(assignedDriverId), DRIVER_POLL_MS);
}

function stopDriverTracking() {
  if (driverTrackingInterval) {
    clearInterval(driverTrackingInterval);
    driverTrackingInterval = null;
  }
  if (driverAnimTimer) {
    cancelAnimationFrame(driverAnimTimer);
    driverAnimTimer = null;
  }
  if (driverLiveMarker) {
    driverLiveMarker.setMap(null);
    driverLiveMarker = null;
  }
  lastDriverRouteLat = null;
  lastDriverRouteLng = null;
  if (driverRouteRenderer) driverRouteRenderer.setMap(null);
  clearRouteProgress();
}

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
  // suppressMarkers: true so our custom green/red pins don't clash with Google's A/B pins
  directionsRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: true });
  directionsRenderer.setMap(map);

  // Driver → Pickup: solid orange road route
  driverRouteRenderer = new google.maps.DirectionsRenderer({
    suppressMarkers: true,
    polylineOptions: {
      strokeColor: '#f37a20',
      strokeOpacity: 0.9,
      strokeWeight: 5,
    },
  });
  driverRouteRenderer.setMap(map);

  // Signal that the map is ready and start driver tracking if a driver is already set
  mapReadyForTracking = true;
  startDriverTracking();

  // View mode: trigger resize after layout has been applied
  if (isViewMode) google.maps.event.trigger(map, 'resize');

  // Calculate route if ride data loaded before map was ready
  if (pendingPickupAddr && pendingDropoffAddr) {
    calculateRouteAndFare(pendingPickupAddr, pendingDropoffAddr);
    pendingPickupAddr = null;
    pendingDropoffAddr = null;
  }

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

  // Kick off the driver list fetch up front so it loads in parallel with the
  // passenger/ride loads below. This way the Assign-Driver dropdown is ready by
  // the time the dispatcher clicks it (it previously only started after those
  // awaited fetches, causing a noticeable delay on first click).
  loadDrivers();

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

  // Detect mode from URL: corporate-ride mode has ?corp_id=, normal has ?id=
  const urlParams = new URLSearchParams(window.location.search);
  const rideId = urlParams.get('id');
  const corpId = urlParams.get('corp_id');
  isCorporateMode = !!corpId;
  isCorporateViewMode = isCorporateMode && urlParams.get('view') === '1';
  isViewMode = !!rideId && !isCorporateMode && urlParams.get('view') === '1';
  const isNormalViewMode = isViewMode;

  if (isCorporateMode) {
    // Relabel the "Passenger" dropdown for corporate context
    const passengerLabel = document.querySelector('label[for="customerNameSelect"]')
      || document.querySelector('#customerNameSelect')?.previousElementSibling;
    if (passengerLabel && passengerLabel.tagName === 'LABEL') {
      passengerLabel.textContent = 'Corporate Employee';
    }
    const customerNameSelect = document.getElementById('customerNameSelect');
    if (customerNameSelect) {
      customerNameSelect.innerHTML = '<option value="">Select corporate employee from list</option>';
    }
    await loadCorporateRide(corpId);
    setupCorporateEmployeeSelect();
  } else {
    // Normal flow: load passengers dropdown, then prefill from rides table
    await loadPassengers();
    setupPassengerSelect();
  }

  if (rideId && !isCorporateMode) {
    try {
      const response = await fetch(`api/get_ride.php?id=${encodeURIComponent(rideId)}`);
      if (response.status === 401) { window.location.href = '/'; return; }
      if (!response.ok) {
        throw new Error('Failed to fetch ride data');
      }

      const result = await response.json();
      if (result.success && result.data) {
        const ride = result.data;
        
        // Prefill Customer Details (passenger from dropdown)
        const customerNameSelect = document.getElementById('customerNameSelect');
        if (customerNameSelect && ride.user_id) {
          // Ensure the ride's passenger exists as an option even if they weren't
          // in the batch loaded by loadPassengers() (limit=500) — otherwise the
          // selection silently fails and the name doesn't appear.
          const exists = Array.from(customerNameSelect.options)
            .some((o) => String(o.value) === String(ride.user_id));
          if (!exists) {
            const opt = document.createElement('option');
            opt.value = ride.user_id;
            opt.textContent = (ride.passenger_name && ride.passenger_name !== 'N/A')
              ? ride.passenger_name : 'Passenger';
            customerNameSelect.appendChild(opt);
            // Keep passengersList in sync so the change-handler can resolve phone.
            if (Array.isArray(passengersList)) {
              passengersList.push({
                id: ride.user_id,
                name: ride.passenger_name,
                phone: ride.passenger_phone,
                email: ride.passenger_email,
              });
            }
          }
          customerNameSelect.value = String(ride.user_id);
        }
        const customerId = document.getElementById('customerId');
        if (customerId) customerId.value = ride.user_id || '';

        const phoneNumber = document.getElementById('phoneNumber');
        if (phoneNumber && ride.passenger_phone && ride.passenger_phone !== 'N/A') {
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

        // Start live driver tracking if the ride already has an assigned driver
        if (ride.driver_id) {
          assignedDriverId = ride.driver_id;
          startDriverTracking();
        }

        // Calculate route if both locations are available
        if (pickupLocation && dropoffLocation && pickupLocation.value && dropoffLocation.value) {
          if (directionsService && directionsRenderer) {
            calculateRouteAndFare(pickupLocation.value, dropoffLocation.value);
          } else {
            // Map not loaded yet — store addresses for initGoogleMaps to consume
            pendingPickupAddr = pickupLocation.value;
            pendingDropoffAddr = dropoffLocation.value;
          }
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

        if (isNormalViewMode) {
          applyCorporateViewMode();
          applyViewModeLayout();
        }
      }
    } catch (error) {
      console.error('Error loading ride data:', error);
    }
  }

  // initGoogleMaps is called by Maps API callback when script is ready (autocomplete set up there)
  // (driver list load was kicked off at the top of init so it runs in parallel)

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
      storeRoutePolyline(result);
      if (routeProgressActive) directionsRenderer.setOptions({ suppressPolylines: true });
      const leg = result.routes[0].legs[0];
      const distanceInKm = leg.distance.value / 1000;
      const durationInMin = Math.round(leg.duration.value / 60);

      // Capture lat/lng so corporate-ride inserts can satisfy NOT NULL columns on rides
      if (leg.start_location) {
        currentPickupLat = leg.start_location.lat();
        currentPickupLng = leg.start_location.lng();
      }
      if (leg.end_location) {
        currentDropLat = leg.end_location.lat();
        currentDropLng = leg.end_location.lng();
      }
      // Place custom pickup/dropoff markers in view mode once coordinates are known
      if (isViewMode && map) placeViewModeMarkers();
      
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

function stripIrelandCountryCode(raw) {
  let phone = String(raw ?? '').trim();
  if (phone.startsWith('+353')) phone = phone.substring(4).trim();
  else if (phone.startsWith('353')) phone = phone.substring(3).trim();
  return phone;
}

async function loadCorporateRide(corpId) {
  try {
    const response = await fetch(`api/get_corporate_ride.php?id=${encodeURIComponent(corpId)}`);
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) throw new Error('Failed to fetch corporate ride');
    const result = await response.json();
    if (!result.success || !result.data) {
      showToast(result.error || 'Corporate ride not found');
      return;
    }

    const ride = result.data.ride || {};
    const employees = Array.isArray(result.data.employees) ? result.data.employees : [];
    corporateEmployeesList = employees;
    currentCorpId = corpId;

    // Populate the employees dropdown
    const select = document.getElementById('customerNameSelect');
    if (select) {
      select.innerHTML = '<option value="">Select corporate employee from list</option>';
      employees.forEach((emp) => {
        const opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.name || 'Unknown';
        select.appendChild(opt);
      });
      // Ensure the current ride's employee is in the list (fallback option if missing)
      if (ride.employee_id) {
        const exists = employees.some((e) => String(e.id) === String(ride.employee_id));
        if (!exists) {
          const opt = document.createElement('option');
          opt.value = ride.employee_id;
          opt.textContent = (ride.employee || '').trim() || 'Employee';
          select.appendChild(opt);
        }
        select.value = String(ride.employee_id);
      }
    }
    const customerIdInput = document.getElementById('customerId');
    if (customerIdInput) customerIdInput.value = ride.employee_id || '';

    // Phone: try to match by employee_id
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
      const matchedEmp = employees.find((e) => String(e.id) === String(ride.employee_id));
      phoneInput.value = matchedEmp ? stripIrelandCountryCode(matchedEmp.phone) : '';
    }

    const serviceType = document.getElementById('serviceType');
    if (serviceType && ride.ride_type) {
      const val = String(ride.ride_type).trim();
      const has = Array.from(serviceType.options).some((o) => o.value === val);
      if (!has) {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = val;
        serviceType.appendChild(opt);
      }
      serviceType.value = val;
    }

    const scheduledAt = ride.enroute_at ?? ride.created_at ?? null;
    if (scheduledAt) {
      const d = new Date(scheduledAt);
      if (!isNaN(d.getTime())) {
        const dateInput = document.getElementById('rideDate');
        const timeInput = document.getElementById('rideTime');
        if (dateInput) {
          const yyyy = d.getFullYear();
          const mm = String(d.getMonth() + 1).padStart(2, '0');
          const dd = String(d.getDate()).padStart(2, '0');
          dateInput.value = `${yyyy}-${mm}-${dd}`;
        }
        if (timeInput) {
          const hh = String(d.getHours()).padStart(2, '0');
          const min = String(d.getMinutes()).padStart(2, '0');
          timeInput.value = `${hh}:${min}`;
        }
      }
    }

    // Pickup / drop-off
    const pickupLocation = document.getElementById('pickupLocation');
    const dropoffLocation = document.getElementById('dropoffLocation');
    if (pickupLocation) pickupLocation.value = ride.pickup_addr || '';
    if (dropoffLocation) dropoffLocation.value = ride.dest_addr || '';

    // Recalculate route on the map
    if (pickupLocation && dropoffLocation && pickupLocation.value && dropoffLocation.value) {
      if (directionsService && directionsRenderer) {
        calculateRouteAndFare(pickupLocation.value, dropoffLocation.value);
      } else {
        pendingPickupAddr = pickupLocation.value;
        pendingDropoffAddr = dropoffLocation.value;
      }
    }

    // Fare / distance / eta
    const estimatedFare = document.getElementById('estimatedFare');
    if (estimatedFare && ride.fare_eur != null && ride.fare_eur !== '') {
      const numericFare = parseFloat(ride.fare_eur);
      if (!isNaN(numericFare)) {
        estimatedFare.value = `€${numericFare.toFixed(2)}`;
        currentFare = numericFare;
        hasDbFare = true;
      }
    }
    const distance = document.getElementById('distance');
    if (distance && ride.distance_km != null && ride.distance_km !== '') {
      const distKm = parseFloat(ride.distance_km);
      if (!isNaN(distKm)) {
        distance.value = `${distKm} km`;
        currentDistance = distKm;
      }
    }
    const estimatedTime = document.getElementById('estimatedTime');
    if (estimatedTime && ride.duration_min != null && ride.duration_min !== '') {
      const mins = parseInt(ride.duration_min, 10);
      if (!isNaN(mins)) {
        const hours = Math.floor(mins / 60);
        const rem = mins % 60;
        currentDuration = mins;
        estimatedTime.value = hours > 0 ? `${hours}h ${rem}m` : `${rem}m`;
      }
    }

    // Prefill selected driver if ride is already assigned
    const driver = result.data.driver;
    if (driver) {
      const driverInput = document.getElementById('driverSearchInput');
      const driverHidden = document.getElementById('driverSelect');
      const name = driver.full_name || driver.name || 'Driver';
      if (driverInput) driverInput.value = `${name} — ${driver.vehicle_make || ''}`;
      if (driverHidden) driverHidden.value = driver.id;
      // Start live tracking for the already-assigned driver
      assignedDriverId = driver.id;
      startDriverTracking();
    }

    if (isCorporateViewMode) {
      applyCorporateViewMode();
    }
  } catch (err) {
    console.error('Error loading corporate ride:', err);
    showToast('Failed to load corporate ride data');
  }
}

function setupCorporateEmployeeSelect() {
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
    const emp = corporateEmployeesList.find((e) => String(e.id) === String(id));
    if (emp) {
      phoneInput.value = stripIrelandCountryCode(emp.phone);
      if (customerIdInput) customerIdInput.value = id;
    }
  });
}

function applyCorporateViewMode() {
  // Disable every form control on the page for read-only inspection
  const root = document.querySelector('main');
  if (root) {
    root.querySelectorAll('input, select, textarea').forEach((el) => {
      el.setAttribute('disabled', 'disabled');
    });
  }
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    assignBtn.disabled = true;
    assignBtn.style.opacity = '0.55';
    assignBtn.style.cursor = 'not-allowed';
    const btnText = document.getElementById('btnText');
    if (btnText) btnText.textContent = 'Driver Assigned';
  }
  const cancelBtn = document.getElementById('cancelRideBtn');
  if (cancelBtn) cancelBtn.style.display = 'none';
}

// Load approved drivers from database
let approvedDrivers = [];

async function loadDrivers() {
  try {
    const response = await fetch('api/get_drivers.php?status=approved&limit=500');
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) throw new Error('Failed to fetch drivers');
    const result = await response.json();
    if (result.success && result.data) {
      approvedDrivers = result.data;
      renderDriverDropdown(approvedDrivers);
    }
  } catch (error) {
    console.error('Error loading drivers:', error);
  }
}

function renderDriverDropdown(drivers) {
  const list = document.getElementById('driverDropdownList');
  if (!list) return;
  list.innerHTML = '';
  if (!drivers || drivers.length === 0) {
    list.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">No approved drivers found</div>';
    return;
  }
  drivers.forEach((driver) => {
    const name = driver.full_name || driver.name || 'Driver';
    const vehicle = driver.vehicle_make || driver.vehicle_number || '';
    const phone = driver.phone || '';
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action border-0 px-3 py-2';
    item.style.cssText = 'font-size:0.8125rem; cursor:pointer; border-bottom:1px solid #F4F4F5 !important;';
    item.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px; flex-shrink:0;">${name.trim().split(/\s+/).map(p=>p[0]).slice(0,2).join('').toUpperCase()}</div>
        <div style="min-width:0;">
          <div class="fw-semibold text-truncate" style="color:#18181B; font-size:0.8125rem;">${name}</div>
          <div class="text-truncate" style="font-size:0.72rem; color:#71717A;">${vehicle}${phone ? ' &middot; ' + phone : ''}</div>
        </div>
      </div>
    `;
    item.addEventListener('mousedown', (e) => { e.preventDefault(); selectDriver(driver); });
    list.appendChild(item);
  });
}

function selectDriver(driver) {
  const input = document.getElementById('driverSearchInput');
  const hidden = document.getElementById('driverSelect');
  const name = driver.full_name || driver.name || 'Driver';
  if (input) input.value = `${name} — ${driver.vehicle_make || ''}`;
  if (hidden) hidden.value = driver.id;
  hideDriverDropdown();
}

function showDriverDropdown() {
  const list = document.getElementById('driverDropdownList');
  if (!list) return;
  const input = document.getElementById('driverSearchInput');
  // When opened with no search term, show the full approved list — or a loading
  // hint if the fetch hasn't finished yet (it auto-renders on completion).
  if (!input || !input.value.trim()) {
    if (approvedDrivers && approvedDrivers.length) {
      renderDriverDropdown(approvedDrivers);
    } else {
      list.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">Loading drivers…</div>';
    }
  }
  list.style.display = 'block';
}

function hideDriverDropdown() {
  const list = document.getElementById('driverDropdownList');
  if (list) list.style.display = 'none';
}

// Filter drivers as user types
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('driverSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const term = this.value.toLowerCase().trim();
      const hidden = document.getElementById('driverSelect');
      if (hidden) hidden.value = '';
      if (!term) {
        renderDriverDropdown(approvedDrivers);
      } else {
        const filtered = approvedDrivers.filter(d => {
          const name = (d.full_name || d.name || '').toLowerCase();
          const vehicle = (d.vehicle_make || d.vehicle_number || '').toLowerCase();
          const phone = (d.phone || '').toLowerCase();
          return name.includes(term) || vehicle.includes(term) || phone.includes(term);
        });
        renderDriverDropdown(filtered);
      }
      showDriverDropdown();
    });
  }
});

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

function showToast(message, isSuccess = false) {
  const toastEl = document.getElementById('globalToast');
  const toastMsg = document.getElementById('toastMessage');
  if (!toastEl || !toastMsg) return;

  toastMsg.textContent = message;
  toastEl.className = `toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0`;
  
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

  if (!currentRideId && !currentCorpId) {
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

  const endpoint = isCorporateMode ? 'api/assign_corporate_driver.php' : 'api/assign_driver.php';
  const pickupLocationVal = document.getElementById('pickupLocation')?.value || '';
  const dropoffLocationVal = document.getElementById('dropoffLocation')?.value || '';
  const rideDateVal = document.getElementById('rideDate')?.value || '';
  const rideTimeVal = document.getElementById('rideTime')?.value || '';
  const pickupTimeIso = (rideDateVal && rideTimeVal) ? `${rideDateVal}T${rideTimeVal}:00` : '';

  const payload = isCorporateMode
    ? {
        corp_id: currentCorpId,
        driver_id: selectedDriverId,
        distance_km: currentDistance,
        duration_min: currentDuration,
        fare_eur: currentFare,
        service_type: serviceType,
        pickup_addr: pickupLocationVal,
        dest_addr: dropoffLocationVal,
        pickup_time: pickupTimeIso,
        pickup_lat: currentPickupLat,
        pickup_lng: currentPickupLng,
        dest_lat: currentDropLat,
        dest_lng: currentDropLng,
      }
    : {
        ride_id: currentRideId,
        driver_id: selectedDriverId,
        distance_km: currentDistance,
        duration_min: currentDuration,
        fare_eur: currentFare,
        service_type: serviceType,
      };

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (response.status === 401) {
      window.location.href = '/';
      return;
    }

    const result = await response.json();

    if (result.success) {
      // Start / refresh live tracking for the newly assigned driver
      assignedDriverId = selectedDriverId;
      stopDriverTracking();
      startDriverTracking();

      const modal = new bootstrap.Modal(
        document.getElementById('driverAssignedModal')
      );
      modal.show();

      const goToPreorderBtn = document.getElementById('goToPreorderBtn');
      const newBtn = goToPreorderBtn.cloneNode(true);
      goToPreorderBtn.parentNode.replaceChild(newBtn, goToPreorderBtn);

      const redirectTo = isCorporateMode ? 'corporate_rides.php' : 'preorder.php';
      newBtn.addEventListener('click', () => {
        window.location.href = redirectTo;
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

function openCancelRideModal() {
  if (!currentRideId && !currentCorpId) {
    showToast('No ride selected to cancel.');
    return;
  }
  const modalEl = document.getElementById('cancelRideModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

async function confirmCancelRide() {
  if (!currentRideId && !currentCorpId) {
    showToast('No ride selected to cancel.');
    return;
  }

  const confirmBtn = document.getElementById('confirmCancelRideBtn');
  const confirmText = document.getElementById('confirmCancelText');
  const confirmSpinner = document.getElementById('confirmCancelSpinner');

  if (confirmBtn) confirmBtn.disabled = true;
  if (confirmText) confirmText.textContent = 'Cancelling...';
  if (confirmSpinner) confirmSpinner.style.display = 'inline-block';

  const endpoint = isCorporateMode ? 'api/update_corporate_ride_status.php' : 'api/update_ride_status.php';
  const payload = isCorporateMode
    ? { corp_id: currentCorpId, status: 'Cancelled' }
    : { ride_id: currentRideId, status: 'cancelled' };
  const redirectTo = isCorporateMode ? 'corporate_rides.php' : 'preorder.php';

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (response.status === 401) { window.location.href = '/'; return; }
    const result = await response.json();

    if (result.success) {
      const modalEl = document.getElementById('cancelRideModal');
      if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
      showToast('Ride cancelled successfully', true);
      setTimeout(() => { window.location.href = redirectTo; }, 900);
    } else {
      showToast('Error cancelling ride: ' + (result.error || 'Unknown error'));
      if (confirmBtn) confirmBtn.disabled = false;
      if (confirmText) confirmText.textContent = 'Yes, Cancel Ride';
      if (confirmSpinner) confirmSpinner.style.display = 'none';
    }
  } catch (error) {
    console.error('Error cancelling ride:', error);
    showToast('Failed to cancel ride. Please try again.');
    if (confirmBtn) confirmBtn.disabled = false;
    if (confirmText) confirmText.textContent = 'Yes, Cancel Ride';
    if (confirmSpinner) confirmSpinner.style.display = 'none';
  }
}

// ── View mode helpers ─────────────────────────────────────────────────────

function applyViewModeLayout() {
  const mainEl = document.querySelector('main.main-content');
  if (mainEl) mainEl.classList.add('view-mode-active');

  // Prevent page-level scrolling in tracking view
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';

  const setMapHeight = () => {
    const mEl = document.querySelector('main.main-content');
    const mainTop = mEl ? Math.round(mEl.getBoundingClientRect().top) : 0;
    // 20px top padding + 20px bottom padding + 4px safety margin
    const availH = window.innerHeight - mainTop - 44;
    const container = document.getElementById('mapContainer');
    const mapEl = document.getElementById('map');
    if (container) { container.style.height = availH + 'px'; container.style.minHeight = availH + 'px'; }
    if (mapEl)      { mapEl.style.height = availH + 'px'; mapEl.style.minHeight = availH + 'px'; }
    if (map && typeof google !== 'undefined') google.maps.event.trigger(map, 'resize');
  };
  setMapHeight();
  window.addEventListener('resize', setMapHeight);
}


function createPinIcon(color) {
  const svg = encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">' +
    '<path d="M14 0C6.27 0 0 6.27 0 14c0 9.63 14 22 14 22S28 23.63 28 14C28 6.27 21.73 0 14 0z" fill="' + color + '"/>' +
    '<circle cx="14" cy="14" r="6" fill="white"/>' +
    '</svg>'
  );
  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + svg,
    scaledSize: new google.maps.Size(28, 36),
    anchor: new google.maps.Point(14, 36),
  };
}

function geoDistanceMeters(lat1, lng1, lat2, lng2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateDriverToPickupRoute(driverLat, driverLng) {
  if (!directionsService || !driverRouteRenderer) return;
  if (!currentPickupLat || !currentPickupLng) return;
  directionsService.route({
    origin: { lat: driverLat, lng: driverLng },
    destination: { lat: currentPickupLat, lng: currentPickupLng },
    travelMode: google.maps.TravelMode.DRIVING,
  }, (result, status) => {
    if (status === google.maps.DirectionsStatus.OK) {
      driverRouteRenderer.setDirections(result);
      lastDriverRouteLat = driverLat;
      lastDriverRouteLng = driverLng;
    }
  });
}

function placeViewModeMarkers() {
  if (!map || !currentPickupLat || !currentPickupLng || !currentDropLat || !currentDropLng) return;

  if (!viewModePickupMarker) {
    viewModePickupMarker = new google.maps.Marker({
      position: { lat: currentPickupLat, lng: currentPickupLng },
      map,
      icon: createPinIcon('#22C55E'),
      title: 'Pickup',
      zIndex: 10,
    });
    const iw = new google.maps.InfoWindow({
      content: '<div style="font-size:13px;font-weight:700;color:#18181B;">Pickup</div>' +
               '<div style="font-size:12px;color:#52525B;margin-top:2px;">' +
               (document.getElementById('pickupLocation')?.value || '') + '</div>',
    });
    viewModePickupMarker.addListener('click', () => iw.open(map, viewModePickupMarker));
  }

  if (!viewModeDropoffMarker) {
    viewModeDropoffMarker = new google.maps.Marker({
      position: { lat: currentDropLat, lng: currentDropLng },
      map,
      icon: createPinIcon('#E11D48'),
      title: 'Dropoff',
      zIndex: 10,
    });
    const iw = new google.maps.InfoWindow({
      content: '<div style="font-size:13px;font-weight:700;color:#18181B;">Dropoff</div>' +
               '<div style="font-size:12px;color:#52525B;margin-top:2px;">' +
               (document.getElementById('dropoffLocation')?.value || '') + '</div>',
    });
    viewModeDropoffMarker.addListener('click', () => iw.open(map, viewModeDropoffMarker));
  }
}


// ── Bearing & icon helpers ─────────────────────────────────────────────────

function computeBearing(fromLat, fromLng, toLat, toLng) {
  if (fromLat === toLat && fromLng === toLng) return null;
  const φ1 = fromLat * Math.PI / 180;
  const φ2 = toLat   * Math.PI / 180;
  const Δλ = (toLng - fromLng) * Math.PI / 180;
  const y  = Math.sin(Δλ) * Math.cos(φ2);
  const x  = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
  return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

function buildDriverIcon(bearingDeg, rideStatus) {
  const s   = (rideStatus || '').toLowerCase();
  const col = ['on_trip','started','in_progress','trip_started'].includes(s)          ? '#3B82F6'
            : ['arrived_at_pickup','driver_arrived','arrived'].includes(s)             ? '#22C55E'
            : '#f37a20';
  const b   = Math.round((bearingDeg || 0) % 360);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44"><g transform="translate(22,22) rotate(${b})"><ellipse cx="0" cy="1" rx="13" ry="11" fill="rgba(0,0,0,0.18)"/><rect x="-9" y="-12" width="18" height="24" rx="5" fill="${col}" stroke="white" stroke-width="1.5"/><rect x="-7" y="-10" width="14" height="8" rx="3" fill="rgba(255,255,255,0.45)"/><rect x="-6" y="6" width="12" height="5" rx="2" fill="rgba(255,255,255,0.25)"/><rect x="-14" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="-14" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><polygon points="0,-19 -5,-12 5,-12" fill="white" opacity="0.9"/></g></svg>`;
  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
    scaledSize: new google.maps.Size(44, 44),
    anchor: new google.maps.Point(22, 22),
  };
}

// ── Route progress ─────────────────────────────────────────────────────────

function storeRoutePolyline(directionsResult) {
  try {
    const route = directionsResult && directionsResult.routes && directionsResult.routes[0];
    if (!route) return;
    currentRoutePath = [];
    for (const leg of route.legs) {
      for (const step of leg.steps) {
        if (Array.isArray(step.path)) {
          step.path.forEach(p => currentRoutePath.push({ lat: p.lat(), lng: p.lng() }));
        } else if (step.polyline && step.polyline.points && typeof google !== 'undefined' && google.maps.geometry) {
          google.maps.geometry.encoding.decodePath(step.polyline.points)
            .forEach(p => currentRoutePath.push({ lat: p.lat(), lng: p.lng() }));
        }
      }
    }
  } catch (e) {
    console.warn('storeRoutePolyline:', e);
  }
}

function findNearestRouteIndex(driverLat, driverLng) {
  let nearestIdx = 0, minDist = Infinity;
  for (let i = 0; i < currentRoutePath.length; i++) {
    const d = geoDistanceMeters(driverLat, driverLng, currentRoutePath[i].lat, currentRoutePath[i].lng);
    if (d < minDist) { minDist = d; nearestIdx = i; }
  }
  return nearestIdx;
}

function updateRouteProgress(driverLat, driverLng) {
  if (!currentRoutePath.length || !map) return;
  const idx = findNearestRouteIndex(driverLat, driverLng);
  const completedPath = currentRoutePath.slice(0, Math.max(1, idx + 1));
  const remainingPath = currentRoutePath.slice(idx);

  if (completedPolyline) { completedPolyline.setMap(null); completedPolyline = null; }
  if (remainingPolyline) { remainingPolyline.setMap(null); remainingPolyline = null; }

  if (completedPath.length >= 2) {
    completedPolyline = new google.maps.Polyline({
      path: completedPath, strokeColor: '#A1A1AA',
      strokeOpacity: 0.45, strokeWeight: 5, zIndex: 1, map,
    });
  }
  if (remainingPath.length >= 2) {
    remainingPolyline = new google.maps.Polyline({
      path: remainingPath, strokeColor: '#3B82F6',
      strokeOpacity: 0.9, strokeWeight: 5, zIndex: 2, map,
    });
  }
}

function clearRouteProgress() {
  if (completedPolyline) { completedPolyline.setMap(null); completedPolyline = null; }
  if (remainingPolyline) { remainingPolyline.setMap(null); remainingPolyline = null; }
  routeProgressActive = false;
}

// ── Ride status state machine ──────────────────────────────────────────────

function handleRideStatusChange(oldStatus, newStatus) {
  if (!newStatus) return;
  const s = newStatus.toLowerCase();
  const isArrived   = ['arrived_at_pickup','driver_arrived','arrived'].includes(s);
  const isOnTrip    = ['on_trip','started','in_progress','trip_started'].includes(s);
  const isCompleted = ['completed','finished','done'].includes(s);
  const isCancelled = ['cancelled','canceled'].includes(s);

  if (isArrived) {
    if (driverRouteRenderer) driverRouteRenderer.setMap(null);
    showRideStatusNotification('Driver arrived at pickup', 'arrived');
    updateDispatcherOverlayStatus('Arrived at Pickup', '#22C55E');
  }

  if (isOnTrip && !routeProgressActive) {
    if (driverRouteRenderer) driverRouteRenderer.setMap(null);
    routeProgressActive = true;
    if (directionsRenderer) directionsRenderer.setOptions({ suppressPolylines: true });
    showRideStatusNotification('Trip in progress', 'on_trip');
    updateDispatcherOverlayStatus('On Trip', '#3B82F6');
  }

  if (isCompleted) {
    showRideStatusNotification('Trip completed', 'completed');
    stopDriverTracking();
    if (directionsRenderer) directionsRenderer.setMap(null);
    showCompletionOverlay();
    return;
  }

  if (isCancelled) {
    showRideStatusNotification('Ride cancelled', 'cancelled');
    stopDriverTracking();
  }
}

function showRideStatusNotification(message, type) {
  const cfg = {
    arrived:   { bg: '#F0FDF4', text: '#16A34A', border: '#DCFCE7', icon: 'bi-geo-alt-fill' },
    on_trip:   { bg: '#EFF6FF', text: '#2563EB', border: '#DBEAFE', icon: 'bi-car-front-fill' },
    completed: { bg: '#F0FDF4', text: '#16A34A', border: '#DCFCE7', icon: 'bi-check-circle-fill' },
    cancelled: { bg: '#FFF1F2', text: '#E11D48', border: '#FFE4E6', icon: 'bi-x-circle-fill' },
  };
  const c = cfg[type] || { bg: '#FAFAFA', text: '#18181B', border: '#EBEBEB', icon: 'bi-info-circle' };

  document.getElementById('rideStatusBanner')?.remove();
  const banner = document.createElement('div');
  banner.id = 'rideStatusBanner';
  banner.style.cssText = `position:absolute;top:14px;right:14px;z-index:15;background:${c.bg};border:1px solid ${c.border};border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.12);padding:10px 14px;display:flex;align-items:center;gap:8px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;pointer-events:none;animation:slideInRight 0.3s ease-out;`;
  banner.innerHTML = `<i class="bi ${c.icon}" style="color:${c.text};font-size:15px;"></i><span style="font-size:0.8125rem;font-weight:600;color:${c.text};">${message}</span>`;
  const container = document.getElementById('mapContainer');
  if (container) container.appendChild(banner);
  if (type !== 'completed' && type !== 'cancelled') setTimeout(() => banner?.remove(), 5000);
}

function updateDispatcherOverlayStatus(label, color) {
  const dot = document.getElementById('liveTrackingDot');
  const lbl = document.getElementById('liveTrackingLabel');
  if (dot) dot.style.background = color;
  if (lbl) { lbl.textContent = label; lbl.style.color = color; }
}

function showCompletionOverlay() {
  const container = document.getElementById('mapContainer');
  if (!container) return;
  document.getElementById('tripCompletionOverlay')?.remove();
  const overlay = document.createElement('div');
  overlay.id = 'tripCompletionOverlay';
  overlay.style.cssText = 'position:absolute;inset:0;z-index:20;background:rgba(255,255,255,0.92);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;backdrop-filter:blur(4px);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  overlay.innerHTML = `
    <div style="width:72px;height:72px;background:#F0FDF4;border-radius:50%;display:flex;align-items:center;justify-content:center;">
      <i class="bi bi-check-circle-fill" style="color:#22C55E;font-size:36px;"></i>
    </div>
    <div style="text-align:center;">
      <div style="font-size:1.25rem;font-weight:700;color:#18181B;letter-spacing:-0.02em;">Trip Completed</div>
      <div style="font-size:0.8125rem;color:#71717A;margin-top:4px;">The driver has reached the destination.</div>
    </div>
    <a href="preorder.php" style="height:40px;background:#22C55E;color:#fff;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;padding:0 24px;display:inline-flex;align-items:center;text-decoration:none;">Back to Live Orders</a>
  `;
  container.appendChild(overlay);
}

window.addEventListener('beforeunload', () => stopDriverTracking());

// Initialize event listener when document is ready
document.addEventListener('DOMContentLoaded', function() {
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    // Remove any existing listeners and add new one
    assignBtn.replaceWith(assignBtn.cloneNode(true));
    document.getElementById('assignDriverBtn').addEventListener('click', assignDriver);
  }

  const cancelBtn = document.getElementById('cancelRideBtn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', openCancelRideModal);
  }

  const confirmCancelBtn = document.getElementById('confirmCancelRideBtn');
  if (confirmCancelBtn) {
    confirmCancelBtn.addEventListener('click', confirmCancelRide);
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
