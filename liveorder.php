<?php
session_start();

require_once 'auth/require_login_redirect.php';
$pageTitle = 'Live Orders | Powercabs Dispatcher';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main id="app-content" class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <!-- Tab header strip — moved into the sidebar's Live Orders dropdown; these
       buttons stay in the DOM (hidden) purely as the click targets that
       TAB_CONFIG/setupTabSwitching() already know how to drive. The visible
       counts now live on the sidebar's #count-* badges instead. -->
  <nav class="po-tabs mt-4" role="tablist" aria-label="Live orders sections">
        <button type="button" class="po-tab is-active tab-btn active-tab" id="tab-unassigned" role="tab" aria-selected="true">
          <i class="bi bi-hourglass-split po-tab__icon"></i>
          <span class="po-tab__label">Unassigned</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-assigned" role="tab" aria-selected="false">
          <i class="bi bi-person-check po-tab__icon"></i>
          <span class="po-tab__label">Assigned</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-enroute" role="tab" aria-selected="false">
          <i class="bi bi-geo-alt po-tab__icon"></i>
          <span class="po-tab__label">Enroute</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-on-trip" role="tab" aria-selected="false" style="display:none;">
          <i class="bi bi-car-front po-tab__icon"></i>
          <span class="po-tab__label">On Trip</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-scheduled" role="tab" aria-selected="false">
          <i class="bi bi-calendar-check po-tab__icon"></i>
          <span class="po-tab__label">Pre-Order</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-cancelled" role="tab" aria-selected="false">
          <i class="bi bi-x-circle po-tab__icon"></i>
          <span class="po-tab__label">Cancelled</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-finished" role="tab" aria-selected="false">
          <i class="bi bi-check-circle po-tab__icon"></i>
          <span class="po-tab__label">Completed</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-meet-greet" role="tab" aria-selected="false">
          <i class="bi bi-airplane po-tab__icon"></i>
          <span class="po-tab__label">Meet &amp; Greet</span>
        </button>
  </nav>

  <!-- Table content card — now standalone since the tab bar above it moved
       into the sidebar's Live Orders dropdown. -->
  <div class="rounded-3 border overflow-hidden mt-4" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);">
    <div class="p-4 pt-3">

      <!-- Unassigned -->
      <div id="pane-unassigned" class="tab-pane-table">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;" id="actionHeader">Action</th>
            </tr></thead>
            <tbody id="unassignedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Assigned -->
      <div id="pane-assigned" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2" style="border:1px solid #EBEBEB; min-height:362px; overflow-x:auto; overflow-y:hidden;">
          <table class="table mb-0" style="border-collapse:collapse; min-width:1100px;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:120px;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:110px;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:300px;">Action</th>
            </tr></thead>
            <tbody id="assignedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Enroute -->
      <div id="pane-enroute" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Driver</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="enrouteRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- On Trip -->
      <div id="pane-ontrip" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Driver</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="onTripRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Scheduled -->
      <div id="pane-scheduled" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Category</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">AI Suggestion</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Action</th>
            </tr></thead>
            <tbody id="scheduledRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Cancelled -->
      <div id="pane-cancelled" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
            </tr></thead>
            <tbody id="cancelledRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Completed -->
      <div id="pane-completed" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2" style="border:1px solid #EBEBEB; min-height:362px; overflow-x:auto; overflow-y:hidden;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Final Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Toll Amount</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Total Charged</th>
            </tr></thead>
            <tbody id="completedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Meet & Greet -->
      <div id="pane-meetgreet" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Company</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Employee</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="meetGreetRidesBody"></tbody>
          </table>
        </div>
      </div>

      <div id="preorderPaginationContainer" class="mt-3"></div>

    </div>
  </div>

</main>

    <script src="js/liveorder.js"></script>
  </body>
</html>
