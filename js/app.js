// js/app.js
let currentData = {
  drivers: [],
  passengers: [],
};

// Pagination managers
let driverPagination = null;
let passengerPagination = null;
const ITEMS_PER_PAGE = 10;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function () {
  // Set default view to Driver
  const driverTable = document.getElementById('driverTable');
  const customerTable = document.getElementById('customerTable');
  if (driverTable && customerTable) {
    // Ensure driver table is visible by default
    driverTable.classList.remove('d-none');
    driverTable.classList.add('d-block');
    customerTable.classList.remove('d-block');
    customerTable.classList.add('d-none');
  }

  // Initialize pagination managers
  const paginationContainer = document.getElementById('paginationContainer');
  if (paginationContainer) {
    driverPagination = new PaginationManager({
      containerId: 'paginationContainer',
      page: 1,
      limit: ITEMS_PER_PAGE,
      total: 0,
      onPageChange: (page, limit) => {
        loadDriversData(page, limit);
      }
    });

    passengerPagination = new PaginationManager({
      containerId: 'paginationContainer',
      page: 1,
      limit: ITEMS_PER_PAGE,
      total: 0,
      onPageChange: (page, limit) => {
        loadPassengersData(page, limit);
      }
    });
  }

  // Load initial data
  loadAllData();

  // Setup document preview modal handlers
  setupDocumentPreview();
});

// Setup document preview modal
function setupDocumentPreview() {
  // Use event delegation for dynamically added images
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('document-preview')) {
      const imageUrl = e.target.getAttribute('data-image-url');
      const title = e.target.getAttribute('data-title') || 'Document Preview';
      showImageModal(imageUrl, title);
    }
  });

  // Close modal when clicking close button or backdrop
  const modal = document.getElementById('imageModal');
  if (modal) {
    const closeBtn = modal.querySelector('.modal-close-btn');
    const backdrop = modal.querySelector('.modal-backdrop');

    if (closeBtn) {
      closeBtn.addEventListener('click', closeImageModal);
    }

    if (backdrop) {
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) {
          closeImageModal();
        }
      });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.classList.contains('d-none')) {
        closeImageModal();
      }
    });
  }
}

// Show image modal
function showImageModal(imageUrl, title) {
  const modal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const modalTitle = document.getElementById('modalTitle');

  if (modal && modalImage && modalTitle) {
    modalImage.src = imageUrl;
    modalImage.alt = title;
    modalTitle.textContent = title;
    modal.classList.remove('d-none');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }
}

// Close image modal
function closeImageModal() {
  const modal = document.getElementById('imageModal');
  if (modal) {
    modal.classList.add('d-none');
    document.body.style.overflow = ''; // Restore scrolling
  }
}

async function loadAllData() {
  // Load initial page for the visible table
  const driverTable = document.getElementById('driverTable');
  const customerTable = document.getElementById('customerTable');
  
  if (driverTable && !driverTable.classList.contains('d-none')) {
    await loadDriversData(1, ITEMS_PER_PAGE);
  } else if (customerTable && !customerTable.classList.contains('d-none')) {
    await loadPassengersData(1, ITEMS_PER_PAGE);
  } else {
    // Default to drivers
    await loadDriversData(1, ITEMS_PER_PAGE);
  }
}

async function loadDriversData(page = 1, limit = ITEMS_PER_PAGE) {
  try {
    const driverTbody = document.getElementById('driverTableBody');
    if (driverTbody) {
      driverTbody.innerHTML =
        '<tr><td colspan="16" class="text-center py-4 text-muted">Loading drivers...</td></tr>';
    }

    const response = await fetch(`api/get_drivers.php?page=${page}&limit=${limit}`);
    if (!response.ok) {
      throw new Error('Failed to fetch drivers from server');
    }

    const driversData = await response.json();

    if (driversData.success && driversData.data) {
      currentData.drivers = driversData.data;
      populateDriverTable(currentData.drivers);
      
      // Update pagination
      if (driverPagination && driversData.pagination) {
        driverPagination.update(driversData.pagination.total, page);
      }
      
      updateTabCounts();
    } else {
      console.error('Error loading drivers:', driversData.error || 'Unknown error');
      currentData.drivers = [];
      if (driverTbody) {
        driverTbody.innerHTML =
          '<tr><td colspan="16" class="text-center py-4 text-muted">No drivers to show</td></tr>';
      }
      if (driverPagination) {
        driverPagination.update(0, page);
      }
    }
  } catch (error) {
    console.error('Error loading drivers:', error);
    const driverTbody = document.getElementById('driverTableBody');
    if (driverTbody) {
      driverTbody.innerHTML =
        '<tr><td colspan="16" class="text-center py-4 text-danger">Error loading drivers. Please refresh the page.</td></tr>';
    }
    if (driverPagination) {
      driverPagination.update(0, 1);
    }
  }
}

