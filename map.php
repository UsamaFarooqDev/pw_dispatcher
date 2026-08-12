<?php
session_start();

require_once 'auth/require_login_redirect.php';
$pageTitle = 'Live Map | Powercabs Dispatcher';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main id="app-content" class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <!-- View toggle -->
  <div class="d-flex align-items-center gap-2 mt-4 mb-3">
    <button type="button" class="map-view-toggle is-active" id="btnTableView" onclick="switchMapView('table')">
      <i class="bi bi-table"></i> <span>Live Driver Coordinates</span>
    </button>
    <button type="button" class="map-view-toggle" id="btnMapView" onclick="switchMapView('map')">
      <i class="bi bi-map"></i> <span>Live Map</span>
    </button>
    <span id="tableOnlineCount" class="d-inline-flex align-items-center gap-1 fw-semibold ms-auto" style="font-size:0.75rem; color:#22C55E; background:#F0FDF4; border:1px solid #DCFCE7; padding:4px 10px; border-radius:999px;">
      <span style="width:6px; height:6px; border-radius:50%; background:#22C55E; display:inline-block;"></span>
      <span id="tableOnlineCountVal">0</span> active drivers
    </span>
  </div>

  <!-- TABLE VIEW (default) -->
  <div id="viewTable" class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
    <div class="p-3 d-flex align-items-center gap-3" style="border-bottom:1px solid #F4F4F5;">
      <div class="position-relative" style="max-width:280px; flex:1;">
        <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
        <input type="text" id="tableSearchInput" placeholder="Search by name, email, phone..." class="form-control"
          style="height:36px; border:1px solid #E4E4E7; border-radius:8px; padding-left:32px; font-size:0.8125rem; background:#FAFAFA; color:#18181B;"
          onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
          onblur="this.style.borderColor='#E4E4E7'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
      </div>
    </div>
    <div class="table-responsive" style="max-height:calc(100vh - 260px); overflow-y:auto;">
      <table class="table mb-0" style="border-collapse:collapse; min-width:900px;">
        <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB; position:sticky; top:0; z-index:2;">
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:160px;">Driver Name</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:180px;">Email</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Phone</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Latitude</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Longitude</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Last Location</th>
          <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Last Fix</th>
        </tr></thead>
        <tbody id="driverTableBody">
          <tr><td colspan="8" class="text-center py-4 text-muted">Loading drivers...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- MAP VIEW (hidden by default) -->
  <div id="viewMap" style="display:none; height:calc(100vh - 200px);">
  <div class="d-flex gap-3" style="height:100%;">

    <div class="position-relative flex-grow-1 rounded-3 overflow-hidden" style="border:1.5px solid #EBEBEB; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
      <div id="map" style="width:100%; height:100%; border:0;"></div>

      <!-- Driver preference filters -->
      <div class="position-absolute top-0 end-0 m-3 d-flex gap-2 flex-wrap" style="z-index:5;">
        <button type="button" class="map-filter-btn" data-filter="acceptCardRides" onclick="toggleMapFilter(this)">
          <i class="bi bi-credit-card"></i> Card Rides
        </button>
        <button type="button" class="map-filter-btn" data-filter="petsAllowed" onclick="toggleMapFilter(this)">
          <i class="bi bi-hearts"></i> Pets Allowed
        </button>
        <button type="button" class="map-filter-btn" data-filter="personWithDisabilities" onclick="toggleMapFilter(this)">
          <i class="bi bi-person-wheelchair"></i> Wheelchair
        </button>
        <button type="button" class="map-filter-btn" data-filter="acceptDeliveryRides" onclick="toggleMapFilter(this)">
          <i class="bi bi-box-seam"></i> Delivery
        </button>
      </div>

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
  </div><!-- /viewMap -->

</main>

    <script src="js/map.js"></script>
  </body>
</html>
