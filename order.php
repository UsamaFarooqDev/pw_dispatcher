<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
  .toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1090; /* Above modals/navbar */
  }
</style>
  </head> 
  <body>
   
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
      <div
        class="d-flex flex-column flex-md-row justify-content-md-between align-items-center my-2 gap-3"
      >
        <div
          class="d-flex gap-3 w-100 w-md-auto justify-content-center justify-content-md-start"
        >
          <a
            href="map.php"
            class="btn glowing-btn fs-6 p-2 px-5 fw-semibold flex-grow-1 flex-md-grow-0"
            style="
              background: #f37a20;
              color: #fff;
              border-radius: 4px;
              box-shadow: 0 0 15px rgba(243, 122, 32, 0.5);
              min-width: 120px;
            "
          >
            Open Map
          </a>
        </div>
      </div>

      <div
        class="container-fluid p-0 my-4"
        style="display: flex; background-color: #f8f9fa"
      >
        <div
          class="col-md-12 bg-white p-4 d-flex flex-column"
          style="
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow-y: auto;
          "
        >
          <div class="row mb-3">
            <div class="col-md-6 position-relative">
              <label class="form-label fw-semibold small">Passenger Name</label>
              <input
                type="text"
                class="form-control text-muted"
                placeholder="Type to search passenger"
                id="customerNameInput"
                autocomplete="off"
              />
              <input type="hidden" id="customerId" />
              <div
                id="customerSuggestions"
                class="list-group position-absolute w-100"
                style="z-index: 10; max-height: 200px; overflow-y: auto; display: none;"
              ></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0">+353</span>
                <input
                  type="tel"
                  class="form-control text-muted border-start-0"
                  id="customerPhone"
                  placeholder=""
                />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Service Type</label>
                <select class="form-select text-muted" id="serviceType">
             <option value="Economy">Economy</option>
                     <option value="Economy XL">Economy XL</option>
                      <option value="Business">Business</option>
                   <option value="Business Plus">Business Plus</option>
                         <option value="Limousine">Limousine</option>
                               <option value="Wheelchair accessible">Wheelchair accessible</option>
                          </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Seats</label>
              <select class="form-select text-muted" id="seatCount">
                <option value="">Select seats</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Date</label>
              <input type="date" class="form-control text-muted" id="rideDate" />
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Time</label>
              <input type="time" class="form-control text-muted" id="rideTime" />
            </div>
          </div>

          <div class="container-fluid p-0" style="display: flex">
            <div
              class="col-md-6 bg-white p-2 d-flex flex-column"
              style="
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.09);
                overflow-y: auto;
              "
            >
              <div class="mb-3">
                <label class="form-label fw-semibold small">Pickup</label>
                <div class="input-group">
                  <span class="input-group-text bg-white">
                    <i class="bi bi-geo-alt" style="color: #f37a20"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control text-muted"
                    id="pickupInput"
                    placeholder="Enter pickup location"
                  />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Drop Off</label>
                <div class="input-group">
                  <span class="input-group-text bg-white">
                    <i class="bi bi-geo-alt" style="color: #f37a20"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control text-muted"
                    id="dropoffInput"
                    placeholder="Enter drop-off location"
                  />
                </div>
              </div>
              <div class="row g-2 mb-3">
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Estimated Fare</label>
                  <input type="text" class="form-control" id="estimatedFare" readonly />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Distance (km)</label>
                  <input type="text" class="form-control" id="distanceKm" readonly />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Time (min)</label>
                  <input type="text" class="form-control" id="travelTime" readonly />
                </div>
              </div>
              <div class="mb-3">
                <h6 class="fw-bold mb-3">Extras</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="creditCard"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="creditCard"
                        >Accept Credit Card</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="personWithDisabilities"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="personWithDisabilities"
                        >Person With Disabilities</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="childSeat"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="childSeat"
                        >Child Seat</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="nonSmoking"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="nonSmoking"
                        >Non Smoking</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="smokingAllowed"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="smokingAllowed"
                        >Smoking Allowed</label
                      >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="extraLuggage"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="extraLuggage"
                        >Extra Luggage Space</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="petsAllowed"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="petsAllowed"
                        >Pets Allowed</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="airConditioning"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="airConditioning"
                        >Air Conditioning</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="bikeMount"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="bikeMount"
                        >Bike Mount</label
                      >
                    </div>
                    <div class="form-check mb-2">
                      <input
                        class="form-check-output"
                        type="checkbox"
                        id="delivery"
                      />
                      <label
                        class="form-check-label small text-muted"
                        for="delivery"
                        >Delivery</label
                      >
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <h6 class="fw-bold mb-2">Special Cost</h6>
                <p class="text-muted small mb-2">
                  To set special cost, fill these fields
                </p>

                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small">Cost</label>
                    <div class="input-group">
                      <span class="input-group-text text-muted bg-white"
                        >EUR</span
                      >
                      <input
                        type="number"
                        class="form-control text-muted"
                        placeholder=""
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small"
                      >Km Included</label
                    >
                    <div class="input-group">
                      <span class="input-group-text text-muted bg-white"
                        >Km</span
                      >
                      <input
                        type="number"
                        class="form-control text-muted"
                        placeholder=""
                      />
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold small"
                    >Minutes Included</label
                  >
                  <div class="input-group">
                    <span class="input-group-text text-muted bg-white"
                      >min</span
                    >
                    <input
                      type="number"
                      class="form-control text-muted"
                      placeholder=""
                    />
                  </div>
                </div>

      <div class="mt-3 mb-1 d-flex gap-3 flex-wrap">
  <button
    class="btn px-3 py-1"
    style="
      color: #f37a20;
      border: 1px solid #f37a20;
      background-color: #fff;
      transition: all 0.3s ease;
    "
    onmouseenter="this.style.backgroundColor='#f37a20'; this.style.color='#fff';"
    onmouseleave="this.style.backgroundColor='#fff'; this.style.color='#f37a20';"
    data-bs-toggle="modal"
    data-bs-target="#assignDriverModal"
  >
    Assign Driver Manually
  </button>

  <button
    type="button"
    class="btn px-3 py-1"
    style="
      color: #f37a20;
      border: 1px solid #f37a20;
      background-color: #fff;
      transition: all 0.3s ease;
    "
    onmouseleave="this.style.backgroundColor='#f37a20'; this.style.color='#fff';"
    onmouseenter="this.style.backgroundColor='#fff'; this.style.color='#f37a20';"
    id="assignNearestDriverOpenBtn"
  >
    Assign Nearest Driver
  </button>

