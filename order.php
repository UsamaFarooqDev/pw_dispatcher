<?php
session_start();

require_once 'auth/require_login_redirect.php';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
     .toast-container {
  pointer-events: none;
}
.toast {
  pointer-events: auto;
}
</style>
  </head> 
  <body>
   
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

<main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">
  <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
    <div class="p-4">

      <div class="mb-1 pb-2" style="border-bottom:1px solid #EBEBEB;">
        <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Passenger Details</span>
      </div>

      <div class="row g-3 mt-1 mb-3">
        <div class="col-md-6 position-relative">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Passenger Name</label>
          <input type="text" class="form-control" placeholder="Type to search passenger"
            id="customerNameInput" autocomplete="off"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
          <input type="hidden" id="customerId" />
          <div id="customerSuggestions" class="list-group position-absolute w-100"
            style="z-index:10; max-height:200px; overflow-y:auto; display:none; border:1.5px solid #EBEBEB; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.10); top:100%; margin-top:4px;"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Phone Number</label>
          <div class="input-group" style="height:38px;">
            <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.845rem; color:#71717A; height:38px;">+353</span>
            <input type="tel" class="form-control" id="customerPhone"
              style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;"
              onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
              onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
          </div>
        </div>
      </div>

      <div class="mb-1 pb-2 mt-4" style="border-bottom:1px solid #EBEBEB;">
        <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Ride Details</span>
      </div>

      <div class="row g-3 mt-1 mb-4">
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Service Type</label>
          <select class="form-select" id="serviceType"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
            <option value="Economy">Economy</option>
            <option value="Economy XL">Economy XL</option>
            <option value="Business">Business</option>
            <option value="Business Plus">Business Plus</option>
            <option value="Limousine">Limousine</option>
            <option value="Wheelchair accessible">Wheelchair Accessible</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Seats</label>
          <select class="form-select" id="seatCount"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
            <option value="">Select seats</option>
            <option>1</option><option>2</option><option>3</option>
            <option>4</option><option>5</option><option>6</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Date</label>
          <input type="date" class="form-control" id="rideDate"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Time</label>
          <input type="time" class="form-control" id="rideTime"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
        </div>
      </div>

      <div class="row g-3">

        <div class="col-md-6">
          <div class="rounded-3 p-3 h-100" style="border:1.5px solid #EBEBEB; background:#FAFAFA;">

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Pickup</label>
              <div class="input-group" style="height:38px;">
                <span class="input-group-text" style="background:#fff; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; height:38px;">
                  <i class="bi bi-geo-alt-fill" style="color:#f37a20; font-size:14px;"></i>
                </span>
                <input type="text" class="form-control" id="pickupInput" placeholder="Enter pickup location"
                  style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#fff;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Drop Off</label>
              <div class="input-group" style="height:38px;">
                <span class="input-group-text" style="background:#fff; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; height:38px;">
                  <i class="bi bi-geo-alt" style="color:#f37a20; font-size:14px;"></i>
                </span>
                <input type="text" class="form-control" id="dropoffInput" placeholder="Enter drop-off location"
                  style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#fff;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
              </div>
            </div>

            <div class="row g-2 mb-4">
              <div class="col-4">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Est. Fare</label>
                <input type="text" class="form-control" id="estimatedFare" readonly
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#fff; color:#18181B; font-weight:600;" />
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Distance (km)</label>
                <input type="text" class="form-control" id="distanceKm" readonly
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#fff; color:#18181B;" />
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Time (min)</label>
                <input type="text" class="form-control" id="travelTime" readonly
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#fff; color:#18181B;" />
              </div>
            </div>

            <div class="mb-3" style="border-top:1px solid #EBEBEB;"></div>

            <div class="mb-3">
              <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Extras</span>
              <div class="row g-1">
                <div class="col-6 d-flex flex-column gap-2">
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="creditCard" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="creditCard" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Accept Credit Card</label>
                  </div>
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="personWithDisabilities" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="personWithDisabilities" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Person With Disabilities</label>
                  </div>
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="childSeat" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="childSeat" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Child Seat</label>
                  </div>
                </div>
                <div class="col-6 d-flex flex-column gap-2">
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="extraLuggage" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="extraLuggage" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Extra Luggage Space</label>
                  </div>
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="petsAllowed" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="petsAllowed" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Pets Allowed</label>
                  </div>
                  <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" id="delivery" style="accent-color:#f37a20; width:15px; height:15px;" />
                    <label class="form-check-label" for="delivery" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Delivery</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3" style="border-top:1px solid #EBEBEB;"></div>

            <div class="mb-3">
              <span class="fw-bold d-block mb-1" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Special Cost</span>
              <p class="mb-3" style="font-size:0.78rem; color:#A1A1AA;">Leave blank to use default fare calculation</p>
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Cost</label>
                  <div class="input-group" style="height:38px;">
                    <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">EUR</span>
                    <input type="number" class="form-control" style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                      onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                      onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Km Included</label>
                  <div class="input-group" style="height:38px;">
                    <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">km</span>
                    <input type="number" class="form-control" style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                      onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                      onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
                  </div>
                </div>
              </div>
              <div>
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Minutes Included</label>
                <div class="input-group" style="height:38px;">
                  <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">min</span>
                  <input type="number" class="form-control" style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                    onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                    onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-3">
              <button class="btn d-flex align-items-center gap-2 fw-semibold px-3"
                style="height:36px; font-size:0.8125rem; color:#f37a20; border:1.5px solid #f37a20; background:#fff; border-radius:8px;"
                onmouseover="this.style.background='#FFF3E8';"
                onmouseout="this.style.background='#fff';"
                data-bs-toggle="modal" data-bs-target="#assignDriverModal">
                <i class="bi bi-person-check" style="font-size:14px;"></i> Assign Driver Manually
              </button>
              <button type="button" class="btn d-flex align-items-center gap-2 fw-semibold px-3"
                style="height:36px; font-size:0.8125rem; color:#f37a20; border:1.5px solid #f37a20; background:#fff; border-radius:8px;"
                onmouseover="this.style.background='#FFF3E8';"
                onmouseout="this.style.background='#fff';"
                id="assignNearestDriverOpenBtn">
                <i class="bi bi-geo" style="font-size:14px;"></i> Assign Nearest Driver
              </button>
            </div>

          </div>
        </div>

        <div class="col-md-6">
          <div class="rounded-3 overflow-hidden h-100" style="border:1.5px solid #EBEBEB; min-height:480px;">
            <div id="map" style="width:100%; height:100%; min-height:480px; border:0;"></div>
          </div>
        </div>

      </div>

      <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top:1px solid #EBEBEB;">
        <button type="button" class="btn d-flex align-items-center gap-1 fw-semibold p-0"
          style="font-size:0.845rem; color:#A1A1AA; background:transparent; border:none;"
          onmouseover="this.style.color='#E11D48';"
          onmouseout="this.style.color='#A1A1AA';"
          data-bs-toggle="modal" data-bs-target="#clearFieldsModal">
          <i class="bi bi-trash3" style="font-size:14px;"></i> Clear Fields
        </button>
        <button class="btn d-flex align-items-center gap-2 fw-semibold px-5"
          style="height:40px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.875rem; box-shadow:0 4px 14px rgba(243,122,32,0.35);"
          onmouseover="this.style.background='#d96010';"
          onmouseout="this.style.background='#f37a20';"
          id="confirmOrderBtn">
          <i class="bi bi-check2-circle" style="font-size:15px;"></i> Confirm Order
        </button>
      </div>

    </div>
  </div>

  <div class="modal fade" id="clearFieldsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 p-4 text-center" style="border-radius:14px; max-width:420px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
          style="width:56px; height:56px; background:#FFF3E8; border-radius:50%;">
          <i class="bi bi-exclamation-lg" style="font-size:1.75rem; color:#f37a20;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color:#18181B;">Clear all fields?</h5>
        <p class="mb-4" style="font-size:0.845rem; color:#71717A;">This will reset the entire order form. This action cannot be undone.</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn fw-semibold px-4"
            style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
            onmouseover="this.style.background='#d96010';"
            onmouseout="this.style.background='#f37a20';"
            onclick="clearAllFields()">Yes, clear it
          </button>
          <button type="button" class="btn fw-semibold px-4"
            style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
            onmouseover="this.style.borderColor='#18181B';"
            onmouseout="this.style.borderColor='#EBEBEB';"
            data-bs-dismiss="modal">Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="searchDriverModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 p-4" style="border-radius:14px; max-width:480px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

        <div id="searchDriverLoading" class="text-center py-3">
          <div class="d-flex justify-content-center mb-4">
            <div class="spinner-border" role="status" style="width:2.5rem; height:2.5rem; border-width:3px; color:#f37a20;">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <h5 class="fw-bold mb-2" style="color:#18181B;">Searching for drivers</h5>
          <p style="font-size:0.845rem; color:#71717A;" class="mb-0">Finding available drivers within 5km…</p>
        </div>

        <div id="searchDriverResults" class="d-none">
          <h5 class="fw-bold mb-1 text-center" style="color:#18181B;">Select a Driver</h5>
          <p class="text-center mb-3" style="font-size:0.8rem; color:#A1A1AA;">Available within 5km of pickup</p>
          <div id="searchDriverList" class="mb-4" style="max-height:280px; overflow-y:auto;"></div>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn fw-semibold px-5"
              style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
              id="assignNearestDriverBtn">Assign
            </button>
            <button type="button" class="btn fw-semibold px-4"
              style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
              data-bs-dismiss="modal">Cancel
            </button>
          </div>
        </div>

        <div id="searchDriverEmpty" class="text-center d-none py-3">
          <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
            style="width:52px; height:52px; background:#FFF1F2; border-radius:50%;">
            <i class="bi bi-person-x" style="font-size:1.5rem; color:#E11D48;"></i>
          </div>
          <h5 class="fw-bold mb-2" style="color:#18181B;">No drivers nearby</h5>
          <p class="mb-4" style="font-size:0.845rem; color:#71717A;">No available drivers within 5km. Try again or assign manually.</p>
          <button type="button" class="btn fw-semibold px-4"
            style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
            data-bs-dismiss="modal">Close
          </button>
        </div>

      </div>
    </div>
  </div>

  <div class="modal fade" id="assignDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 p-4" style="border-radius:14px; max-width:480px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
        <div class="mb-4">
          <h5 class="fw-bold mb-1" style="color:#18181B;">Assign Driver Manually</h5>
          <p style="font-size:0.8rem; color:#A1A1AA; margin:0;">Select a driver and vehicle to assign this order</p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Driver</label>
          <select class="form-select" id="driverSelectModal"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
            onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
            <option selected disabled>Select driver</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Driver Phone</label>
          <div class="input-group" style="height:38px;">
            <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">+353</span>
            <input type="tel" class="form-control" id="driverPhoneModal" readonly
              style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;" />
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Vehicle Number</label>
          <select class="form-select" id="vehicleSelectModal"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
            onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
            onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
            <option selected disabled>Select vehicle</option>
          </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn fw-semibold px-4"
            style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
            onmouseover="this.style.borderColor='#18181B';"
            onmouseout="this.style.borderColor='#EBEBEB';"
            data-bs-dismiss="modal">Cancel
          </button>
          <button type="button" class="btn fw-semibold px-5"
            style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
            onmouseover="this.style.background='#d96010';"
            onmouseout="this.style.background='#f37a20';"
            id="confirmAssignDriverBtn">Confirm
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="orderCreatedModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 p-4 text-center" style="border-radius:14px; max-width:400px; margin:auto; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
          style="width:60px; height:60px; background:#F0FDF4; border-radius:50%;">
          <i class="bi bi-check2-circle" style="font-size:1.8rem; color:#22C55E;"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color:#18181B;">Order Created</h5>
        <p class="mb-4" style="font-size:0.845rem; color:#71717A;">The order has been successfully created and is now live.</p>
        <button type="button" class="btn fw-semibold px-5 mx-auto"
          style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.875rem;"
          onmouseover="this.style.background='#d96010';"
          onmouseout="this.style.background='#f37a20';"
          id="goToOrdersBtn" data-bs-dismiss="modal">OK
        </button>
      </div>
    </div>
  </div>
