<?php
session_start();

require_once 'auth/require_login_redirect.php';
Permission::requireCan('fleet', 'view');
require_once 'auth/role_guard.php';
$isDispatcher = isDispatcherRole();
$pageTitle = 'Fleet Registry | Powercabs Dispatcher';
require('modules/head.php');
?>

<!DOCTYPE html>
<html lang="en">
  <body class="<?php echo $isDispatcher ? 'is-dispatcher-role' : ''; ?>">
   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main id="app-content" class="main-content p-4" style="background: #f4f4f5; min-height: 100vh;">

    <?php @require('modules/bodyHeader.php'); ?>    

    <div class="rounded-3 border mt-4 overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);">
      <div class="p-4">
        <div class="d-flex align-items-center gap-2 mb-4 p-1 rounded-2 d-inline-flex" style="background:#F4F4F5;">
          <button
            id="driverViewBtn"
            class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3"
            style="border-radius:6px; height:32px; font-size:0.9125rem; background:#f37a20; color:#ffff; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.08);"
            onclick="switchTableView('driver')"
          >
            <i class="bi bi-person-badge" style="font-size:13px;"></i> Drivers
          </button>
          <button
            id="customerViewBtn"
            class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3"
            style="border-radius:6px; height:32px; font-size:0.9125rem; background:transparent; color:#71717A; border:none;"
            onclick="switchTableView('customer')"
          >
            <i class="bi bi-people" style="font-size:13px;"></i> Passengers
          </button>
        </div>

        <div id="driverTable" class="d-block mb-3" style="min-height:362px;">
          <div class="table-responsive rounded-2" style="border:1px solid #EBEBEB;">
            <table class="table mb-0" style="border-collapse:collapse; min-width:900px;">
              <thead>
                <tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Email</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Phone</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Vehicle</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Ordered Time</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Vehicle No.</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">License</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Vehicle Reg</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Insurance</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Rides</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
                </tr>
              </thead>
              <tbody id="driverTableBody"></tbody>
            </table>
          </div>
        </div>

        <div id="customerTable" class="d-none mb-3" style="min-height:362px;">
          <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB;">
            <table class="table mb-0" style="border-collapse:collapse;">
              <thead>
                <tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Email</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Phone</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Service Type</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Ordered Time</th>
                  <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Email Verified</th>
                </tr>
              </thead>
              <tbody id="customerTableBody"></tbody>
            </table>
          </div>
        </div>

<div id="driverPaginationContainer" class="mt-3"></div>
<div id="passengerPaginationContainer" class="mt-3" style="display:none;"></div>

      </div>
    </div>

  <div id="imageModal" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="z-index:9999;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,0.70); backdrop-filter:blur(3px);"></div>
    <div class="position-relative" style="z-index:10000; width:90%; max-width:780px; max-height:90vh; animation:modalFadeIn 0.2s ease-out;">
      <div class="d-flex flex-column overflow-hidden" style="background:#fff; border-radius:12px; box-shadow:0 20px 50px rgba(0,0,0,0.30); max-height:90vh;">

        <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid #EBEBEB; background:#FAFAFA; flex-shrink:0;">
          <span id="modalTitle" class="fw-semibold" style="font-size:0.9375rem; color:#18181B;">Document Preview</span>
          <button class="modal-close-btn btn d-flex align-items-center justify-content-center p-0" aria-label="Close"
            style="width:30px; height:30px; border-radius:7px; border:1.5px solid #EBEBEB; background:#fff; color:#71717A;"
            onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20'; this.style.borderColor='#f37a20';"
            onmouseout="this.style.background='#fff'; this.style.color='#71717A'; this.style.borderColor='#EBEBEB';">
            <i class="bi bi-x-lg" style="font-size:12px;"></i>
          </button>
        </div>

        <div class="d-flex align-items-center justify-content-center p-4 overflow-auto" style="background:#F4F4F5; flex:1;">
          <img id="modalImage" src="" alt="Document"
            class="rounded-2"
            style="max-width:100%; max-height:calc(90vh - 120px); object-fit:contain; box-shadow:0 4px 16px rgba(0,0,0,0.12);" />
        </div>

      </div>
    </div>
  </div>

  </main>

<script src="js/app.js"></script>
<script src="js/fleetRegistry.js"></script>
  </body>
</html>