async function loadPassengersData(page = 1, limit = ITEMS_PER_PAGE) {
  try {
    const passengerTbody = document.getElementById('customerTableBody');
    if (passengerTbody) {
      passengerTbody.innerHTML =
        '<tr><td colspan="8" class="text-center py-4 text-muted">Loading passengers...</td></tr>';
    }

    const response = await fetch(`api/get_passengers.php?page=${page}&limit=${limit}`);
    if (!response.ok) {
      throw new Error('Failed to fetch passengers from server');
    }

    const passengersData = await response.json();

    if (passengersData.success && passengersData.data) {
      currentData.passengers = passengersData.data;
      populateCustomerTable(currentData.passengers);
      
      // Update pagination
      if (passengerPagination && passengersData.pagination) {
        passengerPagination.update(passengersData.pagination.total, page);
      }
    } else {
      console.error('Error loading passengers:', passengersData.error || 'Unknown error');
      currentData.passengers = [];
      if (passengerTbody) {
        passengerTbody.innerHTML =
          '<tr><td colspan="8" class="text-center py-4 text-muted">No passengers to show</td></tr>';
      }
      if (passengerPagination) {
        passengerPagination.update(0, page);
      }
    }
  } catch (error) {
    console.error('Error loading passengers:', error);
    const passengerTbody = document.getElementById('customerTableBody');
    if (passengerTbody) {
      passengerTbody.innerHTML =
        '<tr><td colspan="8" class="text-center py-4 text-danger">Error loading passengers. Please refresh the page.</td></tr>';
    }
    if (passengerPagination) {
      passengerPagination.update(0, 1);
    }
  }
}

function populateDriverTable(drivers) {
  const tbody = document.getElementById('driverTableBody');
  tbody.innerHTML = '';

  if (!drivers || drivers.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="8" class="text-center py-4 text-muted">No drivers to show</td></tr>';
    return;
  }

  drivers.forEach((driver) => {
    const driverName = driver.full_name || driver.name || 'N/A';
    const vehicleInfo =
      driver.vehicle_make && driver.vehicle_model
        ? `${driver.vehicle_make} ${driver.vehicle_model}`
        : driver.vehicle_make || driver.vehicle_model || 'N/A';
    const row = document.createElement('tr');
    row.className = 'align-middle';
    row.innerHTML = `
      <td class="ps-3">
        <div class="d-flex align-items-center">
          <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
            ${
              driver.profile_pic_url
                ? `<img src="${driver.profile_pic_url}" alt="${driverName}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" />`
                : `<span class="fw-semibold">${getInitials(driverName)}</span>`
            }
          </div>

          <div>
            <div class="fw-medium">${driverName}</div>
          </div>
        </div>
      </td>
      <td>${driver.email || 'N/A'}</td>
      <td>${driver.phone || 'N/A'}</td>
      <td>${driver.cnic || 'N/A'}</td>
      <td>${vehicleInfo}</td>
      <td>${formatDate(driver.created_at)}</td>
      <td class="fs-6">${driver.vehicle_make || 'N/A'}</td>
      <td>${driver.vehicle_model || 'N/A'}</td>
      <td>${driver.vehicle_number || 'N/A'}</td>
      <td>${driver.license_number || 'N/A'}</td>
      <td>${driver.license_expiry || 'N/A'}</td>
      <td>${
        driver.license_url
          ? `<img src="${driver.license_url}" alt="License" class="document-preview" data-image-url="${driver.license_url}" data-title="License" style="width:36px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;" title="Click to view License" />`
          : 'N/A'
      }</td>
      <td>${
        driver.vehicle_reg_url
          ? `<img src="${driver.vehicle_reg_url}" alt="Vehicle Reg" class="document-preview" data-image-url="${driver.vehicle_reg_url}" data-title="Vehicle Registration" style="width:36px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;" title="Click to view Vehicle Registration" />`
          : 'N/A'
      }</td>
      <td>${
        driver.insurance_url
          ? `<img src="${driver.insurance_url}" alt="Insurance" class="document-preview" data-image-url="${driver.insurance_url}" data-title="Insurance" style="width:36px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;" title="Click to view Insurance" />`
          : 'N/A'
      }</td>
      <td>${driver.status || 'N/A'}</td>
      <td>${driver.last_active || 'N/A'}</td>
    `;
    tbody.appendChild(row);
  });
}

