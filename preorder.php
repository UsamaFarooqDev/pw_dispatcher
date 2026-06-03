<?php
session_start();

require_once 'auth/require_login_redirect.php';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <div class="rounded-3 border mt-4 overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);">
    <div class="p-4">

      <nav class="po-tabs mb-4" role="tablist" aria-label="Live orders sections">
        <button type="button" class="po-tab is-active tab-btn active-tab" id="tab-unassigned" role="tab" aria-selected="true">
          <i class="bi bi-hourglass-split po-tab__icon"></i>
          <span class="po-tab__label">Unassigned</span>
          <span class="po-tab__count" id="count-unassigned">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-assigned" role="tab" aria-selected="false">
          <i class="bi bi-person-check po-tab__icon"></i>
          <span class="po-tab__label">Assigned</span>
          <span class="po-tab__count" id="count-assigned">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-on-trip" role="tab" aria-selected="false">
          <i class="bi bi-car-front po-tab__icon"></i>
          <span class="po-tab__label">On Trip</span>
          <span class="po-tab__count" id="count-on-trip">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-scheduled" role="tab" aria-selected="false">
          <i class="bi bi-calendar-check po-tab__icon"></i>
          <span class="po-tab__label">Pre-Order</span>
          <span class="po-tab__count" id="count-scheduled">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-cancelled" role="tab" aria-selected="false">
          <i class="bi bi-x-circle po-tab__icon"></i>
          <span class="po-tab__label">Cancelled</span>
          <span class="po-tab__count" id="count-cancelled">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-finished" role="tab" aria-selected="false">
          <i class="bi bi-check-circle po-tab__icon"></i>
          <span class="po-tab__label">Completed</span>
          <span class="po-tab__count" id="count-finished">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-meet-greet" role="tab" aria-selected="false">
          <i class="bi bi-airplane po-tab__icon"></i>
          <span class="po-tab__label">Meet &amp; Greet</span>
          <span class="po-tab__count" id="count-meet-greet">0</span>
        </button>
      </nav>

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
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;" id="actionHeader">Action</th>
            </tr></thead>
            <tbody id="unassignedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Assigned -->
      <div id="pane-assigned" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Action</th>
            </tr></thead>
            <tbody id="assignedRidesBody"></tbody>
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
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Category</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
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
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
            </tr></thead>
            <tbody id="cancelledRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Completed -->
      <div id="pane-completed" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
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

<style>
  /* ── Live Orders tab strip — outlined pill on active ────────────────── */
  .po-tabs {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    margin: 0 0 1.5rem;
    padding: 0;
  }
  .po-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 36px;
    padding: 0 14px;
    background: transparent;
    border: 1.5px solid transparent;
    border-radius: 8px;
    color: #71717A;
    font-family: inherit;
    font-size: 0.815rem;
    font-weight: 500;
    line-height: 1;
    letter-spacing: 0.005em;
    cursor: pointer;
    transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
  }
  .po-tab:hover {
    color: #18181B;
    border-color: #D4D4D8;
  }
  .po-tab:focus-visible {
    outline: none;
    color: #18181B;
    border-color: #52525B;
  }
  .po-tab__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    font-size: 14px;
    color: #A1A1AA;
    line-height: 1;
    transition: color 0.15s ease;
  }
  .po-tab__label { white-space: nowrap; }
  .po-tab__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 18px;
    padding: 0 6px;
    margin-left: 2px;
    background: transparent;
    color: #A1A1AA;
    font-size: 0.685rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    border-radius: 9px;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .po-tab.is-active,
  .po-tab.active-tab {
    color: #18181B;
    font-weight: 600;
    background: transparent;
    border-color: #f37a20;
    box-shadow: 0 0 0 3px rgba(243,122,32,0.12), 0 1px 2px rgba(243,122,32,0.08);
  }
  .po-tab.is-active .po-tab__icon,
  .po-tab.active-tab .po-tab__icon {
    color: #f37a20;
  }
  .po-tab.is-active .po-tab__count,
  .po-tab.active-tab .po-tab__count {
    color: #f37a20;
  }
  @media (max-width: 767.98px) {
    .po-tabs {
      flex-wrap: nowrap;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
    }
    .po-tabs::-webkit-scrollbar { display: none; }
    .po-tab { height: 34px; padding: 0 12px; font-size: 0.78rem; }
  }
  /* legacy hook (still referenced elsewhere) */
  .tab-btn.active-tab { color: #18181B; }
  #unassignedRidesBody tr, #assignedRidesBody tr,
  #scheduledRidesBody tr, #cancelledRidesBody tr, #completedRidesBody tr,
  #meetGreetRidesBody tr {
    border-bottom: 1px solid #F4F4F5;
    transition: background 0.12s;
  }
  #unassignedRidesBody tr:hover, #assignedRidesBody tr:hover,
  #scheduledRidesBody tr:hover, #cancelledRidesBody tr:hover,
  #completedRidesBody tr:hover, #meetGreetRidesBody tr:hover { background: #FAFAFA; }
  #unassignedRidesBody td, #assignedRidesBody td,
  #scheduledRidesBody td, #cancelledRidesBody td, #completedRidesBody td,
  #meetGreetRidesBody td {
    padding: 14px 16px;
    font-size: 0.845rem;
    color: #18181B;
    vertical-align: middle;
    border: none;
  }
  /* Keep the M&G action cell from being squeezed into invisibility */
  #meetGreetRidesBody td:last-child { white-space: nowrap; min-width: 130px; }