</main>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090;">
  <div id="toastMsg" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastText"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
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
  toastEl.className = 'toast align-items-center text-white bg-danger border-0';

  let bsToast = bootstrap.Toast.getInstance(toastEl);
  if (!bsToast) {
    bsToast = new bootstrap.Toast(toastEl, {
      autohide: true,
      delay: 3000
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
            });
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
              showToast('Please select a driver');
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
              showToast('Please enter both pickup and drop-off locations before assigning a driver.');
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
              showToast('Please select a driver from the list.');
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
        const serviceType = document.getElementById('serviceType');
        const handler = () => tryCalculateRoute();
        [pickupInput, dropoffInput, rideDate, rideTime].forEach((el) => {
          if (el) el.addEventListener('change', handler);
          if (el) el.addEventListener('blur', handler);
        });
        if (serviceType) {
          serviceType.addEventListener('change', () => {
            recalculateFareForCurrentRoute();
          });
        }
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
            currentFare = calculateFare(currentDistance, currentDuration, pickupTimeStr, document.getElementById('serviceType')?.value || 'Economy');
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

      function calculateFare(distanceKm, durationMin, pickupTimeStr, rideType) {
        const pickupDate = new Date(pickupTimeStr);
        const hour = pickupDate.getHours();
        const initialFare = 3.0;
        let baseFare, ratePerKm, ratePerMinute;
        if (hour >= 8 && hour < 20) {
          baseFare = 4.4;
          ratePerKm = 1.32;
          ratePerMinute = 0.20;
        } else {
          baseFare = 5.4;
          ratePerKm = 1.81;
          ratePerMinute = 0.30;
        }
        const rawFare = initialFare + baseFare + (distanceKm * ratePerKm) + ((durationMin || 0) * ratePerMinute);
        const multipliers = {
          'Economy': 1.0,
          'Economy XL': 1.2,
          'Business': 1.0,
          'Business Plus': 1.2,
          'Limousine': 2.0,
          'Wheelchair accessible': 1.1,
          'Wheelchair Taxi': 1.1,
          'Pets Taxi': 1.15,
          'Courier / Parcel': 0.9
        };
        const multiplier = multipliers[rideType] ?? 1.0;
        return Math.round((rawFare * multiplier) * 100) / 100;
      }

      function recalculateFareForCurrentRoute() {
        if (currentDistance == null || currentDuration == null) return;
        const pickupTimeStr = buildPickupDateTime();
        const rideType = document.getElementById('serviceType')?.value || 'Economy';
        currentFare = calculateFare(currentDistance, currentDuration, pickupTimeStr, rideType);
        updateSummaryFields();
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

      // Function to set button loading state
function setButtonLoading(isLoading, customText = null) {
  const btn = document.getElementById('confirmOrderBtn');
  const originalText = btn.innerHTML;
  
  if (isLoading) {
    // Store original text if not already stored
    if (!btn.hasAttribute('data-original-text')) {
      btn.setAttribute('data-original-text', originalText);
    }
    
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'not-allowed';
    btn.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      ${customText || 'Confirming...'}
    `;
  } else {
    btn.disabled = false;
    btn.style.opacity = '';
    btn.style.cursor = '';
    const original = btn.getAttribute('data-original-text') || 'Confirm Order';
    btn.innerHTML = original;
    btn.removeAttribute('data-original-text');
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
    showToast('Please select a customer');
    return;
  }

  if (!phone) {
    showToast('Please enter customer phone');
    return;
  }

  if (!rideDateVal || !rideTimeVal) {
    showToast('Please select date and time');
    return;
  }

  if (!seats) {
    showToast('Please select seats');
    return;
  }

  if (!pickup || !dropoff) {
    showToast('Please enter pickup and drop-off locations');
    return;
  }

  if (!currentDistance || !currentDuration || !currentFare || !pickupLatLng || !dropoffLatLng) {
    showToast('Please wait for route/fare calculation to finish.');
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

          // Set loading state before API call
  setButtonLoading(true, 'Creating Order...');

        try {
          const res = await fetch('api/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });

if (res.status === 401) {
      window.location.href = '/';
      return;
    }

          const data = await res.json();
          if (data.success) {
            const modal = new bootstrap.Modal(
  document.getElementById('orderCreatedModal')
);
modal.show();

      // Remove existing event listener to prevent multiple redirects
      const goToOrdersBtn = document.getElementById('goToOrdersBtn');
      const newBtn = goToOrdersBtn.cloneNode(true);
      goToOrdersBtn.parentNode.replaceChild(newBtn, goToOrdersBtn);
      
      newBtn.addEventListener('click', () => {
        window.location.href = 'order.php';
      });

          } else {
            showToast('Error creating order: ' + (data.error || 'Unknown error'));
          }
        } catch (err) {
          console.error('Order create error', err);
          showToast('Failed to create order.');
        } finally {
    // Reset button state after API call completes (success or error)
    setButtonLoading(false);
  }
      }
    </script>
  </body>
</html>
