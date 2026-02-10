<?php
session_start();

require('modules/head.php');

?>
<!DOCTYPE html>
<html lang="en">
  <body>
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>
    <main
      class="main-content p-4"
      style="background: #f5f7fa; min-height: 92vh"
    >
      <div
        class="card shadow border-0 mt-1 h-100"
        style="border-radius: 25px; overflow: hidden"
      >
        <div class="card-body p-3">
          <div class="table-responsive bg-white" style="min-height: 400px">
            <table class="table mb-0" style="vertical-align: middle">
              <thead class="bg-light">
                <tr>
                  <th scope="col">Ride ID</th>
                  <th scope="col">Company</th>
                  <th scope="col">Employee</th>
                  <th scope="col">Email</th>
                  <th scope="col">Pickup</th>
                  <th scope="col">Destination</th>
                  <th scope="col">Payment</th>
                  <th scope="col">Fare</th>
                  <th scope="col">Status</th>
                  <th scope="col" class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody id="ridesTableBody">
                <!-- Data will be injected here by JS -->
              </tbody>
            </table>
          </div>

          <div class="p-3 border-top">
            <div id="corporatePaginationContainer"></div>
          </div>
        </div>
      </div>
    </main>

    <div
      class="modal fade"
      id="editModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">Update Ride Status</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body pt-2">
            <div class="mb-3">
              <label class="form-label fw-medium small text-muted"
                >Status</label
              >
              <select
                id="rideStatusSelect"
                class="form-select py-1.5"
                style="border-radius: 5px; border: 1px solid #ddd"
              >
                <option value="searching">Searching</option>
                <option value="assigned">Assigned</option>
                <option value="upcoming">Upcoming</option>
              </select>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0 pb-3">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-2 px-4"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
          <button
  type="button"
  class="btn fw-semibold rounded-2 px-4"
  id="saveRideStatusBtn"
  style="background: #f37a20; color: white"
>
              Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="deleteModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 p-4 border-0 shadow">
          <div class="modal-body text-center py-4">
            <div class="mb-3">
              <i
                class="bi bi-exclamation-circle text-warning"
                style="font-size: 2.8rem"
              ></i>
            </div>
            <h3 class="fw-bold mb-2">Delete Corporate Ride?</h3>
            <p class="text-muted medium mb-0">
              This ride will be permanently removed.<br />Are you sure?
            </p>
          </div>
          <div class="modal-footer justify-content-center border-0 pb-3">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-2 px-4"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button
  type="button"
  class="btn fw-semibold rounded-2 px-4"
  id="confirmDeleteRideBtn"
  style="background: #f37a20; color: white"
>
              Delete
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
      });

      async function loadRidesData(page = 1, limit = ITEMS_PER_PAGE) {
        try {
          const tbody = document.getElementById('ridesTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Loading rides...</td></tr>';
          }

          const response = await fetch(`api/get_rides.php?page=${page}&limit=${limit}`);
          
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
              tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No rides to show</td></tr>';
            }
            if (ridesPagination) {
              ridesPagination.update(0, page);
            }
          }
        } catch (error) {
          console.error('Error loading rides:', error);
          const tbody = document.getElementById('ridesTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error loading rides. Please refresh the page.</td></tr>';
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
          tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No rides to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const rideId = ride.id ? ride.id.substring(0, 8).toUpperCase() : 'N/A';

          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';

          const payment = ride.payment_method || 'N/A';
          
          const fare = ride.fare_eur ? `€${parseFloat(ride.fare_eur).toFixed(2)}` : 'N/A';
          
          const status = ride.status || 'N/A';
          const statusClass = getStatusClass(status);
          const statusText = formatStatus(status);
          
          const company = ride.company || 'N/A';
          const employee = ride.passenger_name || 'N/A';
          const email = ride.passenger_email || 'N/A';

          const row = document.createElement('tr');
          row.style.transition = 'none';
          row.innerHTML = `
            <td>${rideId}</td>
            <td>${company}</td>
            <td>${employee}</td>
            <td>${email}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${payment}</td>
            <td class="text-success">${fare}</td>
            <td>
              <span class="${statusClass} px-2 py-1">${statusText}</span>
            </td>
            <td class="text-end pe-4">
              <button
                class="btn btn-sm p-0 me-2 text-muted"
                data-bs-toggle="modal"
                data-bs-target="#editModal"
                data-ride-id="${ride.id}"
                data-ride-status="${ride.status}"
              >
                <i class="bi bi-pencil-square fs-6"></i>
              </button>
              <button
                class="btn btn-sm p-0 text-muted"
                data-bs-toggle="modal"
                data-bs-target="#deleteModal"
                data-ride-id="${ride.id}"
                aria-label="Delete"
              >
                <i class="bi bi-trash fs-6"></i>
              </button>
            </td>
          `;
          tbody.appendChild(row);
        });
      }

      function getStatusClass(status) {
        const statusLower = (status || '').toLowerCase();
        if (statusLower === 'completed' || statusLower === 'finished') {
          return 'text-success';
        } else if (statusLower === 'pending' || statusLower === 'waiting') {
          return 'text-warning';
        } else if (statusLower === 'on progress' || statusLower === 'in_progress' || statusLower === 'active') {
          return 'text-info';
        } else if (statusLower === 'cancelled' || statusLower === 'canceled') {
          return 'text-danger';
        } else {
          return 'text-muted';
        }
      }

      function formatStatus(status) {
        if (!status) return 'N/A';
        return status.split('_').map(word => 
          word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        ).join(' ');
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