</div>

              </div>
            </div>

            

            <div
              class="col-md-6 p-0 position-relative"
              style="
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                margin-left: 1rem;
                background-color: #f8f9fa;
              "
            >
              <div id="map" style="width: 100%; height: 100%; border: 0;"></div>
            </div>
          </div>

          <div
            class="mt-auto pt-3 d-flex justify-content-between align-items-center"
          >
            <button
              type="button"
              class="btn btn-link fw-semibold p-0"
              style="font-size: 0.875rem; color: #f37a20"
              data-bs-toggle="modal"
              data-bs-target="#clearFieldsModal"
            >
              Clear Fields
            </button>
            <button
              class="btn btn-md text-white px-4"
              style="color: white; background-color: #f37a20"
              id="confirmOrderBtn"
            >
              Confirm Order
            </button>
          </div>
        </div>

        <div
          class="modal fade"
          id="clearFieldsModal"
          tabindex="-1"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div
              class="modal-content p-5 text-center"
              style="border-radius: 12px; max-width: 450px; margin: auto"
            >
              <div
                class="d-flex justify-content-center align-items-center mx-auto mb-3"
                style="
                  width: 60px;
                  height: 60px;
                  border: 4px solid #f37a20;
                  border-radius: 50%;
                "
              >
                <i
                  class="bi bi-exclamation-lg"
                  style="font-size: 3rem; color: #f37a20"
                ></i>
              </div>

              <h5 class="fw-bold mb-4">Are you sure to clear all fields?</h5>
              <div class="d-flex justify-content-center gap-3">
                <button
                  type="button"
                  class="btn px-4"
                  style="
                    background-color: #f37a20;
                    color: white;
                    border-radius: 6px;
                  "
                  onclick="clearAllFields()"
                >
                  Yes, I am sure
                </button>
                <button
                  type="button"
                  class="btn btn-outline-secondary px-4"
                  style="border-radius: 6px"
                  data-bs-dismiss="modal"
                >
                  Back to Dashboard
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Searching Driver Modal -->
<div
  class="modal fade"
  id="searchDriverModal"
  tabindex="-1"
  aria-hidden="true"
  data-bs-backdrop="static"
  data-bs-keyboard="false"
