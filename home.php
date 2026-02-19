<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    header('Location: login.php');
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
      style="border-radius: 14px; color: #3b3b3b; min-height: 36px;"
    />
  </div>
</div>

<div class="card shadow border-0 mt-4 h-100" style='border-radius: 25px;'>
        <div class="card-body">
          <div class="container-fluid p-3">

<div class="d-flex align-items-center mb-3">
  <button 
    id="driverViewBtn" 
    class="btn btn-sm fw-semibold me-2 px-3" 
    style="background: #f37a20; color: white; border-radius: 14px;"
    onclick="switchTableView('driver')"
  >
    Driver
  </button>
  <button 
    id="customerViewBtn" 
    class="btn btn-sm fw-semibold px-3" 
    style="background: #fff; color: #3b3b3b; border: 1px solid #3b3b3b; border-radius: 14px;"
    onclick="switchTableView('customer')"
  >
    Passenger
  </button>
</div>

<div id="tableContainer" class="mb-4">

  <div id="driverTable" class="table-responsive bg-white rounded shadow-sm mb-3 d-block" style="min-height: 362px">
    <table class="table table-borderless mb-0">
      <thead>
        <tr class="bg-light">
  <th scope="col" class="fw-semibold text-nowrap">Name</th>
  <th scope="col" class="fw-semibold text-nowrap">Email</th>
  <th scope="col" class="fw-semibold text-nowrap">Phone</th>
  <th scope="col" class="fw-semibold text-nowrap">CNIC</th>
  <th scope="col" class="fw-semibold text-nowrap">Car Comfort</th>
  <th scope="col" class="fw-semibold text-nowrap">Ordered Time</th>
  <th scope="col" class="fw-semibold text-nowrap">Vehicle Make</th>
  <th scope="col" class="fw-semibold text-nowrap">Vehicle Model</th>
  <th scope="col" class="fw-semibold text-nowrap">Vehicle Number</th>
  <th scope="col" class="fw-semibold text-nowrap">License Number</th>
  <th scope="col" class="fw-semibold text-nowrap">License Expiry</th>
  <th scope="col" class="fw-semibold text-nowrap">License</th>
  <th scope="col" class="fw-semibold text-nowrap">Vehicle Reg</th>
  <th scope="col" class="fw-semibold text-nowrap">Insurance</th>
  <th scope="col" class="fw-semibold text-nowrap">Status</th>
  <th scope="col" class="fw-semibold text-nowrap">Last Active</th>
</tr>

      </thead>
      <tbody id="driverTableBody">
        <!-- Data will be injected here by JS -->
      </tbody>
    </table>
  </div>

  <!-- Customer Table -->
  <div id="customerTable" class="table-responsive bg-white rounded shadow-sm mb-3 d-none" style="min-height: 362px">
    <table class="table table-borderless mb-0">
      <thead>
        <tr class="bg-light">
          <th scope="col" class="fw-semibold text-nowrap">Name</th>
          <th scope="col" class="fw-semibold text-nowrap">Service Type</th>
          <th scope="col" class="fw-semibold text-nowrap">Ordered Time</th>
          <th scope="col" class="fw-semibold text-nowrap">Email</th>
          <th scope="col" class="fw-semibold text-nowrap">Phone</th>
          <th scope="col" class="fw-semibold text-nowrap">Email Verified</th>
          <th scope="col" class="fw-semibold text-nowrap">Created At</th>
          <th scope="col" class="fw-semibold text-nowrap">Updated At</th>
        </tr>
      </thead>
      <tbody id="customerTableBody">
        <!-- Data will be injected here by JS -->
      </tbody>
    </table>
  </div>
</div>
</div>

<!-- Pagination -->
<div id="paginationContainer" class="mt-3"></div>  
</div>
</div>
</div>
</div>
</div>
    </main>

    <div id="imageModal" class="image-modal d-none">
      <div class="modal-backdrop"></div>
      <div class="modal-content-wrapper">
        <div class="modal-card">
          <div class="modal-header">
            <h5 id="modalTitle" class="modal-title">Document Preview</h5>
            <button class="modal-close-btn" aria-label="Close">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body">
            <img id="modalImage" src="" alt="Document" class="modal-image" />
          </div>
        </div>
      </div>
    </div>

    <style>
      .image-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(2px);
      }

      .modal-content-wrapper {
        position: relative;
        z-index: 10000;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        animation: modalFadeIn 0.2s ease-out;
      }

      @keyframes modalFadeIn {
        from {
          opacity: 0;
          transform: scale(0.95);
        }
        to {
          opacity: 1;
          transform: scale(1);
        }
      }

      .modal-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
      }

      .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
      }

      .modal-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
      }

      .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #666;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
      }

      .modal-close-btn:hover {
        background: #e0e0e0;
        color: #333;
      }

      .modal-body {
        padding: 20px;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
        flex: 1;
      }

      .modal-image {
        max-width: 100%;
        max-height: calc(90vh - 120px);
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      /* Document preview image hover effect */
      .document-preview {
        transition: transform 0.2s, box-shadow 0.2s;
      }

      .document-preview:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 10;
        position: relative;
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
            <img src="assets/profile.svg" alt="${type}" class="rounded-circle me-2" />
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