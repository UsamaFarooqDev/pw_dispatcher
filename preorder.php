<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-center my-2 gap-3">
  <div class="d-flex gap-3 w-100 w-md-auto justify-content-center justify-content-md-start">
    <a
      href="order.php"
      class="btn glowing-btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
      style="background: #f37a20; color: #fff; border-radius: 4px; box-shadow: 0 0 15px rgba(243, 122, 32, 0.5); min-width: 120px;"
    >
      New Order
    </a>
    <a
      href="map.php"
      class="btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
      style="background: #fff; color: #000; border: 1px solid #000; border-radius: 4px; min-width: 120px;"
    >
      Open Map
    </a>
  </div>
  
  <div class="w-100 w-md-auto" style="max-width: 300px;">
    <input
      type="text"
      placeholder="Search"
      class="form-control w-100 border border-secondary px-3 mx-auto mx-md-0"
      style="border-radius: 30px; color: #3b3b3b; min-height: 36px;"
    />
  </div>
</div>

      <div class="card shadow border-0 mt-4" style='border-radius: 25px;'>
        <div class="card-body">
          <div class="container-fluid p-3">
  <!-- Tab Navigation -->
  <div class="d-flex flex-wrap align-items-center gap-2 mb-3 border-bottom pb-2">
    <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-on-trip"
      disabled
    >
      On Trip (0)
    </button>
    <span class="text-muted">›</span>
      <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #f37a20; border-bottom: 3px solid #f37a20;"
      id="tab-unassigned"
    >
      Unassigned (0)
    </button>
    <span class="text-muted">›</span>
     <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-assigned"
    >
      Assigned (0)
    </button>
    <span class="text-muted">›</span>
     <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-scheduled"
    >
    Scheduled (0)
    </button>
    <span class="text-muted">›</span>
     <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-cancelled"
      disabled
    >
      Cancelled (0)
    </button>
    <span class="text-muted">›</span>
      <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-finished"
      disabled
    >
      Finished (0)
    </button>
    <span class="text-muted">›</span>
    <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-unpaid"
      disabled
    >
      Unpaid (0)
    </button>
    <span class="text-muted">›</span>
      <button
      class="btn btn-link text-decoration-none px-2 py-1 fs-6 fw-medium"
      style="color: #3b3b3b;"
      id="tab-all-mine"
      disabled
    >
      All Mine (0)
    </button>
    <span class="text-muted">›</span>
  </div>

  <div class="table-responsive bg-white rounded shadow-sm mb-3">
    <table class="table table-borderless mb-0">
      <thead>
        <tr class="bg-light">
          <th scope="col" class="fw-semibold ps-3">Name</th>
          <th scope="col" class="fw-semibold">Order Time</th>
          <th scope="col" class="fw-semibold">Pickup</th>
          <th scope="col" class="fw-semibold">Destination</th>
          <th scope="col" class="fw-semibold text-end">Status</th>
          <th scope="col" class="fw-semibold text-end pe-4">Fare</th>
          <th scope="col" class="fw-semibold text-end pe-4" id="actionHeader">Action</th>
        </tr>
      </thead>
    </table>
  </div>

  <div class="table-responsive bg-white rounded" style="min-height: 310px">
    <table class="table mb-0">
      <tbody id="unassignedRidesBody">
        <!-- Dynamic unassigned rides -->
      </tbody>
      <tbody id="assignedRidesBody" style="display: none;">
        <!-- Dynamic assigned rides -->
      </tbody>
      <tbody id="scheduledRidesBody" style="display: none;">
        <!-- Dynamic scheduled rides -->
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div id="preorderPaginationContainer" class="mt-3"></div>
  </div>
