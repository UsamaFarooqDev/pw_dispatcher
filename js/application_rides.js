      let ridesPagination = null;
      const APP_RIDES_ITEMS_PER_PAGE = 10;

      function initApplicationRidesCore() {
        // Initialize pagination
        const paginationContainer = document.getElementById('corporatePaginationContainer');
        if (paginationContainer) {
          ridesPagination = new PaginationManager({
            containerId: 'corporatePaginationContainer',
            page: 1,
            limit: APP_RIDES_ITEMS_PER_PAGE,
            total: 0,
            onPageChange: (page, limit) => {
              loadRidesData(page, limit);
            }
          });
        }

        loadRidesData(1, APP_RIDES_ITEMS_PER_PAGE);
        setupCorporateSearch();
      }

      async function loadRidesData(page = 1, limit = APP_RIDES_ITEMS_PER_PAGE) {
        try {
          const tbody = document.getElementById('ridesTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="pw-loading-cell"><div class="pw-loading-inner"><span class="pw-spinner-sm"></span><span class="pw-loading-text">Loading rides…</span></div></td></tr>';
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
              tbody.innerHTML = '<tr><td colspan="8" class="pw-loading-cell"><div class="pw-loading-inner"><i class="bi bi-inbox" style="font-size:1.7rem; color:#E4E4E7;"></i><span class="pw-loading-text">No rides to show</span></div></td></tr>';
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
        } finally {
          if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
        }
      }


      function populateRidesTable(rides) {
        const tbody = document.getElementById('ridesTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          const colCount = IS_DISPATCHER_ROLE ? 7 : 8;
          tbody.innerHTML = `<tr><td colspan="${colCount}" class="pw-loading-cell"><div class="pw-loading-inner"><i class="bi bi-inbox" style="font-size:1.7rem; color:#E4E4E7;"></i><span class="pw-loading-text">No rides to show</span></div></td></tr>`;
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
            ${IS_DISPATCHER_ROLE ? '' : `
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
            `}
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

      function pickStatus(btn) {
        document.querySelectorAll('.status-option').forEach(el => el.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        document.getElementById('rideStatusSelect').value = btn.dataset.value;
      }

      // Wires the edit/delete modals and their action buttons. Guarded with
      // `if (el)` (unlike the original code) since this now runs again on
      // every SPA revisit — cheap insurance if it's ever called before this
      // page's markup exists.
      function initApplicationRidesModals() {
        // Edit modal - when opened, set the current ride ID and status
        const editModal = document.getElementById('editModal');
        if (editModal) {
          editModal.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            selectedRideId = btn.getAttribute('data-ride-id');
            const currentStatus = (btn.getAttribute('data-ride-status') || '').toLowerCase();

            const allowedStatuses = ['searching', 'assigned', 'enroute', 'scheduled', 'completed', 'cancelled'];
            const statusToSelect = allowedStatuses.includes(currentStatus) ? currentStatus : 'searching';
            document.getElementById('rideStatusSelect').value = statusToSelect;
            document.querySelectorAll('.status-option').forEach(el => {
              el.classList.toggle('is-selected', el.dataset.value === statusToSelect);
            });
          });
        }

        // Save ride status button
        const saveRideStatusBtn = document.getElementById('saveRideStatusBtn');
        if (saveRideStatusBtn) {
          saveRideStatusBtn.addEventListener('click', async function() {
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
                loadRidesData(currentPage, APP_RIDES_ITEMS_PER_PAGE);
              } else {
                alert('Error updating ride status: ' + (data.error || 'Unknown error'));
              }
            } catch (error) {
              console.error('Error updating ride status:', error);
              alert('Error updating ride status. Please try again.');
            }
          });
        }

        // Delete modal - when opened, set the current ride ID
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
          deleteModal.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            selectedRideId = btn.getAttribute('data-ride-id');
          });
        }

        // Confirm delete button
        const confirmDeleteRideBtn = document.getElementById('confirmDeleteRideBtn');
        if (confirmDeleteRideBtn) {
          confirmDeleteRideBtn.addEventListener('click', async function() {
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
                loadRidesData(currentPage, APP_RIDES_ITEMS_PER_PAGE);
              } else {
                alert('Error deleting ride: ' + (data.error || 'Unknown error'));
              }
            } catch (error) {
              console.error('Error deleting ride:', error);
              alert('Error deleting ride. Please try again.');
            }
          });
        }
      }

      function initApplicationRidesPage() {
        initApplicationRidesCore();
        initApplicationRidesModals();
      }
      document.addEventListener('DOMContentLoaded', initApplicationRidesPage);

      window.SPA_PAGES = window.SPA_PAGES || {};
      window.SPA_PAGES['application_rides.php'] = { init: initApplicationRidesPage, cleanup: null };