>
  <div class="modal-dialog modal-dialog-centered">
    <div
      class="modal-content p-5"
      style="border-radius: 12px; max-width: 500px; margin: auto"
    >
      <!-- Loading state -->
      <div id="searchDriverLoading" class="text-center">
        <div class="d-flex justify-content-center mb-4">
          <div
            class="spinner-border"
            role="status"
            style="width: 3rem; height: 3rem; border-width: 0.25em; color: #f37a20;"
          >
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <h4 class="fw-bold mb-3">Searching for drivers</h4>
        <p class="text-muted mb-0">Searching for drivers within 5km…</p>
      </div>

      <!-- Results: driver list -->
      <div id="searchDriverResults" class="d-none">
        <h4 class="fw-bold mb-3 text-center">Select a driver</h4>
        <p class="text-muted small text-center mb-3">Available drivers within 5km of pickup</p>
        <div id="searchDriverList" class="mb-4" style="max-height: 280px; overflow-y: auto;"></div>
        <div class="d-flex justify-content-center gap-3">
          <button
            type="button"
            class="btn px-4"
            style="background-color: #f37a20; color: white; border-radius: 6px;"
            id="assignNearestDriverBtn"
          >
            Assign
          </button>
          <button
            type="button"
            class="btn btn-outline-secondary px-4"
            style="border-radius: 6px"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
        </div>
      </div>

      <!-- No drivers found -->
      <div id="searchDriverEmpty" class="text-center d-none">
        <h4 class="fw-bold mb-2">No available drivers within 5km</h4>
        <p class="fw-semibold text-muted mb-4">Try again later or assign a driver manually.</p>
        <button
          type="button"
          class="btn btn-outline-secondary px-4"
          style="border-radius: 6px"
          data-bs-dismiss="modal"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</div>

        <!-- Assign Driver Manually Modal -->
        <div
          class="modal fade"
          id="assignDriverModal"
          tabindex="-1"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div
              class="modal-content p-5"
              style="border-radius: 12px; max-width: 500px; margin: auto"
            >
              <div class="text-center mb-2">
                <h3 class="fw-bold">Assign Driver Manually</h3>
                <p class="text-muted medium mb-4">
                  To manually assign driver, fill these fields
                </p>
              </div>

              <div class="modal-body p-0">
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Driver</label>
                  <select class="form-select" id="driverSelectModal">
                    <option selected disabled>Select driver</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold small">Driver Phone</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">+353</span>
                    <input
                      type="tel"
                      class="form-control border-start-0"
                      id="driverPhoneModal"
                      readonly
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold small">Vehicle Number</label>
                  <select class="form-select" id="vehicleSelectModal">
                    <option selected disabled>Select vehicle</option>
                  </select>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                  <button
                    type="button"
                    class="btn px-4"
                    style="
                      background-color: #f37a20;
                      color: white;
                      border-radius: 6px;
                    "
                    id="confirmAssignDriverBtn"
                  >
                    Confirm
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary px-4"
                    style="border-radius: 6px"
                    data-bs-dismiss="modal"
                  >
                    Back to Dashboard
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Order Created Success Modal -->
<div
  class="modal fade"
  id="orderCreatedModal"
  tabindex="-1"
  aria-hidden="true"
  data-bs-backdrop="static"