function populateCustomerTable(passengers) {
  const tbody = document.getElementById('customerTableBody');
  tbody.innerHTML = '';

  if (!passengers || passengers.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="8" class="text-center py-4 text-muted">No passengers to show</td></tr>';
    return;
  }

  passengers.forEach((passenger) => {
    const passengerName = passenger.name || passenger.full_name || 'N/A';
    const serviceType = passenger.service_type || 'Standard';
    const orderedTime = passenger.ordered_time || passenger.created_at;
    const email = passenger.email || 'N/A';
    const phone = passenger.phone || 'N/A';
    const isEmailVerified =
      passenger.is_email_verified !== undefined
        ? passenger.is_email_verified
          ? 'Yes'
          : 'No'
        : 'N/A';
    const createdAt = passenger.created_at || 'N/A';
    const updatedAt = passenger.updated_at || 'N/A';

    const row = document.createElement('tr');
    row.className = 'align-middle';
    row.innerHTML = `
            <td class="ps-3">
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
                         style="width: 40px; height: 40px;">
                        ${
                          passenger.photo_url || passenger.profile_pic_url
                            ? `<img src="${
                                passenger.photo_url || passenger.profile_pic_url
                              }" alt="${passengerName}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" />`
                            : `<span class="fw-semibold">${getInitials(
                                passengerName
                              )}</span>`
                        }
                    </div>
                    <div>
                        <div class="fw-medium">${passengerName}</div>
                    </div>
                </div>
            </td>
            <td>${serviceType}</td>
            <td>${formatDate(orderedTime)}</td>
            <td>${email}</td>
            <td>${phone}</td>
            <td>${isEmailVerified}</td>
            <td>${formatDate(createdAt)}</td>
            <td>${formatDate(updatedAt)}</td>
        `;
    tbody.appendChild(row);
  });
}

function updateTabCounts() {
  // Update tab counts based on your business logic
  try {
    const onTripCountEl = document.getElementById('onTripCount');
    const unassignedCountEl = document.getElementById('unassignedCount');
    const cancelledCountEl = document.getElementById('cancelledCount');
    const finishedCountEl = document.getElementById('finishedCount');
    const unpaidCountEl = document.getElementById('unpaidCount');
    const allMineCountEl = document.getElementById('allMineCount');
    const totalItemsEl = document.getElementById('totalItems');

    if (onTripCountEl) {
      const count = currentData.drivers ? currentData.drivers.length : 0;
      onTripCountEl.textContent = ` (${count})`;
    }
    if (unassignedCountEl) unassignedCountEl.textContent = '0';
    if (cancelledCountEl) cancelledCountEl.textContent = '0';
    if (finishedCountEl) finishedCountEl.textContent = '0';
    if (unpaidCountEl) unpaidCountEl.textContent = '0';
    if (allMineCountEl) {
      const count = currentData.drivers ? currentData.drivers.length : 0;
      allMineCountEl.textContent = ` (${count})`;
    }
    if (totalItemsEl) {
      const count = currentData.drivers ? currentData.drivers.length : 0;
      totalItemsEl.textContent = count;
    }
  } catch (error) {
    // Silently fail if elements don't exist
    console.log('Tab count elements not found, skipping update');
  }
}

// Helper functions
function getInitials(name) {
  if (!name || typeof name !== 'string') return 'N/A';
  return name
    .split(' ')
    .filter((n) => n.length > 0)
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2); // Limit to 2 characters
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return (
    date.toLocaleDateString() +
    ' ' +
    date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  );
}

function showLoading(show) {
  const spinner = document.getElementById('loadingSpinner');
  if (spinner) {
    if (show) {
      spinner.classList.remove('d-none');
    } else {
      spinner.classList.add('d-none');
    }
  }
}

// Your existing tab switching functions
function switchTab(tabName) {
  // Remove active class from all tabs
  document.querySelectorAll('[id$="Tab"]').forEach((tab) => {
    tab.classList.remove('active-tab');
    tab.style.color = '#3b3b3b';
    tab.style.borderBottom = 'none';
  });

  // Add active class to clicked tab
  const activeTab = document.getElementById(tabName + 'Tab');
  activeTab.classList.add('active-tab');
  activeTab.style.color = '#f37a20';
  activeTab.style.borderBottom = '3px solid #f37a20';

  // Here you can filter data based on tab selection
  // For now, we'll just reload all data
  loadAllData();
}

function switchTableView(viewType) {
  const driverTable = document.getElementById('driverTable');
  const customerTable = document.getElementById('customerTable');
  const driverBtn = document.getElementById('driverViewBtn');
  const customerBtn = document.getElementById('customerViewBtn');

  if (viewType === 'driver') {
    driverTable.classList.remove('d-none');
    driverTable.classList.add('d-block');
    customerTable.classList.remove('d-block');
    customerTable.classList.add('d-none');

    driverBtn.style.background = '#f37a20';
    driverBtn.style.color = 'white';
    driverBtn.style.borderColor = '#f37a20';
    customerBtn.style.background = '#fff';
    customerBtn.style.color = '#3b3b3b';
    customerBtn.style.borderColor = '#3b3b3b';

    // Load drivers data with pagination
    const currentPage = driverPagination ? driverPagination.getCurrentPage() : 1;
    loadDriversData(currentPage, ITEMS_PER_PAGE);
  } else {
    driverTable.classList.remove('d-block');
    driverTable.classList.add('d-none');
    customerTable.classList.remove('d-none');
    customerTable.classList.add('d-block');

    customerBtn.style.background = '#f37a20';
    customerBtn.style.color = 'white';
    customerBtn.style.borderColor = '#f37a20';
    driverBtn.style.background = '#fff';
    driverBtn.style.color = '#3b3b3b';
    driverBtn.style.borderColor = '#3b3b3b';

    // Load passengers data with pagination
    const currentPage = passengerPagination ? passengerPagination.getCurrentPage() : 1;
    loadPassengersData(currentPage, ITEMS_PER_PAGE);
  }
}