</style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pagination.js"></script>
    <script>

      // Configuration: Polling interval in milliseconds (default: 10 seconds)
      const POLLING_INTERVAL_MS = 10 * 1000; // 10 seconds

      let pollingInterval = null;
      let beepingRideIds = new Set(); // Track which rides are currently beeping
       let previousRideStatuses = new Map(); // Track previous status of each ride to detect transitions
      let currentRidesData = {
        unassigned: [],
        assigned: [],
        ontrip: [],
        scheduled: [],
        cancelled: [],
        completed: [],
        meetgreet: []
      }; // Cache current rides data to detect changes
      
      // Pagination
      let preorderPagination = null;
      const ITEMS_PER_PAGE = 10;
      let currentTab = 'unassigned'; // Track current active tab

      document.addEventListener('DOMContentLoaded', function () {
        // Setup tab switching first — must always run regardless of other errors
        setupTabSwitching();
        setupPreorderSearch();

        // Initialize pagination
        try {
          const paginationContainer = document.getElementById('preorderPaginationContainer');
          if (paginationContainer && typeof PaginationManager !== 'undefined') {
            preorderPagination = new PaginationManager({
              containerId: 'preorderPaginationContainer',
              page: 1,
              limit: ITEMS_PER_PAGE,
              total: 0,
              onPageChange: (page, limit) => {
                updateTableForCurrentTab(page, limit);
              }
            });
          }
        } catch (e) {
          console.warn('Pagination init error:', e);
        }

        // Sync beeping state
        if (window.BeepMonitor) {
          beepingRideIds = new Set(window.BeepMonitor.getBeepingRideIds());
        }

        // Load all tab data
        loadUnassignedRides(true);
        loadAssignedRides(true);
        loadOnTripRides(true);
        loadScheduledRides(true);
        loadCancelledRides(true);
        loadCompletedRides(true);
        loadMeetGreetRides(true);

        // Start polling
        startPolling();
      });

      // Cleanup on page unload
      window.addEventListener('beforeunload', function () {
        stopPolling();
      });

      function startPolling() {
        if (pollingInterval) {
          clearInterval(pollingInterval);
        }
        pollingInterval = setInterval(() => {
          // Silent status check - only update UI if there are changes
          checkRideStatusChanges();
        }, POLLING_INTERVAL_MS);
      }

      function stopPolling() {
        if (pollingInterval) {
          clearInterval(pollingInterval);
          pollingInterval = null;
        }
      }

      function toggleActionColumn(show) {
  // Toggle header
  const actionHeader = document.getElementById('actionHeader');
  if (actionHeader) {
    actionHeader.style.display = show ? '' : 'none';
  }

  // Toggle action cells (last column in each row)
  document.querySelectorAll('tbody tr').forEach(row => {
    const actionCell = row.lastElementChild;
    if (actionCell) {
      actionCell.style.display = show ? '' : 'none';
    }
  });
}

      // Silent status check - fetches data and only updates UI if there are changes
      async function checkRideStatusChanges() {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = result && result.data ? result.data : [];

          const unassignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'searching';
          });

          const assignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'assigned';
          });

          const scheduledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'upcoming' || status === 'scheduled' || status === 'pending' || status === 'awaiting_assignment';
          });

          const onTripRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ['on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived','enroute','en_route','en-route'].includes(status);
          });

          const cancelledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'cancelled' || status === 'canceled';
          });

          const completedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'completed' || status === 'finished';
          });

             // Initialize previous statuses on first check if empty
          if (previousRideStatuses.size === 0) {
            rides.forEach((ride) => {
              if (ride.id) {
                previousRideStatuses.set(ride.id, (ride.status || '').toLowerCase());
              }
            });
          }

          // Auto-transition scheduled rides based on time proximity
          await processScheduledRideTransitions(scheduledRides);

          // Check for beeping rides (always check, regardless of UI updates)
          checkForBeepingRides(unassignedRides, rides);

          // Only update UI if there are actual changes
          const unassignedChanged = hasRidesChanged(currentRidesData.unassigned, unassignedRides);
          const assignedChanged = hasRidesChanged(currentRidesData.assigned, assignedRides);
          const scheduledChanged = hasRidesChanged(currentRidesData.scheduled, scheduledRides);

          if (unassignedChanged) {
            currentRidesData.unassigned = unassignedRides;
            updateUnassignedTabCount(unassignedRides.length);
            if (currentTab === 'unassigned') {
              updatePaginationInfo(unassignedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          if (assignedChanged) {
            currentRidesData.assigned = assignedRides;
            updateAssignedTabCount(assignedRides.length);
            if (currentTab === 'assigned') {
              updatePaginationInfo(assignedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          if (scheduledChanged) {
            currentRidesData.scheduled = scheduledRides;
            updateScheduledTabCount(scheduledRides.length);
            if (currentTab === 'scheduled') {
              updatePaginationInfo(scheduledRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const cancelledChanged = hasRidesChanged(currentRidesData.cancelled, cancelledRides);
          if (cancelledChanged) {
            currentRidesData.cancelled = cancelledRides;
            updateCancelledTabCount(cancelledRides.length);
            if (currentTab === 'cancelled') {
              updatePaginationInfo(cancelledRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const completedChanged = hasRidesChanged(currentRidesData.completed, completedRides);
          if (completedChanged) {
            currentRidesData.completed = completedRides;
            updateCompletedTabCount(completedRides.length);
            if (currentTab === 'completed') {
              updatePaginationInfo(completedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const onTripChanged = hasRidesChanged(currentRidesData.ontrip, onTripRides);
          if (onTripChanged) {
            currentRidesData.ontrip = onTripRides;
            updateOnTripTabCount(onTripRides.length);
            if (currentTab === 'ontrip') {
              updatePaginationInfo(onTripRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }
        } catch (error) {
          console.error('Error checking ride status:', error);
          // Don't show error in UI during silent polling - only log it
        }
      }

      // Auto-transition scheduled rides when pickup time is ≤ 40 min away.
      //
      // Two paths:
      //   1. Pre-assigned driver present
      //        → call assign_driver.php with force_assign=true
      //          This writes driver_id + status='assigned' + updated_at in one shot —
      //          exactly the same field set written by an immediate dispatch.
      //          Supabase Real-time fires the same UPDATE event shape the driver app
      //          already subscribes to, so the trip request popup appears as normal.
      //
      //   2. No driver assigned yet
      //        → call update_ride_status.php to set status='searching'
      //          Ride is broadcast to all available drivers the usual way.
      //
      // Rides are NEVER auto-cancelled here; only a human action may cancel.
      const _transitioningRideIds = new Set();
      async function processScheduledRideTransitions(scheduledRides) {
        const now = Date.now();
        for (const ride of scheduledRides) {
          if (!ride.id) continue;
          if (_transitioningRideIds.has(ride.id)) continue;

          // Resolve scheduled time: prefer the dedicated column, fall back to meta JSON.
          let scheduledAt = ride.scheduled_at || null;
          if (!scheduledAt && ride.meta) {
            try {
              const meta = typeof ride.meta === 'string' ? JSON.parse(ride.meta) : ride.meta;
              scheduledAt = meta.scheduled_datetime || null;
            } catch (_) {}
          }
          if (!scheduledAt) continue;

          const scheduledMs = new Date(scheduledAt).getTime();
          if (isNaN(scheduledMs)) continue;

          const diffMin = (scheduledMs - now) / 60000;
          if (diffMin > 40) continue; // not yet in the activation window

          _transitioningRideIds.add(ride.id);
          try {
            if (ride.driver_id) {
              // Path 1 — pre-assigned driver: use the same assign_driver endpoint
              // so the driver app receives the identical Real-time event as an
              // instant assigned ride.
              await fetch('api/assign_driver.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  ride_id:      ride.id,
                  driver_id:    ride.driver_id,
                  force_assign: true   // bypass scheduled-status preservation
                })
              });
            } else {
              // Path 2 — no driver: open for broadcast searching.
              await fetch('api/update_ride_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ride_id: ride.id, status: 'searching' })
              });
            }
          } catch (e) {
            console.error('Scheduled transition error for ride', ride.id, e);
          } finally {
            _transitioningRideIds.delete(ride.id);
          }
        }
      }

      // Compare two arrays of rides to detect changes
      function hasRidesChanged(oldRides, newRides) {
        if (oldRides.length !== newRides.length) {
          return true;
        }

        // Create maps of ride IDs and their statuses
        const oldMap = new Map();
        oldRides.forEach(ride => {
          if (ride.id) {
            oldMap.set(ride.id, {
              status: (ride.status || '').toLowerCase(),
              updated_at: ride.updated_at
            });
          }
        });

        const newMap = new Map();
        newRides.forEach(ride => {
          if (ride.id) {
            newMap.set(ride.id, {
              status: (ride.status || '').toLowerCase(),
              updated_at: ride.updated_at
            });
          }
        });

        // Check if any ride IDs are different
        if (oldMap.size !== newMap.size) {
          return true;
        }

        // Check if any ride status or updated_at has changed
        for (const [id, newData] of newMap) {
          const oldData = oldMap.get(id);
          if (!oldData || oldData.status !== newData.status || oldData.updated_at !== newData.updated_at) {
            return true;
          }
        }

        // Check if any old ride is missing in new rides
        for (const id of oldMap.keys()) {
          if (!newMap.has(id)) {
            return true;
          }
        }

        return false;
      }

      async function loadUnassignedRides(showLoading = false) {
        const tbody = document.getElementById('unassignedRidesBody');

        if (showLoading && tbody) {
          tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-4 text-muted">Loading rides...</td></tr>';
        }

        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = result && result.data ? result.data : [];

          const unassignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'searching';
          });

             // Initialize previous statuses on first load if empty
          if (previousRideStatuses.size === 0) {
            rides.forEach((ride) => {
              if (ride.id) {
                previousRideStatuses.set(ride.id, (ride.status || '').toLowerCase());
              }
            });
          }

          // Check for rides that need beeping
          checkForBeepingRides(unassignedRides, rides);

          // Update cache
          currentRidesData.unassigned = unassignedRides;

          updateUnassignedTabCount(unassignedRides.length);
          
          // Always update table for unassigned tab (default tab)
          if (currentTab === 'unassigned' || currentTab === null || currentTab === undefined) {
            currentTab = 'unassigned';
            try {
              if (preorderPagination && typeof preorderPagination.getCurrentPage === 'function') {
                updatePaginationInfo(unassignedRides.length);
                const currentPage = preorderPagination.getCurrentPage();
                updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
              } else {
                // If pagination not ready yet, just populate all rides
                populateUnassignedTable(unassignedRides);
              }
            } catch (paginationError) {
              console.error('Error updating pagination/table:', paginationError);
              // Fallback: just populate the table
              populateUnassignedTable(unassignedRides);
            }
          }
        } catch (error) {
          console.error('Error loading unassigned rides:', error);
          console.error('Error details:', error.message, error.stack);
          if (tbody) {
            tbody.innerHTML =
              '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading rides. Please refresh the page.</td></tr>';
          }
          if (preorderPagination) {
            preorderPagination.update(0, 1);
          }
          updateUnassignedTabCount(0);
        }
      }

      function checkForBeepingRides(unassignedRides, allRides) {
        const currentBeepingIds = new Set();
            const newSearchingRides = []; // Rides that just entered searching status

          // Track current status of all rides
        const currentRideStatuses = new Map();
        allRides.forEach((ride) => {
          if (ride.id) {
              const status = (ride.status || '').toLowerCase();
            currentRideStatuses.set(ride.id, status);
          }
        });

        // Check for rides that just transitioned to 'searching' status
        currentRideStatuses.forEach((status, rideId) => {
          if (status === 'searching') {
            currentBeepingIds.add(rideId);

            // Check if this ride just entered searching status
            const previousStatus = previousRideStatuses.get(rideId);
            if (previousStatus !== 'searching') {
              // This ride just entered searching status - record timestamp
              // If ride has updated_at, use that; otherwise use current time
              const ride = allRides.find(r => r.id === rideId);
              const timestamp = ride && ride.updated_at 
                ? new Date(ride.updated_at).getTime() 
                : Date.now();

              newSearchingRides.push({
                rideId: rideId,
                timestamp: timestamp
              });
            }
                }
        });

        // Update previous statuses for next check
        previousRideStatuses = currentRideStatuses;

        // Remove rides that are no longer searching from beeping set
        beepingRideIds.forEach((rideId) => {
          if (!currentBeepingIds.has(rideId)) {
            // Ride is no longer searching, remove from beeping set
          }
        });

        // Update beeping set
        beepingRideIds = currentBeepingIds;

        // Control beep based on whether we should be beeping
        // Use global BeepMonitor to ensure beep works across all pages
        if (window.BeepMonitor) {
          if (beepingRideIds.size > 0) {
                // Pass ride IDs and timestamps for newly searching rides
            window.BeepMonitor.setBeepingRideIds(Array.from(beepingRideIds), newSearchingRides);
          } else {
             window.BeepMonitor.setBeepingRideIds([], []);
            window.BeepMonitor.stopBeep();
          }
        }
      }

      function populateUnassignedTable(rides) {
        try {
          const tbody = document.getElementById('unassignedRidesBody');
          if (!tbody) {
            console.error('unassignedRidesBody element not found');
            return;
          }

          tbody.innerHTML = '';

          if (!rides || rides.length === 0) {
            tbody.innerHTML =
              '<tr><td colspan="7" class="text-center py-4 text-muted">No unassigned rides to show</td></tr>';
            return;
          }

          rides.forEach((ride) => {
            try {
              const name = ride.passenger_name || 'N/A';
              const orderTime = formatOrderTime(ride.created_at);
              const pickup =
                ride.pickup_addr || ride.actual_start_addr || 'N/A';
              const destination =
                ride.dest_addr || ride.actual_end_addr || 'N/A';
              const status = ride.status || 'N/A';
              const fare = formatFare(ride.fare_eur, ride.estimate_fare);
              const rideId = ride.id || '';

              const row = document.createElement('tr');
              row.innerHTML = `
                <td class="ps-3">${name}</td>
                <td>${orderTime}</td>
                <td>${pickup}</td>
                <td>${destination}</td>
                <td>${renderStatusBadge(status)}</td>
                <td class="text-end pe-4">${fare}</td>
                <td class="text-end pe-4">
                  <a href="orderassigned.php?id=${rideId}" class="view-details-btn">
                    <span>View Details</span>
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </td>
              `;
              tbody.appendChild(row);
            } catch (rowError) {
              console.error('Error creating table row:', rowError, ride);
            }
          });

          applyPreorderSearchFilterForCurrentTab();
        } catch (error) {
          console.error('Error in populateUnassignedTable:', error);
          const tbody = document.getElementById('unassignedRidesBody');
          if (tbody) {
            tbody.innerHTML =
              '<tr><td colspan="7" class="text-center py-4 text-danger">Error displaying rides data</td></tr>';
          }
        }
      }


           function formatOrderTime(createdAt) {
        if (!createdAt) return 'N/A';
        const date = new Date(createdAt);
        if (Number.isNaN(date.getTime())) return createdAt;

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${day}.${month}.${year} | ${hours}:${minutes}`;
      }

      function formatFare(fareEur, estimateFare) {
        const value =
          fareEur != null
            ? parseFloat(fareEur)
            : estimateFare != null
            ? parseFloat(estimateFare)
            : null;

        if (value == null || Number.isNaN(value)) return 'N/A';
        return `€${value.toFixed(2)}`;
      }

      function updateUnassignedTabCount(count) {
        const badge = document.getElementById('count-unassigned');
        if (badge) badge.textContent = count;
      }

      function updatePaginationInfo(totalItems) {
        if (preorderPagination) {
          const currentPage = preorderPagination.getCurrentPage();
          preorderPagination.update(totalItems, currentPage);
        }
      }
      
      function updateTableForCurrentTab(page = 1, limit = ITEMS_PER_PAGE) {
        try {
          const rides = currentRidesData[currentTab] || [];
          const start = (page - 1) * limit;
          const end = start + limit;
          const paginatedRides = rides.slice(start, end);

          if (currentTab === 'unassigned') {
            populateUnassignedTable(paginatedRides);
          } else if (currentTab === 'assigned') {
            populateAssignedTable(paginatedRides);
          } else if (currentTab === 'ontrip') {
            populateOnTripTable(paginatedRides);
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(paginatedRides);
          } else if (currentTab === 'cancelled') {
            populateCancelledTable(paginatedRides);
          } else if (currentTab === 'completed') {
            populateCompletedTable(paginatedRides);
          } else if (currentTab === 'meetgreet') {
            populateMeetGreetTable(paginatedRides);
          }
        } catch (error) {
          console.error('Error in updateTableForCurrentTab:', error);
          const rides = currentRidesData[currentTab] || [];
          if (currentTab === 'unassigned') {
            populateUnassignedTable(rides);
          } else if (currentTab === 'assigned') {
            populateAssignedTable(rides);
          } else if (currentTab === 'ontrip') {
            populateOnTripTable(rides);
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(rides);
          } else if (currentTab === 'cancelled') {
            populateCancelledTable(rides);
          } else if (currentTab === 'completed') {
            populateCompletedTable(rides);
          } else if (currentTab === 'meetgreet') {
            populateMeetGreetTable(rides);
          }
        }
      }

      async function loadAssignedRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = result && result.data ? result.data : [];

          const assignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'assigned';
          });

          // Update cache
          currentRidesData.assigned = assignedRides;

          updateAssignedTabCount(assignedRides.length);
          if (currentTab === 'assigned') {
            updatePaginationInfo(assignedRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading assigned rides:', error);
          updateAssignedTabCount(0);
        }
      }

      function populateAssignedTable(rides) {
        const tbody = document.getElementById('assignedRidesBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-4 text-muted">No assigned rides to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = formatOrderTime(ride.created_at);
          const pickup =
            ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination =
            ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-end pe-4">
              <a href="orderassigned.php?id=${encodeURIComponent(ride.id)}&view=1" class="view-details-btn">
                <span>View Details</span>
                <i class="bi bi-chevron-right"></i>
              </a>
            </td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateAssignedTabCount(count) {
        const badge = document.getElementById('count-assigned');
        if (badge) badge.textContent = count;
      }

      const TAB_CONFIG = [
        { btnId: 'tab-unassigned', paneId: 'pane-unassigned', key: 'unassigned', action: true },
        { btnId: 'tab-assigned',   paneId: 'pane-assigned',   key: 'assigned',   action: true },
        { btnId: 'tab-on-trip',    paneId: 'pane-ontrip',     key: 'ontrip',     action: false },
        { btnId: 'tab-scheduled',  paneId: 'pane-scheduled',  key: 'scheduled',  action: false },
        { btnId: 'tab-cancelled',  paneId: 'pane-cancelled',  key: 'cancelled',  action: false },
        { btnId: 'tab-finished',   paneId: 'pane-completed',  key: 'completed',  action: false },
        { btnId: 'tab-meet-greet', paneId: 'pane-meetgreet',  key: 'meetgreet',  action: true },
      ];

      function setupTabSwitching() {
        function clearAllTabs() {
          TAB_CONFIG.forEach(({ btnId, paneId }) => {
            const btn = document.getElementById(btnId);
            const pane = document.getElementById(paneId);
            if (btn) {
              btn.classList.remove('active-tab', 'is-active');
              btn.setAttribute('aria-selected', 'false');
            }
            if (pane) pane.style.display = 'none';
          });
        }

        TAB_CONFIG.forEach(({ btnId, paneId, key, action }) => {
          const btn = document.getElementById(btnId);
          const pane = document.getElementById(paneId);
          if (!btn) return;
          btn.addEventListener('click', () => {
            currentTab = key;
            clearAllTabs();
            btn.classList.add('active-tab', 'is-active');
            btn.setAttribute('aria-selected', 'true');
            if (pane) pane.style.display = '';
            const rides = currentRidesData[key] || [];
            if (preorderPagination) {
              preorderPagination.update(rides.length, 1);
              updateTableForCurrentTab(1, ITEMS_PER_PAGE);
            }
          });
        });
      }

      async function loadScheduledRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = result && result.data ? result.data : [];

          const scheduledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'upcoming' || status === 'scheduled' || status === 'pending' || status === 'awaiting_assignment';
          });

          // Update cache
          currentRidesData.scheduled = scheduledRides;

          updateScheduledTabCount(scheduledRides.length);
          if (currentTab === 'scheduled') {
            updatePaginationInfo(scheduledRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading scheduled rides:', error);
          updateScheduledTabCount(0);
        }
      }

      function scheduledCategoryBadge(source) {
        const src = (source || '').toLowerCase();
        if (src.includes('meet_and_greet') || src.includes('meet and greet')) {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#EFF6FF; color:#2563EB; white-space:nowrap;">M&amp;G</span>';
        }
        if (src.startsWith('corporate') || src.includes('corporate')) {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F0FDF4; color:#16A34A; white-space:nowrap;">Corporate</span>';
        }
        if (src === 'dispatcher') {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F5F3FF; color:#7C3AED; white-space:nowrap;">Dispatcher</span>';
        }
        return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#FFF7ED; color:#EA580C; white-space:nowrap;">App</span>';
      }

      function populateScheduledTable(rides) {
        const tbody = document.getElementById('scheduledRidesBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="8" class="text-center py-4 text-muted">No pre-orders to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = formatOrderTime(ride.created_at);
          const pickup      = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr   || ride.actual_end_addr   || 'N/A';
          const status = ride.status || 'N/A';
          const fare   = formatFare(ride.fare_eur, ride.estimate_fare);
          const rideId = ride.id || '';
          const categoryBadge = scheduledCategoryBadge(ride.source);

          const hasDriver = !!(ride.driver_id);
          const actionCell = hasDriver
            ? `<a href="orderassigned.php?id=${encodeURIComponent(rideId)}&view=1" class="view-details-btn">
                 <span>View Details</span><i class="bi bi-chevron-right"></i>
               </a>`
            : `<a href="orderassigned.php?id=${encodeURIComponent(rideId)}" class="view-details-btn"
                 style="background:#f37a20 !important; color:#fff !important; border-color:#f37a20 !important;">
                 <span>Assign</span><i class="bi bi-chevron-right"></i>
               </a>`;

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${categoryBadge}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-end pe-4">${actionCell}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateScheduledTabCount(count) {
        const badge = document.getElementById('count-scheduled');
        if (badge) badge.textContent = count;
      }

      async function loadCancelledRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }
          const result = await response.json();
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          const rides = result && result.data ? result.data : [];
          const cancelledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'cancelled' || status === 'canceled';
          });
          currentRidesData.cancelled = cancelledRides;
          updateCancelledTabCount(cancelledRides.length);
          if (currentTab === 'cancelled') {
            updatePaginationInfo(cancelledRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading cancelled rides:', error);
          updateCancelledTabCount(0);
        }
      }

      function populateCancelledTable(rides) {
        const tbody = document.getElementById('cancelledRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="6" class="text-center py-4 text-muted">No cancelled rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = formatOrderTime(ride.created_at);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td class="text-end pe-4">${fare}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateCancelledTabCount(count) {
        const badge = document.getElementById('count-cancelled');
        if (badge) badge.textContent = count;
      }

      async function loadCompletedRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }
          const result = await response.json();
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          const rides = result && result.data ? result.data : [];
          const completedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'completed' || status === 'finished';
          });
          currentRidesData.completed = completedRides;
          updateCompletedTabCount(completedRides.length);
          if (currentTab === 'completed') {
            updatePaginationInfo(completedRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading completed rides:', error);
          updateCompletedTabCount(0);
        }
      }

      function populateCompletedTable(rides) {
        const tbody = document.getElementById('completedRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="6" class="text-center py-4 text-muted">No completed rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = formatOrderTime(ride.created_at);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td class="text-end pe-4">${fare}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function setupPreorderSearch() {
        const searchInput = document.getElementById('globalSearchInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
          applyPreorderSearchFilterForCurrentTab();
        });
      }

      function applyPreorderSearchFilterForCurrentTab() {
        const searchInput = document.getElementById('globalSearchInput');
        if (!searchInput) return;

        const term = searchInput.value.trim().toLowerCase();

        let tbodyId = null;
        if (currentTab === 'unassigned') {
          tbodyId = 'unassignedRidesBody';
        } else if (currentTab === 'assigned') {
          tbodyId = 'assignedRidesBody';
        } else if (currentTab === 'ontrip') {
          tbodyId = 'onTripRidesBody';
        } else if (currentTab === 'scheduled') {
          tbodyId = 'scheduledRidesBody';
        } else if (currentTab === 'cancelled') {
          tbodyId = 'cancelledRidesBody';
        } else if (currentTab === 'completed') {
          tbodyId = 'completedRidesBody';
        } else if (currentTab === 'meetgreet') {
          tbodyId = 'meetGreetRidesBody';
        }

        if (!tbodyId) return;

        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');

        if (!term) {
          rows.forEach(row => {
            row.style.display = '';
          });
          return;
        }

        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length < 5) {
            row.style.display = '';
            return;
          }

          const name = cells[0].textContent.toLowerCase();
          const orderTime = cells[1].textContent.toLowerCase();
          const pickup = cells[2].textContent.toLowerCase();
          const destination = cells[3].textContent.toLowerCase();
          const status = cells[4].textContent.toLowerCase();

          const matches =
            name.includes(term) ||
            orderTime.includes(term) ||
            pickup.includes(term) ||
            destination.includes(term) ||
            status.includes(term);

          row.style.display = matches ? '' : 'none';
        });
      }

      function updateCompletedTabCount(count) {
        const badge = document.getElementById('count-finished');
        if (badge) badge.textContent = count;
      }

      async function loadOnTripRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) throw new Error('Failed to fetch rides from server');
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Failed to fetch rides');
          const rides = result && result.data ? result.data : [];
          const onTripRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ['on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived','enroute','en_route','en-route'].includes(status);
          });
          currentRidesData.ontrip = onTripRides;
          updateOnTripTabCount(onTripRides.length);
          if (currentTab === 'ontrip') {
            updatePaginationInfo(onTripRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading on-trip rides:', error);
          updateOnTripTabCount(0);
        }
      }

      function populateOnTripTable(rides) {
        const tbody = document.getElementById('onTripRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No on-trip rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = formatOrderTime(ride.created_at);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const driverName = ride.driver_name || 'Unassigned';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const rideId = encodeURIComponent(ride.id || '');
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${driverName}</td>
            <td>${fare}</td>
            <td class="text-end pe-4">
              <a href="orderassigned.php?id=${rideId}&view=1" class="view-details-btn">
                <span>View Live</span>
                <i class="bi bi-geo-alt-fill"></i>
              </a>
            </td>
          `;
          tbody.appendChild(row);
        });
        applyPreorderSearchFilterForCurrentTab();
      }

      function updateOnTripTabCount(count) {
        const tab = document.getElementById('tab-on-trip');
        if (!tab) return;
        const badge = tab.querySelector('#count-on-trip');
        if (badge) badge.textContent = count;
      }

      // ──────────────── Meet & Greet ────────────────
      async function loadMeetGreetRides(showLoading = false) {
        const tbody = document.getElementById('meetGreetRidesBody');
        if (showLoading && tbody) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Loading rides...</td></tr>';
        }
        try {
          const response = await fetch('api/get_meet_and_greet_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) throw new Error('Failed to fetch meet & greet rides');
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Failed to fetch meet & greet rides');
          // Exclude cancelled rides — they belong only in the Cancelled tab
          const rides = (result && result.data ? result.data : []).filter(r => {
            const s = (r.status || '').toLowerCase();
            return s !== 'cancelled' && s !== 'canceled';
          });

          currentRidesData.meetgreet = rides;
          updateMeetGreetTabCount(rides.length);

          if (currentTab === 'meetgreet') {
            updatePaginationInfo(rides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading meet & greet rides:', error);
          updateMeetGreetTabCount(0);
          if (tbody && currentTab === 'meetgreet') {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading rides. Please refresh.</td></tr>';
          }
        }
      }

      function populateMeetGreetTable(rides) {
        const tbody = document.getElementById('meetGreetRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No meet &amp; greet rides to show</td></tr>';
          return;
        }

        const esc = (v) => String(v ?? '')
          .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

        rides.forEach((ride) => {
          const company     = esc(ride.company || '—');
          const employee    = esc(ride.employee || ride.passenger_name || '—');
          const orderTime   = formatOrderTime(ride.created_at);
          const pickup      = esc(ride.pickup_addr || '—');
          const destination = esc(ride.dest_addr || '—');
          const payment     = esc(ride.payment_method || '—');
          const fare        = formatFare(ride.fare_eur, ride.estimate_fare);
          const status      = ride.status || 'N/A';
          const rideId      = encodeURIComponent(ride.id || '');
          const statusKey   = String(status).trim().toLowerCase().replace(/\s+/g, '_');

          let actionCell = '';
          if (statusKey === 'pending' || statusKey === 'searching') {
            actionCell = `<a href="orderassigned.php?corp_id=${rideId}" class="view-details-btn">
                <span>Assign</span><i class="bi bi-chevron-right"></i></a>`;
          } else if (['assigned','on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived','enroute','en_route','en-route','completed','cancelled'].includes(statusKey)) {
            actionCell = `<a href="orderassigned.php?corp_id=${rideId}&view=1" class="view-details-btn">
                <span>View Details</span><i class="bi bi-chevron-right"></i></a>`;
          }

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${company}</td>
            <td>${employee}</td>
            <td>${orderTime}</td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${pickup}">${pickup}</td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${destination}">${destination}</td>
            <td>${payment}</td>
            <td class="text-end pe-2">${fare}</td>
            <td>${renderStatusBadge(status)}</td>
            <td class="text-end pe-4">${actionCell}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateMeetGreetTabCount(count) {
        const badge = document.getElementById('count-meet-greet');
        if (badge) badge.textContent = count;
      }

    </script>
  </body>
</html>