>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 p-4 border-0 shadow">
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i
            class="bi bi-check-circle-fill text-success"
            style="font-size: 2.8rem"
          ></i>
        </div>
        <h3 class="fw-bold mb-2">Order Created</h3>
        <p class="text-muted mb-0">
          The order has been created successfully.
        </p>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-3">
        <button
          type="button"
          class="btn fw-semibold rounded-2 px-4"
          style="background: #f37a20; color: white"
          id="goToOrdersBtn"
          data-bs-dismiss="modal"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</div>

    <!-- Toast for validation messages -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
      <div id="toastMsg" class="toast bg-white shadow" role="alert" aria-live="assertive" aria-atomic="true"
        style="border-radius: 8px; border: 1px solid #f0f0f0; min-width: 280px;">
        <div class="toast-header border-0" style="background: transparent;">
          <i class="bi bi-info-circle-fill me-2" style="color: #f37a20;"></i>
          <strong class="me-auto" style="color: #f37a20;">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body text-muted small" id="toastText"></div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9ea0A-mjnD5iHfT9X8Dn5YYH4_KZopLI&libraries=places&callback=initGoogleMaps" async defer></script>
    <script>
      let passengers = [];
      let drivers = [];
      let selectedPassengerId = null;
      let selectedDriverId = null;
      let selectedVehicleNumber = null;
      let map, directionsService, directionsRenderer;
      let pickupLatLng = null;
      let dropoffLatLng = null;
      let currentDistance = null;
      let currentDuration = null;
      let currentFare = null;
      let nearbyDriversList = [];
      let selectedNearbyDriverId = null;

    function showToast(message, isSuccess = false) {
  const toastEl = document.getElementById('toastMsg');
  const toastText = document.getElementById('toastText');

  if (!toastEl || !toastText) {
    console.warn('Toast elements not found in DOM');
    return;
  }

  toastText.innerHTML = `<span style="font-weight: 500; font-size: 14px;">${message}</span>`;
  toastEl.classList.remove('bg-success', 'bg-danger');
  toastEl.style.borderLeft = `4px solid ${isSuccess ? '#28a745' : '#f37a20'}`;

  let bsToast = bootstrap.Toast.getInstance(toastEl);
  if (!bsToast) {
    bsToast = new bootstrap.Toast(toastEl, {
      autohide: true,
      delay: 4000
    });
  }

  bsToast.show();
}

      document.addEventListener('DOMContentLoaded', () => {
        fetchPassengers();
        fetchDrivers();
        // initGoogleMaps called by Maps API callback when ready (ensures Places autocomplete works)
        setupCustomerAutocomplete();
        setupDriverModal();
        setupRouteListeners();
        setupConfirmOrder();
        setupAssignNearestDriver();
      });

      /* ---------------------- Customers ---------------------- */
      async function fetchPassengers() {
        try {
          const res = await fetch('api/get_passengers.php');
          const data = await res.json();
          if (data.success && data.data) {
            passengers = data.data;
          }
        } catch (err) {
          console.error('Error fetching passengers', err);
        }
      }

      function setupCustomerAutocomplete() {
        const nameInput = document.getElementById('customerNameInput');
        const phoneInput = document.getElementById('customerPhone');
        const suggestions = document.getElementById('customerSuggestions');
        const customerIdInput = document.getElementById('customerId');

        const render = (items) => {
          suggestions.innerHTML = '';
          if (!items.length) {
            suggestions.style.display = 'none';
            return;
          }
          items.forEach((p) => {
            const div = document.createElement('button');
            div.type = 'button';
            div.className = 'list-group-item list-group-item-action';
            div.textContent = p.name || p.full_name || 'Unknown';
            div.addEventListener('click', () => {
              nameInput.value = p.name || p.full_name || '';
              phoneInput.value = (p.phone || p.phone_number || '').replace('+353', '');
              customerIdInput.value = p.id || '';
              selectedPassengerId = p.id || null;
              suggestions.style.display = 'none';
            });
            suggestions.appendChild(div);
          });
          suggestions.style.display = 'block';
        };

        let debounceTimer = null;
        nameInput.addEventListener('input', (e) => {
          clearTimeout(debounceTimer);
          const term = e.target.value.toLowerCase();
          debounceTimer = setTimeout(() => {
            if (!term) {
              suggestions.style.display = 'none';
              return;
            }
            const matches = passengers.filter((p) => {
              const name = (p.name || p.full_name || '').toLowerCase();
              return name.includes(term);
            }).slice(0, 8);
            render(matches);
          }, 200);
        });

        document.addEventListener('click', (e) => {
          if (!suggestions.contains(e.target) && e.target !== nameInput) {
            suggestions.style.display = 'none';
          }
        });
      }

      /* ---------------------- Drivers ---------------------- */
      async function fetchDrivers() {
        try {
          const res = await fetch('api/get_drivers.php');
          const data = await res.json();
          if (data.success && data.data) {
            drivers = data.data;
            populateDriverSelects();
          }
        } catch (err) {
          console.error('Error fetching drivers', err);
        }
      }

      function populateDriverSelects() {
        const modalSelect = document.getElementById('driverSelectModal');
        const driverSelect = document.getElementById('driverSelectModal');
        if (!modalSelect) return;
        modalSelect.innerHTML = '<option selected disabled>Select driver</option>';
        drivers.forEach((d) => {
          const opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = `${d.full_name || d.name || 'Driver'} — ${d.vehicle_make || ''}`;
          modalSelect.appendChild(opt);
        });
      }

      function setupDriverModal() {
        const driverSelect = document.getElementById('driverSelectModal');
        const phoneInput = document.getElementById('driverPhoneModal');
        const vehicleSelect = document.getElementById('vehicleSelectModal');
        const confirmBtn = document.getElementById('confirmAssignDriverBtn');

        if (driverSelect) {
          driverSelect.addEventListener('change', () => {
            const driver = drivers.find((d) => d.id == driverSelect.value);
            if (!driver) return;
            selectedDriverId = driver.id;
            phoneInput.value = (driver.phone || '').replace('+353', '');
            vehicleSelect.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = driver.vehicle_number || '';
            opt.textContent = driver.vehicle_number || 'Vehicle';
            vehicleSelect.appendChild(opt);
            selectedVehicleNumber = driver.vehicle_number || null;
          });
        }

        if (confirmBtn) {
          confirmBtn.addEventListener('click', () => {
            if (!selectedDriverId) {
              alert('Please select a driver');
              return;
            }
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('assignDriverModal')
            );
            modal?.hide();
          });
        }
      }

      /* ---------------------- Assign Nearest Driver ---------------------- */
      function setupAssignNearestDriver() {
        const openBtn = document.getElementById('assignNearestDriverOpenBtn');
        const modalEl = document.getElementById('searchDriverModal');
        const loadingEl = document.getElementById('searchDriverLoading');
        const resultsEl = document.getElementById('searchDriverResults');
        const emptyEl = document.getElementById('searchDriverEmpty');
        const listEl = document.getElementById('searchDriverList');
        const assignBtn = document.getElementById('assignNearestDriverBtn');

        if (!openBtn || !modalEl) return;

        openBtn.addEventListener('click', () => {
          const pickup = document.getElementById('pickupInput')?.value?.trim() || '';
          const dropoff = document.getElementById('dropoffInput')?.value?.trim() || '';
          if (!pickup || !dropoff) {
            alert('Please enter both pickup and drop-off locations before assigning a driver.');
            return;
          }

          function showModalState(loading, results, empty) {
            if (loadingEl) loadingEl.classList.toggle('d-none', !loading);
            if (resultsEl) resultsEl.classList.toggle('d-none', !results);
            if (emptyEl) emptyEl.classList.toggle('d-none', !empty);
          }

          function getPickupLatLng(cb) {
            if (pickupLatLng && typeof pickupLatLng.lat === 'function') {
              cb(pickupLatLng.lat(), pickupLatLng.lng());
              return;
            }
            if (typeof google === 'undefined' || !google.maps || !google.maps.Geocoder) {
              cb(null, null);
              return;
            }
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: pickup }, (results, status) => {
              if (status === google.maps.GeocoderStatus.OK && results && results[0] && results[0].geometry) {
                const loc = results[0].geometry.location;
                cb(loc.lat(), loc.lng());
              } else {
                cb(null, null);
              }
            });
          }

          const modal = new bootstrap.Modal(modalEl);
          modal.show();
          showModalState(true, false, false);
          nearbyDriversList = [];
          selectedNearbyDriverId = null;
          if (listEl) listEl.innerHTML = '';
          // Reset empty state to default message
          if (emptyEl) {
            const h4 = emptyEl.querySelector('h4');
            const p = emptyEl.querySelector('.text-muted');
            if (h4) h4.textContent = 'No available drivers within 5km.';
            if (p) p.textContent = 'Try again later or assign a driver manually.';
          }

          getPickupLatLng((lat, lng) => {
            if (lat == null || lng == null) {
              showModalState(false, false, true);
              if (emptyEl) {
                const h4 = emptyEl.querySelector('h4');
                const p = emptyEl.querySelector('.text-muted');
                if (h4) h4.textContent = 'Could not find pickup location';
                if (p) p.textContent = 'Please select a valid pickup address from the suggestions.';
              }
              return;
            }
            const url = `api/get_nearby_drivers.php?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}&radius_km=5`;
            fetch(url)
              .then((res) => res.json())
              .then((data) => {
                if (data.success && data.data && data.data.length > 0) {
                  nearbyDriversList = data.data;
                  selectedNearbyDriverId = null;
                  listEl.innerHTML = '';
                  data.data.forEach((d) => {
                    const box = document.createElement('div');
                    box.className = 'border rounded p-3 mb-2 cursor-pointer';
                    box.style.cursor = 'pointer';
                    box.style.borderColor = '#dee2e6';
                    box.style.transition = 'border-color 0.2s, box-shadow 0.2s';
                    box.dataset.driverId = d.id;
                    box.innerHTML = `
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong class="d-block">${(d.full_name || d.name || 'Driver').escapeHtml()}</strong>
                          <span class="text-muted small">${(d.vehicle_make || d.vehicle_brand || 'Vehicle').escapeHtml()}</span>
                        </div>
                      </div>
                    `;
                    box.addEventListener('click', () => {
                      selectedNearbyDriverId = d.id;
                      listEl.querySelectorAll('.border').forEach((b) => {
                        b.style.borderColor = '#dee2e6';
                        b.style.boxShadow = 'none';
                      });
                      box.style.borderColor = '#f37a20';
                      box.style.boxShadow = '0 0 0 2px rgba(243,122,32,0.3)';
                    });
                    listEl.appendChild(box);
                  });
                  showModalState(false, true, false);
                } else {
                  showModalState(false, false, true);
                }
              })
              .catch(() => {
                showModalState(false, false, true);
                const h4 = emptyEl ? emptyEl.querySelector('h4') : null;
                const p = emptyEl ? emptyEl.querySelector('.text-muted') : null;
                if (h4) h4.textContent = 'Something went wrong';
                if (p) p.textContent = 'Try again later or assign a driver manually.';
              });
          });
        });

        if (assignBtn) {
          assignBtn.addEventListener('click', () => {
            if (!selectedNearbyDriverId || !nearbyDriversList.length) {
              alert('Please select a driver from the list.');
              return;
            }
            const driver = nearbyDriversList.find((d) => d.id == selectedNearbyDriverId);
            if (driver) {
              selectedDriverId = driver.id;
              selectedVehicleNumber = driver.vehicle_number || null;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('searchDriverModal'));
            modal?.hide();
          });
        }
      }

      if (typeof String.prototype.escapeHtml !== 'function') {
        String.prototype.escapeHtml = function () {
          const div = document.createElement('div');
          div.textContent = this;
          return div.innerHTML;
        };
      }

      /* ---------------------- Google Maps ---------------------- */
      function initGoogleMaps() {
        if (typeof google === 'undefined' || !google.maps) {
          setTimeout(initGoogleMaps, 200);
          return;
        }
        const mapEl = document.getElementById('map');
        if (!mapEl) {
          setTimeout(initGoogleMaps, 100);
          return;
        }
        map = new google.maps.Map(mapEl, {
          center: { lat: 53.349805, lng: -6.26031 },
          zoom: 12,
        });
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({ map });

        const pickupInput = document.getElementById('pickupInput');
        const dropoffInput = document.getElementById('dropoffInput');

        if (pickupInput && dropoffInput && google.maps.places) {
          const pickupAC = new google.maps.places.Autocomplete(pickupInput);
          const dropoffAC = new google.maps.places.Autocomplete(dropoffInput);

          pickupAC.addListener('place_changed', () => {
            const place = pickupAC.getPlace();
            if (place && place.geometry) {
              pickupLatLng = place.geometry.location;
            }
            tryCalculateRoute();
          });
          dropoffAC.addListener('place_changed', () => {
            const place = dropoffAC.getPlace();
            if (place && place.geometry) {
              dropoffLatLng = place.geometry.location;
            }
            tryCalculateRoute();
          });
        }
      }

      function setupRouteListeners() {
        const pickupInput = document.getElementById('pickupInput');
        const dropoffInput = document.getElementById('dropoffInput');
        const rideDate = document.getElementById('rideDate');
        const rideTime = document.getElementById('rideTime');
        const handler = () => tryCalculateRoute();
        [pickupInput, dropoffInput, rideDate, rideTime].forEach((el) => {
          if (el) el.addEventListener('change', handler);
          if (el) el.addEventListener('blur', handler);
        });
      }

      function tryCalculateRoute() {
        const pickup = document.getElementById('pickupInput')?.value;
        const dropoff = document.getElementById('dropoffInput')?.value;
        if (!pickup || !dropoff || !directionsService) return;

        const req = {
          origin: pickupLatLng || pickup,
          destination: dropoffLatLng || dropoff,
          travelMode: google.maps.TravelMode.DRIVING,
        };
        directionsService.route(req, (result, status) => {
          if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(result);
            const leg = result.routes[0].legs[0];
            currentDistance = leg.distance.value / 1000;
            currentDuration = Math.round(leg.duration.value / 60);
            const pickupTimeStr = buildPickupDateTime();
            currentFare = calculateFare(currentDistance, pickupTimeStr);
            updateSummaryFields();
            pickupLatLng = leg.start_location;
            dropoffLatLng = leg.end_location;
          }
        });
      }

      function buildPickupDateTime() {
        const d = document.getElementById('rideDate')?.value;
        const t = document.getElementById('rideTime')?.value;
        if (d && t) return `${d}T${t}`;
        const now = new Date();
        return now.toISOString().slice(0, 16);
      }

      function calculateFare(distanceKm, pickupTimeStr) {
        const pickupDate = new Date(pickupTimeStr);
        const hour = pickupDate.getHours();
        let baseFare, ratePerKm;
        if (hour >= 8 && hour < 20) {
          baseFare = 4.4;
          ratePerKm = 1.32;
        } else {
          baseFare = 5.4;
          ratePerKm = 1.81;
        }
        return baseFare + ratePerKm * distanceKm;
      }

      function updateSummaryFields() {
        const fareEl = document.getElementById('estimatedFare');
        const distEl = document.getElementById('distanceKm');
        const timeEl = document.getElementById('travelTime');
        if (fareEl && currentFare != null) fareEl.value = `€${currentFare.toFixed(2)}`;
        if (distEl && currentDistance != null) distEl.value = currentDistance.toFixed(2);
        if (timeEl && currentDuration != null) timeEl.value = currentDuration.toString();
      }

      /* ---------------------- Confirm Order ---------------------- */
      function setupConfirmOrder() {
        const btn = document.getElementById('confirmOrderBtn');
        if (btn) {
          btn.addEventListener('click', createOrder);
        }
      }

      async function createOrder() {
  const passengerId = selectedPassengerId;
  const customerName = document.getElementById('customerNameInput')?.value?.trim() || '';
  const phoneRaw = document.getElementById('customerPhone')?.value?.trim() || '';
  const serviceType = document.getElementById('serviceType')?.value || 'Economy';
  const seats = document.getElementById('seatCount')?.value || '';
  const pickup = document.getElementById('pickupInput')?.value?.trim() || '';
  const dropoff = document.getElementById('dropoffInput')?.value?.trim() || '';
  const rideDateVal = document.getElementById('rideDate')?.value || '';
  const rideTimeVal = document.getElementById('rideTime')?.value || '';
  const pickupTimeStr = buildPickupDateTime();
    const phone = phoneRaw
    ? phoneRaw.startsWith('+353')
      ? phoneRaw
      : '+353' + phoneRaw.replace(/^0+/, '')
    : '';

  if (!customerName) {
    alert('Please select a customer.');
    return;
  }

  if (!phone) {
    alert('Please enter customer phone.');
    return;
  }

  if (!rideDateVal || !rideTimeVal) {
    alert('Please select date and time.');
    return;
  }

  if (!seats) {
    alert('Please select seats.');
    return;
  }

  if (!pickup || !dropoff) {
    alert('Please enter pickup and drop-off locations.');
    return;
  }

  if (!currentDistance || !currentDuration || !currentFare || !pickupLatLng || !dropoffLatLng) {
    alert('Please wait for route/fare calculation to finish.');
    return;
  }

        const payload = {
          user_id: passengerId,
          customer_name: customerName,
          phone_number: phone,
          passenger_name: customerName,
          passenger_phone: phone,
          service_type: serviceType,
          seats,
          date: rideDateVal,
          time: rideTimeVal,
          pickup_addr: pickup,
          dest_addr: dropoff,
          pickup_lat: pickupLatLng ? pickupLatLng.lat() : null,
          pickup_lng: pickupLatLng ? pickupLatLng.lng() : null,
          dest_lat: dropoffLatLng ? dropoffLatLng.lat() : null,
          dest_lng: dropoffLatLng ? dropoffLatLng.lng() : null,
          distance_km: currentDistance,
          duration_min: currentDuration,
          fare_eur: currentFare,
          payment_method: 'cash',
          service_type_display: serviceType,
          scheduled_at: pickupTimeStr,
          driver_id: selectedDriverId || null,
          vehicle_number: selectedVehicleNumber || null,
        };

        try {
          const res = await fetch('api/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (data.success) {
            const modal = new bootstrap.Modal(
  document.getElementById('orderCreatedModal')
);
modal.show();

document
  .getElementById('goToOrdersBtn')
  .addEventListener('click', () => {
    window.location.href = 'order.php';
  });

          } else {
            alert('Error creating order: ' + (data.error || 'Unknown error'));
          }
        } catch (err) {
          console.error('Order create error', err);
          alert('Failed to create order.');
        }
      }
    </script>
    <div class="toast-container" id="toastContainer"></div>
    <div 
  class="toast align-items-center text-white" 
  role="alert" 
  aria-live="assertive" 
  aria-atomic="true"
  id="toastMsg"
  style="min-width: 280px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
>
  <div class="d-flex w-100 p-3">
    <div id="toastText" class="me-auto"></div>
    <button 
      type="button" 
      class="btn-close btn-close-white ms-2" 
      data-bs-dismiss="toast" 
      aria-label="Close"
    ></button>
  </div>
</div>
  </body>
</html>
