<?php
session_start();

require_once 'auth/require_login_redirect.php';
require_once 'auth/role_guard.php';
$isDispatcher = isDispatcherRole();
require('modules/head.php');

?>
<!DOCTYPE html>
<html lang="en">
  <body>
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <div class="rounded-3 border mt-4 overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">

    <div class="table-responsive" style="min-height:400px;">
      <table class="table mb-0" style="border-collapse:collapse;">
        <thead>
          <tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
            <?php /* Ride ID and Company columns hidden on request
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Ride ID</th>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Company</th>
            */ ?>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Employee</th>
            <th class="fw-semibold px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:260px;">Pickup</th>
            <th class="fw-semibold px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:260px;">Destination</th>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Date &amp; Time</th>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
            <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
            <?php if (!$isDispatcher): ?>
            <th class="fw-semibold text-nowrap px-4 py-2 text-end" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="ridesTableBody"></tbody>
      </table>
    </div>

    <div class="px-4 py-3" style="border-top:1px solid #EBEBEB;">
      <div id="corporatePaginationContainer"></div>
    </div>

  </div>

</main>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-4" style="border-radius:14px; max-width:440px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

      <div class="d-flex align-items-center justify-content-between mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
        <div>
          <h5 class="fw-bold mb-0" style="color:#18181B; font-size:0.9375rem;">Update Ride Status</h5>
          <p class="mb-0 mt-1" style="font-size:0.78rem; color:#A1A1AA;">Select a new status for this ride</p>
        </div>
        <button type="button" class="btn d-flex align-items-center justify-content-center p-0" data-bs-dismiss="modal"
          style="width:30px; height:30px; border-radius:7px; border:1.5px solid #EBEBEB; background:#fff; color:#71717A;"
          onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20'; this.style.borderColor='#f37a20';"
          onmouseout="this.style.background='#fff'; this.style.color='#71717A'; this.style.borderColor='#EBEBEB';">
          <i class="bi bi-x-lg" style="font-size:12px;"></i>
        </button>
      </div>

      <input type="hidden" id="rideStatusSelect" value="searching" />
      <div class="mb-4 d-flex flex-column gap-2">
        <label class="form-label fw-semibold mb-1" style="font-size:0.8125rem; color:#18181B;">Status</label>
        <button type="button" class="status-option" data-value="searching" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#f37a20;"></span>
          <span class="status-label">Searching</span>
          <span class="status-desc">Looking for a driver</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
        <button type="button" class="status-option" data-value="assigned" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#2563EB;"></span>
          <span class="status-label">Assigned</span>
          <span class="status-desc">Driver has been assigned</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
        <button type="button" class="status-option" data-value="enroute" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#4F46E5;"></span>
          <span class="status-label">Enroute</span>
          <span class="status-desc">Driver heading to pickup</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
        <button type="button" class="status-option" data-value="scheduled" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#7C3AED;"></span>
          <span class="status-label">Scheduled</span>
          <span class="status-desc">Pre-booked for later</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
        <button type="button" class="status-option" data-value="completed" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#16A34A;"></span>
          <span class="status-label">Completed</span>
          <span class="status-desc">Trip finished</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
        <button type="button" class="status-option status-option--danger" data-value="cancelled" onclick="pickStatus(this)">
          <span class="status-dot" style="background:#E11D48;"></span>
          <span class="status-label">Cancelled</span>
          <span class="status-desc">Cancel this ride</span>
          <i class="bi bi-check-lg status-check"></i>
        </button>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn fw-semibold px-4"
          style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
          onmouseover="this.style.borderColor='#18181B';"
          onmouseout="this.style.borderColor='#EBEBEB';"
          data-bs-dismiss="modal">Cancel
        </button>
        <button type="button" class="btn fw-semibold px-5" id="saveRideStatusBtn"
          style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
          onmouseover="this.style.background='#d96010';"
          onmouseout="this.style.background='#f37a20';">Save
        </button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 p-4 text-center" style="border-radius:14px; max-width:420px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

      <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
        style="width:56px; height:56px; background:#FFF7ED; border-radius:50%;">
        <i class="bi bi-exclamation-triangle" style="font-size:1.5rem; color:#F97316;"></i>
      </div>
      <h5 class="fw-bold mb-1" style="color:#18181B;">Delete Corporate Ride?</h5>
      <p class="mb-4" style="font-size:0.845rem; color:#71717A;">This ride will be permanently removed and cannot be recovered.</p>

      <div class="d-flex justify-content-center gap-2">
        <button type="button" class="btn fw-semibold px-4"
          style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
          onmouseover="this.style.borderColor='#18181B';"
          onmouseout="this.style.borderColor='#EBEBEB';"
          data-bs-dismiss="modal">Cancel
        </button>
        <button type="button" class="btn fw-semibold px-4" id="confirmDeleteRideBtn"
          style="height:38px; background:#E11D48; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
          onmouseover="this.style.background='#BE123C';"
          onmouseout="this.style.background='#E11D48';">
          <i class="bi bi-trash3 me-1" style="font-size:13px;"></i>Delete
        </button>
      </div>

    </div>
  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pagination.js"></script>
    <script>
      // Dispatcher role: read-only here — no Edit/Delete on App Rides.
      const IS_DISPATCHER_ROLE = <?php echo json_encode($isDispatcher); ?>;
    </script>
    <script src="js/application_rides.js"></script>
  </body>
</html>