</div>
</div>
</div>
</div>
    </main>
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
        scheduled: []
      }; // Cache current rides data to detect changes
      
      // Pagination
      let preorderPagination = null;
      const ITEMS_PER_PAGE = 10;
      let currentTab = 'unassigned'; // Track current active tab

      document.addEventListener('DOMContentLoaded', function () {
        try {
          // Initialize pagination
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
          } else if (!paginationContainer) {
            console.warn('Pagination container not found');
          } else if (typeof PaginationManager === 'undefined') {
            console.warn('PaginationManager not loaded. Make sure pagination.js is loaded before this script.');
          }
          
          // Load beeping state from global monitor
          if (window.BeepMonitor) {
            window.BeepMonitor.loadBeepingState();
            beepingRideIds = new Set(window.BeepMonitor.getBeepingRideIds());
          }
          // Initial load - show loading state
          loadUnassignedRides(true);
          loadAssignedRides(true);
          loadScheduledRides(true);
          // Start polling for ride status updates (silent - no UI reload)
          startPolling();

          toggleActionColumn(true);
          
          // Setup tab switching
          setupTabSwitching();
        } catch (error) {
          console.error('Error initializing page:', error);
          // Still try to load data even if pagination fails
          loadUnassignedRides(true);
          loadAssignedRides(true);
          loadScheduledRides(true);
        }
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
            return status === 'upcoming' || status === 'scheduled';
          });

             // Initialize previous statuses on first check if empty
          if (previousRideStatuses.size === 0) {
            rides.forEach((ride) => {
              if (ride.id) {
                previousRideStatuses.set(ride.id, (ride.status || '').toLowerCase());
              }
            });
          }

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
        } catch (error) {
          console.error('Error checking ride status:', error);
          // Don't show error in UI during silent polling - only log it
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
                <td>${status}</td>
                <td class="text-end pe-4">${fare}</td>
                <td class="text-end pe-4">
                  <a href="orderassigned.php?id=${rideId}" class="text-decoration-none" style="color: #f37a20;">View Details</a>
                </td>
              `;
              tbody.appendChild(row);
            } catch (rowError) {
              console.error('Error creating table row:', rowError, ride);
            }
          });
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
        const unassignedTab = document.getElementById('tab-unassigned');
        if (!unassignedTab) return;
        unassignedTab.textContent = `Unassigned (${count})`;
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
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(paginatedRides);
          }
        } catch (error) {
          console.error('Error in updateTableForCurrentTab:', error);
          // Fallback: populate all rides for current tab
          const rides = currentRidesData[currentTab] || [];
          if (currentTab === 'unassigned') {
            populateUnassignedTable(rides);
          } else if (currentTab === 'assigned') {
            populateAssignedTable(rides);
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(rides);
          }
        }
      }

      async function loadAssignedRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
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
            <td>${status}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-end pe-4">
              <a href="preorder/${ride.id}" class="text-decoration-none" style="color: #f37a20;">View Details</a>
            </td>
          `;
          tbody.appendChild(row);
        });
      }

      function updateAssignedTabCount(count) {
        const assignedTab = document.getElementById('tab-assigned');
        if (!assignedTab) return;
        assignedTab.textContent = `Assigned (${count})`;
      }

      function setupTabSwitching() {
        const unassignedTab = document.getElementById('tab-unassigned');
        const assignedTab = document.getElementById('tab-assigned');
        const scheduledTab = document.getElementById('tab-scheduled');
        const unassignedBody = document.getElementById('unassignedRidesBody');
        const assignedBody = document.getElementById('assignedRidesBody');
        const scheduledBody = document.getElementById('scheduledRidesBody');

        if (unassignedTab) {
          unassignedTab.addEventListener('click', () => {
            currentTab = 'unassigned';
            // Update tab styles
            toggleActionColumn(true);
            unassignedTab.style.color = '#f37a20';
            unassignedTab.style.borderBottom = '3px solid #f37a20';
            assignedTab.style.color = '#3b3b3b';
            assignedTab.style.borderBottom = 'none';
            if (scheduledTab) {
              scheduledTab.style.color = '#3b3b3b';
              scheduledTab.style.borderBottom = 'none';
            }
            
            // Show/hide table bodies
            if (unassignedBody) unassignedBody.style.display = '';
            if (assignedBody) assignedBody.style.display = 'none';
            if (scheduledBody) scheduledBody.style.display = 'none';
            
            // Update pagination and table
            const rides = currentRidesData.unassigned || [];
            if (preorderPagination) {
              preorderPagination.update(rides.length, 1);
              updateTableForCurrentTab(1, ITEMS_PER_PAGE);
            }
          });
        }

        if (assignedTab) {
          assignedTab.addEventListener('click', () => {
            currentTab = 'assigned';
            // Update tab styles
            toggleActionColumn(true);
            assignedTab.style.color = '#f37a20';
            assignedTab.style.borderBottom = '3px solid #f37a20';
            unassignedTab.style.color = '#3b3b3b';
            unassignedTab.style.borderBottom = 'none';
            if (scheduledTab) {
              scheduledTab.style.color = '#3b3b3b';
              scheduledTab.style.borderBottom = 'none';
            }
            
            // Show/hide table bodies
            if (assignedBody) assignedBody.style.display = '';
            if (unassignedBody) unassignedBody.style.display = 'none';
            if (scheduledBody) scheduledBody.style.display = 'none';
            
            // Update pagination and table
            const rides = currentRidesData.assigned || [];
            if (preorderPagination) {
              preorderPagination.update(rides.length, 1);
              updateTableForCurrentTab(1, ITEMS_PER_PAGE);
            }
          });
        }

        if (scheduledTab) {
          scheduledTab.addEventListener('click', () => {
            currentTab = 'scheduled';
            toggleActionColumn(false);
            scheduledTab.style.color = '#f37a20';
            scheduledTab.style.borderBottom = '3px solid #f37a20';
            assignedTab.style.color = '#3b3b3b';
            assignedTab.style.borderBottom = 'none';
            unassignedTab.style.color = '#3b3b3b';
            unassignedTab.style.borderBottom = 'none';

            if (scheduledBody) scheduledBody.style.display = '';
            if (assignedBody) assignedBody.style.display = 'none';
            if (unassignedBody) unassignedBody.style.display = 'none';
            
            // Update pagination and table
            const rides = currentRidesData.scheduled || [];
            if (preorderPagination) {
              preorderPagination.update(rides.length, 1);
              updateTableForCurrentTab(1, ITEMS_PER_PAGE);
            }
          });
        }
      }

      async function loadScheduledRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
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
            return status === 'upcoming' || status === 'scheduled';
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

      function populateScheduledTable(rides) {
        const tbody = document.getElementById('scheduledRidesBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-4 text-muted">No scheduled rides to show</td></tr>';
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
            <td>${status}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-end pe-4">
              <a href="orderassigned.php?id=${ride.id}" class="text-decoration-none" style="color: #f37a20;">View Details</a>
            </td>
          `;
          tbody.appendChild(row);
        });
      }

      function updateScheduledTabCount(count) {
        const scheduledTab = document.getElementById('tab-scheduled');
        if (!scheduledTab) return;
        scheduledTab.textContent = `Scheduled (${count})`;
      }

    </script>
  </body>
</html>
