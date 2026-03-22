<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    header('Location: /');
    exit;
}

$user = $_SESSION['user'];
require('modules/head.php');
?>

<!DOCTYPE html>
<html lang="en">
  <body>
   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f4f4f5; min-height: 100vh;">

    <?php @require('modules/bodyHeader.php'); ?>    

    <div class="rounded-3 border mt-4 overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);">
      <div class="p-4">
        <div class="d-flex align-items-center gap-2 mb-4 p-1 rounded-2 d-inline-flex" style="background:#F4F4F5;">
          <button
            id="driverViewBtn"
            class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3"
            style="border-radius:6px; height:32px; font-size:0.9125rem; background:#fff; color:#18181B; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.08);"
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
          <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB;">
            <table class="table mb-0" style="border-collapse:collapse;">
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

        <div id="paginationContainer" class="mt-3"></div>

      </div>
    </div>

  </main>

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

  <style>
    @keyframes modalFadeIn {
      from { opacity:0; transform:scale(0.96); }
      to   { opacity:1; transform:scale(1); }
    }
    #driverTable tbody tr, #customerTable tbody tr {
      border-bottom: 1px solid #F4F4F5;
      transition: background 0.12s;
    }
    #driverTable tbody tr:hover, #customerTable tbody tr:hover {
      background: #FAFAFA;
    }
    #driverTable tbody td, #customerTable tbody td {
      padding: 14px 24px;
      font-size: 0.845rem;
      color: #18181B;
      vertical-align: middle;
      border: none;
    }
    .document-preview {
      transition: transform 0.15s, box-shadow 0.15s;
      cursor: pointer;
    }
    .document-preview:hover {
      transform: scale(1.08);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      position: relative;
      z-index: 10;
    }
  </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pagination.js"></script>
    <script src="js/app.js"></script>
    <script>
            document
        .getElementById('sidebarToggle')
        .addEventListener('click', function () {
          document.querySelector('.sidebar').classList.toggle('active');
        });

      document.addEventListener('click', function (event) {
        const sidebar = document.querySelector('.sidebar');
        if (
          window.innerWidth < 768 &&
          !event.target.closest('.sidebar') &&
          !event.target.closest('#sidebarToggle')
        ) {
          sidebar.classList.remove('active');
        }
      });

      function toggleSidebar() {
        console.log('Sidebar toggle functionality would go here');
      }
        // Toggle between Driver and Customer table views
function switchTableView(view) {
  const driverTable = document.getElementById('driverTable');
  const customerTable = document.getElementById('customerTable');
  const driverBtn = document.getElementById('driverViewBtn');
  const customerBtn = document.getElementById('customerViewBtn');

  if (view === 'driver') {
    // Show Driver, Hide Customer
    driverTable.classList.remove('d-none');
    driverTable.classList.add('d-block');
    customerTable.classList.remove('d-block');
    customerTable.classList.add('d-none');

    // Update button styles
    driverBtn.style.backgroundColor = '#f37a20';
    driverBtn.style.color = 'white';
    driverBtn.style.borderColor = '#f37a20';

    customerBtn.style.backgroundColor = '#fff';
    customerBtn.style.color = '#3b3b3b';
    customerBtn.style.borderColor = '#3b3b3b';
  } else {
    // Show Customer, Hide Driver
    customerTable.classList.remove('d-none');
    customerTable.classList.add('d-block');
    driverTable.classList.remove('d-block');
    driverTable.classList.add('d-none');

    // Update button styles
    customerBtn.style.backgroundColor = '#f37a20';
    customerBtn.style.color = 'white';
    customerBtn.style.borderColor = '#f37a20';

    driverBtn.style.backgroundColor = '#fff';
    driverBtn.style.color = '#3b3b3b';
    driverBtn.style.borderColor = '#3b3b3b';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Default to Driver view
  switchTableView('driver');
});

  // Switch tab function
  function switchTab(tabName) {
    // Reset all tabs
    document.querySelectorAll('.btn.btn-link').forEach(btn => {
      btn.style.color = '#3b3b3b';
      btn.style.borderBottom = 'none';
    });
    document.getElementById(tabName + 'Tab').style.color = '#f37a20';
    document.getElementById(tabName + 'Tab').style.borderBottom = '3px solid #f37a20';

    // Show correct data
    const data = tabData[tabName];

    // Render driver table
    renderTable('driverTableBody', data.driver, 'driver');
    // Render customer table
    renderTable('customerTableBody', data.customer, 'customer');

    // Toggle visibility of tables
    document.getElementById('driverTable').style.display = 'block';
    document.getElementById('customerTable').style.display = 'block'; // or 'none' if you want only one visible

    // Optional: Update pagination count
    updatePagination(data.driver.length);
  }

  // Render table rows
  function renderTable(containerId, data, type) {
    const tbody = document.getElementById(containerId);
    tbody.innerHTML = '';

    if (data.length === 0) {
      if (type === 'passenger' || type === 'customer') {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">No passengers to show</td></tr>`;
      } else {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">No drivers to show</td></tr>`;
      }
      return;
    }

    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td class="ps-3">
          <input type="checkbox" class="form-check-output" />
        </td>
        <td>
          <div class="d-flex align-items-center">
              <div class="fw-medium small">${item.name}</div>
          </div>
        </td>
        <td>${type === 'driver' ? item.car : item.service}</td>
        <td>${item.time}</td>
        <td>${type === 'driver' ? item.start : item.pickup}</td>
        <td>${type === 'driver' ? item.end : item.dropoff}</td>
        <td class="text-end pe-4">
          <span class="text-success">${item.fare}</span>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
      // Lightweight initializer that reuses functions from js/app.js
      document.addEventListener('DOMContentLoaded', () => {
        try {
          if (typeof switchTableView === 'function') switchTableView('driver');
          if (typeof switchTab === 'function') switchTab('onTrip');
        } catch (e) {
          console.error('Initialization error', e);
        }
      });
    </script>