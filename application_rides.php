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
            <th class="fw-semibold text-nowrap px-4 py-2 text-end" style="font-size:0.75rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Action</th>
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

<style>
  #ridesTableBody tr {
    border-bottom: 1px solid #F4F4F5;
    transition: background 0.12s;
  }
  #ridesTableBody tr:hover { background: #FAFAFA; }
  #ridesTableBody td {
    padding: 14px 24px;
    font-size: 0.8125rem;
    color: #18181B;
    vertical-align: middle;
    border: none;
  }
  #ridesTableBody td.ride-addr-cell {
    min-width: 260px;
    max-width: 340px;
    white-space: normal;
    line-height: 1.35;
    color: #27272A;
  }
  #ridesTableBody td.ride-action-cell {
    white-space: nowrap;
    min-width: 110px;
  }
  .ride-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #E4E4E7;
    background: #fff;
    color: #52525B;
    font-size: 14px;
    transition: all 0.15s;
  }
  .ride-action-btn:hover {
    border-color: #f37a20;
    color: #f37a20;
    background: #FFF7ED;
  }
  .ride-action-btn--danger:hover {
    border-color: #E11D48;
    color: #E11D48;
    background: #FFF1F2;
  }
</style>

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

      <div class="mb-4">
        <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Status</label>
        <select id="rideStatusSelect" class="form-select"
          style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
          onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
          onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
          <option value="searching">Searching</option>
          <option value="assigned">Assigned</option>
          <option value="upcoming">Upcoming</option>
        </select>
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
      let ridesPagination = null;
      const ITEMS_PER_PAGE = 10;

      document.addEventListener('DOMContentLoaded', function() {
        // Initialize pagination
        const paginationContainer = document.getElementById('corporatePaginationContainer');
        if (paginationContainer) {
          ridesPagination = new PaginationManager({
            containerId: 'corporatePaginationContainer',
            page: 1,
            limit: ITEMS_PER_PAGE,
            total: 0,
            onPageChange: (page, limit) => {
              loadRidesData(page, limit);
            }
          });
        }
        
        loadRidesData(1, ITEMS_PER_PAGE);
        setupCorporateSearch();
      });

      async function loadRidesData(page = 1, limit = ITEMS_PER_PAGE) {
        try {
          const tbody = document.getElementById('ridesTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading rides...</td></tr>';
          }

          const response = await fetch(`api/get_rides.php?page=${page}&limit=${limit}`);

          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const data = await response.json();

          if (data.success && data.data) {
            populateRidesTable(data.data);
            
            // Update pagination
            if (ridesPagination && data.pagination) {
              ridesPagination.update(data.pagination.total, page);
            }
          } else {
            console.error('Error loading rides:', data.error || 'Unknown error');
            if (tbody) {
              tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No rides to show</td></tr>';
            }
            if (ridesPagination) {
              ridesPagination.update(0, page);
            }
          }
        } catch (error) {
          console.error('Error loading rides:', error);
          const tbody = document.getElementById('ridesTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Error loading rides. Please refresh the page.</td></tr>';
          }
          if (ridesPagination) {
            ridesPagination.update(0, 1);
          }
        }
      }


      function populateRidesTable(rides) {
        const tbody = document.getElementById('ridesTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No rides to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';

          const payment = ride.payment_method || 'N/A';

          const fare = ride.fare_eur ? `€${parseFloat(ride.fare_eur).toFixed(2)}` : 'N/A';

          const status = ride.status || 'N/A';

          const employee = ride.passenger_name || 'N/A';

          const rideTimeSource = ride.pickup_time || ride.scheduled_at || ride.actual_start_at || ride.created_at || null;
          let dateTimeHtml = '<span style="color:#A1A1AA;">N/A</span>';
          if (rideTimeSource) {
            const d = new Date(rideTimeSource);
            if (!isNaN(d.getTime())) {
              const datePart = d.toLocaleDateString('en-IE', { day: '2-digit', month: 'short', year: 'numeric' });
              const timePart = d.toLocaleTimeString('en-IE', { hour: '2-digit', minute: '2-digit', hour12: false });
              dateTimeHtml = `<div style="white-space:nowrap; line-height:1.25;">
                <div style="color:#18181B;">${datePart}</div>
                <div style="color:#71717A; font-size:0.75rem;">${timePart}</div>
              </div>`;
            }
          }

          const row = document.createElement('tr');
          row.style.transition = 'none';
          row.innerHTML = `
            <td>${employee}</td>
            <td class="ride-addr-cell">${pickup}</td>
            <td class="ride-addr-cell">${destination}</td>
            <td>${dateTimeHtml}</td>
            <td>${payment}</td>
            <td class="text-success fw-semibold">${fare}</td>
            <td>
              ${renderStatusBadge(status)}
            </td>
            <td class="ride-action-cell text-end pe-4">
              <div class="d-inline-flex align-items-center gap-2 justify-content-end">
                <button
                  class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center ride-action-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#editModal"
                  data-ride-id="${ride.id}"
                  data-ride-status="${ride.status}"
                  aria-label="Edit"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button
                  class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center ride-action-btn ride-action-btn--danger"
                  data-bs-toggle="modal"
                  data-bs-target="#deleteModal"
                  data-ride-id="${ride.id}"
                  aria-label="Delete"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          `;
          tbody.appendChild(row);
        });

        // Re-apply any active search filter after repopulating rows
        applyCorporateSearchFilter();
      }

      // Status badge rendering is handled by the shared renderStatusBadge() from js/status-badge.js

      function setupCorporateSearch() {
        const searchInput = document.getElementById('globalSearchInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
          applyCorporateSearchFilter();
        });
      }

      function applyCorporateSearchFilter() {
        const tbody = document.getElementById('ridesTableBody');
        const searchInput = document.getElementById('globalSearchInput');
        if (!tbody || !searchInput) return;

        const term = searchInput.value.trim().toLowerCase();
        const rows = tbody.querySelectorAll('tr');

        if (!term) {
          rows.forEach(row => {
            row.style.display = '';
          });
          return;
        }

        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length < 7) {
            row.style.display = '';
            return;
          }

          const employee = cells[0].textContent.toLowerCase();
          const pickup = cells[1].textContent.toLowerCase();
          const destination = cells[2].textContent.toLowerCase();
          const dateTimeText = cells[3].textContent.toLowerCase();
          const statusText = cells[6].textContent.toLowerCase();

          const matches =
            employee.includes(term) ||
            pickup.includes(term) ||
            destination.includes(term) ||
            dateTimeText.includes(term) ||
            statusText.includes(term);

          row.style.display = matches ? '' : 'none';
        });
      }


      let selectedRideId = null;

      // Edit modal - when opened, set the current ride ID and status
      document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        selectedRideId = btn.getAttribute('data-ride-id');
        const currentStatus = (btn.getAttribute('data-ride-status') || '').toLowerCase();
        
        const statusSelect = document.getElementById('rideStatusSelect');
        if (statusSelect) {
          // Set the current status, or default to 'searching' if not in allowed list
          const allowedStatuses = ['searching', 'assigned', 'upcoming'];
          if (allowedStatuses.includes(currentStatus)) {
            statusSelect.value = currentStatus;
          } else {
            statusSelect.value = 'searching';
          }
        }
      });

      // Save ride status button
      document.getElementById('saveRideStatusBtn').addEventListener('click', async function() {
        if (!selectedRideId) {
          alert('Error: No ride selected');
          return;
        }

        const statusSelect = document.getElementById('rideStatusSelect');
        const status = statusSelect ? statusSelect.value : null;

        if (!status) {
          alert('Error: Please select a status');
          return;
        }

        try {
          const res = await fetch('api/update_ride_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
              ride_id: selectedRideId,
              status: status
            })
          });

          if (res.status === 401) { window.location.href = '/'; return; }
          const data = await res.json();
          
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            const currentPage = ridesPagination ? ridesPagination.getCurrentPage() : 1;
            loadRidesData(currentPage, ITEMS_PER_PAGE);
          } else {
            alert('Error updating ride status: ' + (data.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error updating ride status:', error);
          alert('Error updating ride status. Please try again.');
        }
      });

      // Delete modal - when opened, set the current ride ID
      document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        selectedRideId = btn.getAttribute('data-ride-id');
      });

      // Confirm delete button
      document.getElementById('confirmDeleteRideBtn').addEventListener('click', async function() {
        if (!selectedRideId) {
          alert('Error: No ride selected');
          return;
        }

        try {
          const res = await fetch('api/delete_ride.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ride_id: selectedRideId })
          });

          if (res.status === 401) { window.location.href = '/'; return; }
          const data = await res.json();
          
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            const currentPage = ridesPagination ? ridesPagination.getCurrentPage() : 1;
            loadRidesData(currentPage, ITEMS_PER_PAGE);
          } else {
            alert('Error deleting ride: ' + (data.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error deleting ride:', error);
          alert('Error deleting ride. Please try again.');
        }
      });
    </script>
  </body>
</html>
