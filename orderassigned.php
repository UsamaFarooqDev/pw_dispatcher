<?php
session_start();

require_once 'auth/require_login_redirect.php';
$pageTitle = 'Assigned Orders | Powercabs Dispatcher';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>
    <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

<?php
  $_vmActive = !empty($_GET['id']) && isset($_GET['view']) && $_GET['view']==='1' && !isset($_GET['corp_id']);
  $_fromTab = isset($_GET['from']) ? $_GET['from'] : '';
  $_backUrl = 'liveorder.php';
  if ($_fromTab !== '') $_backUrl .= '#tab-' . htmlspecialchars($_fromTab, ENT_QUOTES);
?>
<main id="app-content" class="main-content p-4<?php echo $_vmActive ? ' view-mode-active' : ''; ?>" style="background:#F4F4F5; min-height:100vh;">

  <!-- Page-level loading overlay (covers form + map until data is ready) -->
  <div id="oaPageLoader" style="position:fixed; inset:0; z-index:9999; background:#F4F4F5; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px;">
    <div style="width:38px; height:38px; border:3.5px solid #E4E4E7; border-top-color:#f37a20; border-radius:50%; animation:spin .7s linear infinite;"></div>
    <div style="font-size:0.82rem; font-weight:600; color:#71717A; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">Loading order details...</div>
  </div>

  <a href="<?php echo $_backUrl; ?>" class="oa-back-btn">
    <i class="bi bi-arrow-left"></i> Back to Live Orders
  </a>

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
          <!-- Order / enroute timestamps -->
          <div style="border-top:1px solid #F4F4F5; margin-top:10px; padding-top:10px; display:flex; flex-direction:column; gap:6px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
              <span style="font-size:0.67rem; text-transform:uppercase; color:#A1A1AA; letter-spacing:0.05em; font-weight:600;">Order Placed</span>
              <span id="overlayOrderTime" style="font-size:0.76rem; color:#18181B; font-weight:600; text-align:right;">—</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
              <span style="font-size:0.67rem; text-transform:uppercase; color:#A1A1AA; letter-spacing:0.05em; font-weight:600;">Enroute Since</span>
              <span id="overlayEnrouteTime" style="font-size:0.76rem; color:#18181B; font-weight:600; text-align:right;">Not yet</span>
            </div>
          </div>
          <!-- Live ride progress status -->
          <div id="rideProgressCard" style="display:none; margin-top:10px; padding:9px 12px; border-radius:8px; background:#EFF6FF; border:1px solid #DBEAFE;">
            <div style="display:flex; align-items:center; gap:7px;">
              <i id="rideProgressIcon" class="bi bi-car-front-fill" style="font-size:14px; color:#2563EB;"></i>
              <span id="rideProgressText" style="font-size:0.78rem; font-weight:600; color:#1E40AF; line-height:1.35;"></span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

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


<!-- Global Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
  <div id="globalToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
</main>

    <script src="js/orderassigned.js"></script>
  </body>
</html>
