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

  <main class="main-content p-4" style="background:#F4F4F5; min-height:92vh;">

  <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 py-1">
  <div class="d-flex align-items-center gap-2">
      <button class="btn d-flex align-items-center gap-2 fw-semibold px-4"
  style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.85rem; box-shadow:0 4px 14px rgba(243,122,32,0.35);"
  onmouseover="this.style.background='#d96010';"
  onmouseout="this.style.background='#f37a20';"
  id="openCorporateModalBtn">
  <i class="bi bi-building-add" style="font-size:15px;"></i>
  New Corporate Account
</button>
  </div>

  <div class="position-relative" style="width:100%; max-width:280px;">
    <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:13px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
    <input
      type="text"
      id="globalSearchInput"
      placeholder="Find rides, assign orders…"
      class="form-control w-100"
      style="height:38px; border-radius:8px; border:1.5px solid #18181b; padding-left:34px; font-size:0.8375rem; color:#18181B; background:#FAFAFA; outline:none;"
      onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
      onblur="this.style.borderColor='#18181b'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"
    />
  </div>
</div>

    <div class="rounded-3 mt-4 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
      <div class="table-responsive" style="min-height:400px;">
        <table class="table mb-0" style="border-collapse:collapse;">
          <thead>
            <tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Company</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Employee</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Date</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
            </tr>
          </thead>
          <tbody id="corporateRidesBody">
            <tr>
              <td colspan="8" class="text-center py-5" style="border:none;">
                <div style="font-size:1.5rem; color:#EBEBEB; margin-bottom:8px;"><i class="bi bi-building"></i></div>
                <div style="font-size:0.845rem; color:#A1A1AA;">Loading corporate rides…</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-4 py-3" style="border-top:1px solid #EBEBEB;">
        <div id="corporateRidesPagination"></div>
      </div>
    </div>

  </main>

  <div class="modal fade" id="newCorporateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0" style="border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

        <div class="px-4 py-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid #EBEBEB; flex-shrink:0;">
          <div>
            <h5 class="fw-bold mb-0" style="font-size:0.9375rem; color:#18181B;">New Corporate Account</h5>
            <p class="mb-0 mt-1" style="font-size:0.78rem; color:#A1A1AA;">Fill in the details to create a corporate account</p>
          </div>
          <button type="button" class="btn d-flex align-items-center justify-content-center p-0" data-bs-dismiss="modal"
            style="width:30px; height:30px; border-radius:7px; border:1.5px solid #EBEBEB; background:#fff; color:#71717A;"
            onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20'; this.style.borderColor='#f37a20';"
            onmouseout="this.style.background='#fff'; this.style.color='#71717A'; this.style.borderColor='#EBEBEB';">
            <i class="bi bi-x-lg" style="font-size:12px;"></i>
          </button>
        </div>

        <div class="modal-body p-4" style="overflow-y:auto;">

          <div class="mb-2 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Company Information</span>
          </div>

          <div class="row g-3 mt-1 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Company Name <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_company_name" placeholder="e.g. Acme Ireland Ltd"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Tax Number <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_tax_number" placeholder="e.g. IE1234567T"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Office Address <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_office_address" placeholder="Full office address"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
          </div>

          <div class="mb-2 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Appointed Contact</span>
          </div>

          <div class="row g-3 mt-1 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Appointed Person <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_appointed_person" placeholder="Full name"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Designation <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_designation" placeholder="e.g. Finance Manager"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Email Address <span style="color:#E11D48;">*</span></label>
              <div class="position-relative">
                <i class="bi bi-envelope position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
                <input type="email" class="form-control" id="corp_email" placeholder="contact@company.ie"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; padding-left:32px;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Phone Number <span style="color:#E11D48;">*</span></label>
              <div class="input-group" style="height:38px;">
                <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.845rem; color:#71717A; height:38px;">+353</span>
                <input type="tel" class="form-control" id="corp_phone" placeholder="Phone number"
                  style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
              </div>
            </div>
          </div>

          <div class="mb-2 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Billing & Invoicing</span>
          </div>

          <div class="row g-3 mt-1 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Billing IBAN <span style="color:#E11D48;">*</span></label>
              <input type="text" class="form-control" id="corp_iban" placeholder="IE00 AIBK 1234 5678 9012 34"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; letter-spacing:0.03em;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Payment Cycle <span style="color:#E11D48;">*</span></label>
              <select class="form-select" id="corp_payment_cycle"
                style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B;"
                onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';">
                <option value="" disabled selected>Select cycle</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Bi-Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Invoice Email <span style="color:#E11D48;">*</span></label>
              <div class="position-relative">
                <i class="bi bi-envelope-at position-absolute top-50 translate-middle-y" style="left:11px; font-size:12px; color:#A1A1AA; pointer-events:none;"></i>
                <input type="email" class="form-control" id="corp_invoice_email" placeholder="invoices@company.ie"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; padding-left:32px;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
              </div>
            </div>
          </div>

          <div class="mb-2 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Notes</span>
          </div>

          <div class="row g-3 mt-1 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Special Notes
                <span style="font-size:0.72rem; color:#A1A1AA; font-weight:400;">(Company)</span>
              </label>
              <textarea class="form-control" id="corp_notes_company" rows="3" placeholder="Any notes from the company…"
                style="border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; resize:none;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Special Notes
                <span style="font-size:0.72rem; color:#A1A1AA; font-weight:400;">(PowerCabs)</span>
              </label>
              <textarea class="form-control" id="corp_notes_powercabs" rows="3" placeholder="Internal notes for PowerCabs…"
                style="border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; resize:none;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"></textarea>
            </div>
          </div>

          <div class="mb-2 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.775rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Account Access</span>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Password <span style="color:#E11D48;">*</span></label>
              <div class="position-relative">
                <input type="password" class="form-control" id="corp_password" placeholder="Set account password"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; padding-right:40px;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                <button type="button"
                  class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                  style="width:28px; height:28px; background:transparent; border:none; z-index:10;"
                  onclick="const f=document.getElementById('corp_password'); const i=document.getElementById('corpPassIcon'); if(f.type==='password'){f.type='text'; i.className='bi bi-eye';}else{f.type='password'; i.className='bi bi-eye-slash';}">
                  <i class="bi bi-eye-slash" id="corpPassIcon" style="font-size:14px; color:#A1A1AA;"></i>
                </button>
              </div>
              <div style="font-size:0.72rem; color:#A1A1AA; margin-top:5px;">This password will be used by the corporate to access their panel.</div>
            </div>
          </div>

        </div>

        <div class="px-4 py-3 d-flex justify-content-between align-items-center" style="border-top:1px solid #EBEBEB; flex-shrink:0;">
          <div style="font-size:0.75rem; color:#A1A1AA;">
            <span style="color:#E11D48;">*</span> Required fields
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn fw-semibold px-4"
              style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem;"
              onmouseover="this.style.borderColor='#18181B';"
              onmouseout="this.style.borderColor='#EBEBEB';"
              data-bs-dismiss="modal">Cancel
            </button>
            <button type="button" class="btn fw-semibold d-flex align-items-center gap-2 px-4" id="createCorporateBtn"
              style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem; box-shadow:0 4px 14px rgba(243,122,32,0.30);"
              onmouseover="this.style.background='#d96010';"
              onmouseout="this.style.background='#f37a20';">
              <i class="bi bi-building-check" style="font-size:14px;"></i>
              Create Account
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>


  <style>
    #corporateRidesBody tr { border-bottom:1px solid #F4F4F5; transition:background 0.12s; }
    #corporateRidesBody tr:hover { background:#FAFAFA; }
    #corporateRidesBody td { padding:14px 24px; font-size:0.845rem; color:#18181B; vertical-align:middle; border:none; }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/pagination.js"></script>
<script>

  document.addEventListener('DOMContentLoaded', () => {

    const modalEl = document.getElementById('newCorporateModal');
    const openBtn = document.getElementById('openCorporateModalBtn');
    let bsModal   = null;

    // Init Bootstrap modal manually — fixes "not opening" issue
    if (modalEl && typeof bootstrap !== 'undefined') {
      bsModal = new bootstrap.Modal(modalEl);
    } else if (modalEl) {
      // Fallback: wait for bootstrap to load
      window.addEventListener('load', () => {
        bsModal = new bootstrap.Modal(modalEl);
      });
    }

    openBtn?.addEventListener('click', () => {
      if (bsModal) bsModal.show();
      else console.error('Bootstrap Modal not available — check Bootstrap JS is loaded');
    });

    // ── FORM VALIDATION HELPERS ───────────────────────
    function getVal(id) {
      return document.getElementById(id)?.value?.trim() ?? '';
    }

    function setFieldError(id, message) {
      const el = document.getElementById(id);
      if (!el) return;
      el.style.borderColor = '#E11D48';
      el.style.boxShadow   = '0 0 0 3px rgba(225,29,72,0.10)';
      let err = el.parentElement.querySelector('.field-error');
      if (!err) {
        err = document.createElement('div');
        err.className = 'field-error';
        err.style.cssText = 'font-size:0.72rem; color:#E11D48; margin-top:4px;';
        el.parentElement.appendChild(err);
      }
      err.textContent = message;
    }

    function clearFieldErrors() {
      document.querySelectorAll('.field-error').forEach(e => e.remove());
      document.querySelectorAll('#newCorporateModal input, #newCorporateModal select, #newCorporateModal textarea').forEach(el => {
        el.style.borderColor  = '#EBEBEB';
        el.style.boxShadow    = 'none';
        el.style.background   = '#FAFAFA';
      });
    }

    function validateForm() {
      clearFieldErrors();
      let valid = true;

      const required = {
        corp_company_name:    'Company name is required',
        corp_tax_number:      'Tax number is required',
        corp_office_address:  'Office address is required',
        corp_appointed_person:'Appointed person is required',
        corp_designation:     'Designation is required',
        corp_email:           'Email address is required',
        corp_phone:           'Phone number is required',
        corp_iban:            'Billing IBAN is required',
        corp_payment_cycle:   'Payment cycle is required',
        corp_invoice_email:   'Invoice email is required',
        corp_password:        'Password is required'
      };

      for (const [id, msg] of Object.entries(required)) {
        if (!getVal(id)) { setFieldError(id, msg); valid = false; }
      }

      // Email format
      const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (getVal('corp_email') && !emailRe.test(getVal('corp_email'))) {
        setFieldError('corp_email', 'Enter a valid email address');
        valid = false;
      }
      if (getVal('corp_invoice_email') && !emailRe.test(getVal('corp_invoice_email'))) {
        setFieldError('corp_invoice_email', 'Enter a valid invoice email');
        valid = false;
      }

      // Password length
      if (getVal('corp_password') && getVal('corp_password').length < 8) {
        setFieldError('corp_password', 'Password must be at least 8 characters');
        valid = false;
      }

      return valid;
    }

    // ── CREATE CORPORATE SUBMIT ───────────────────────
    document.getElementById('createCorporateBtn')?.addEventListener('click', async () => {
      if (!validateForm()) return;

      const btn     = document.getElementById('createCorporateBtn');
      const origHTML = btn.innerHTML;
      btn.disabled  = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" style="width:14px;height:14px;border-width:2px;" role="status"></span>Creating…`;

      const payload = {
        company_name:      getVal('corp_company_name'),
        tax_number:        getVal('corp_tax_number'),
        office_address:    getVal('corp_office_address'),
        appointed_person:  getVal('corp_appointed_person'),
        designation:       getVal('corp_designation'),
        email:             getVal('corp_email'),
        phone:             getVal('corp_phone'),
        billing_iban:              getVal('corp_iban'),
        payment_cycle:     getVal('corp_payment_cycle'),
        invoice_email:     getVal('corp_invoice_email'),
        special_notes_company:     getVal('corp_notes_company'),
        special_notes_powercabs:   getVal('corp_notes_powercabs'),
        password:          getVal('corp_password')
      };

      try {
        const res  = await fetch('api/create_corporate.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify(payload)
        });

        const json = await res.json();

        if (json.success) {
          bsModal.hide();
          clearFieldErrors();
          document.querySelectorAll('#newCorporateModal input, #newCorporateModal select, #newCorporateModal textarea')
            .forEach(el => el.value = '');
          showToast('Corporate account created successfully', '#22C55E');
          loadCorporateRides(1); // refresh table
        } else {
          showToast(json.error ?? 'Something went wrong', '#E11D48');
          // If it's a field-specific error, highlight the field
          if (json.error?.toLowerCase().includes('email')) {
            setFieldError('corp_email', json.error);
          }
        }

      } catch (err) {
        console.error('Create corporate error:', err);
        showToast('Network error — please try again', '#E11D48');
      } finally {
        btn.disabled  = false;
        btn.innerHTML = origHTML;
      }
    });

    // ── TOAST HELPER ─────────────────────────────────
    function showToast(message, bgColor = '#18181B') {
      let toast = document.getElementById('corpToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'corpToast';
        toast.style.cssText = `
          position:fixed; bottom:24px; right:24px; z-index:9999;
          background:${bgColor}; color:#fff; font-size:0.845rem; font-weight:500;
          padding:12px 20px; border-radius:10px;
          box-shadow:0 8px 24px rgba(0,0,0,0.18);
          display:flex; align-items:center; gap:10px;
          transform:translateY(20px); opacity:0;
          transition:all 0.25s ease;
        `;
        document.body.appendChild(toast);
      }
      toast.style.background = bgColor;
      toast.innerHTML = `
        <i class="bi ${bgColor === '#22C55E' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}"></i>
        ${message}
      `;
      requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity   = '1';
      });
      setTimeout(() => {
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity   = '0';
      }, 4000);
    }

    // ── LOAD CORPORATE RIDES TABLE ────────────────────
    const CORP_PAGE_SIZE = 10;
    let currentPage = 1;
    let currentSearch = '';
    const corporatePagination = (typeof PaginationManager !== 'undefined')
      ? new PaginationManager({
          containerId: 'corporateRidesPagination',
          page: 1,
          limit: CORP_PAGE_SIZE,
          total: 0,
          maxVisiblePages: 7,
          onPageChange: (page) => loadCorporateRides(page)
        })
      : null;

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    async function loadCorporateRides(page = 1) {
      currentPage = page;
      const tbody = document.getElementById('corporateRidesBody');
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5" style="border:none; color:#A1A1AA; font-size:0.845rem;">
        <i class="bi bi-arrow-repeat d-block mb-2" style="font-size:1.4rem; color:#EBEBEB;"></i>Loading…</td></tr>`;
      
      try {
        const params = new URLSearchParams({
          page: String(page),
          limit: String(CORP_PAGE_SIZE),
          search: currentSearch
        });
        const res = await fetch(`api/get_corporate_rides.php?${params.toString()}`);
        const data = await res.json();

        if (!res.ok || !data.success) {
          throw new Error(data.error || 'Failed to load records');
        }

        const rows = Array.isArray(data.data) ? data.data : [];
        const total = Number(data.pagination?.total ?? 0);

        if (rows.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5" style="border:none;">
            <i class="bi bi-building d-block mb-2" style="font-size:1.5rem; color:#EBEBEB;"></i>
            <span style="font-size:0.845rem; color:#A1A1AA;">No corporate rides found${currentSearch ? ' for this search' : ''}</span>
          </td></tr>`;
          if (corporatePagination) {
            corporatePagination.update(0, 1);
          } else {
            document.getElementById('corporateRidesPagination').innerHTML = '';
          }
          return;
        }

        const statusConfig = {
          completed: { bg:'#F0FDF4', color:'#16A34A', label:'Completed' },
          assigned:  { bg:'#EFF6FF', color:'#2563EB', label:'Assigned'  },
          searching: { bg:'#FFF3E8', color:'#f37a20', label:'Searching' },
          upcoming:  { bg:'#F5F3FF', color:'#7C3AED', label:'Upcoming'  },
          cancelled: { bg:'#FFF1F2', color:'#E11D48', label:'Cancelled' },
          on_trip:   { bg:'#FFF7ED', color:'#EA580C', label:'On Trip'   },
        };

        tbody.innerHTML = rows.map(ride => {
          const normalizedStatusKey = String(ride.status ?? '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_');
          const s = statusConfig[normalizedStatusKey] ?? { bg:'#F4F4F5', color:'#71717A', label: ride.status ?? '—' };
          const dateSource = ride.pickupTime ?? ride.date ?? ride.created_at ?? null;
          const date = dateSource
            ? new Date(dateSource).toLocaleDateString('en-IE', { day:'2-digit', month:'short', year:'numeric' })
            : '—';
          const company = ride.company_name ?? ride.company ?? '—';
          const employee = ride.employee_name ?? ride.employee ?? ride.passenger_name ?? '—';
          const pickup = ride.pickup ?? ride.pickup_location ?? '—';
          const destination = ride.destination ?? ride.dropoff ?? ride.destination_location ?? '—';
          const payment = ride.payment_method ?? ride.payment_source ?? ride.payment ?? '—';
          const fare = ride.fare ?? ride.price ?? null;

          return `<tr>
            <td>${escapeHtml(company)}</td>
            <td>${escapeHtml(employee)}</td>
            <td style="color:#71717A; font-size:0.8rem; white-space:nowrap;">${escapeHtml(date)}</td>
            <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(pickup)}">${escapeHtml(pickup)}</td>
            <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(destination)}">${escapeHtml(destination)}</td>
            <td>${escapeHtml(payment)}</td>
            <td>${fare ? '€' + Number(fare).toFixed(2) : '—'}</td>
            <td>
              <span class="rounded-pill px-2 py-1 fw-semibold"
                style="font-size:0.72rem; background:${s.bg}; color:${s.color}; white-space:nowrap;">
                ${escapeHtml(s.label)}
              </span>
            </td>
          </tr>`;
        }).join('');

        if (corporatePagination) {
          corporatePagination.update(total, page);
        } else {
          renderPagination(total, page);
        }

      } catch (err) {
        console.error('Corporate rides error:', err);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4" style="border:none; color:#E11D48; font-size:0.845rem;">
          Failed to load — please refresh.</td></tr>`;
      }
    }

    function renderPagination(total, current) {
      const pages = Math.ceil(total / CORP_PAGE_SIZE);
      const el    = document.getElementById('corporateRidesPagination');
      if (pages <= 1) { el.innerHTML = ''; return; }

      let html = `<div class="d-flex align-items-center gap-2 flex-wrap">
        <span style="font-size:0.78rem; color:#A1A1AA;">${total} records</span>
        <div class="d-flex gap-1 ms-auto">`;

      html += `<button onclick="corpGoTo(${current-1})" ${current===1?'disabled':''} class="btn px-2"
        style="height:30px; border:1.5px solid #EBEBEB; border-radius:6px; font-size:0.78rem; background:#fff;">
        <i class="bi bi-chevron-left"></i></button>`;

      for (let p = 1; p <= pages; p++) {
        if (p===1 || p===pages || (p>=current-1 && p<=current+1)) {
          html += `<button onclick="corpGoTo(${p})" class="btn px-3"
            style="height:30px; border:1.5px solid ${p===current?'#f37a20':'#EBEBEB'}; border-radius:6px;
            font-size:0.78rem; background:${p===current?'#f37a20':'#fff'};
            color:${p===current?'#fff':'#52525B'}; font-weight:${p===current?700:400};">${p}</button>`;
        } else if (p===current-2 || p===current+2) {
          html += `<span style="line-height:30px; color:#A1A1AA; font-size:0.78rem;">…</span>`;
        }
      }

      html += `<button onclick="corpGoTo(${current+1})" ${current===pages?'disabled':''} class="btn px-2"
        style="height:30px; border:1.5px solid #EBEBEB; border-radius:6px; font-size:0.78rem; background:#fff;">
        <i class="bi bi-chevron-right"></i></button>`;

      html += `</div></div>`;
      el.innerHTML = html;
    }

    // Expose fallback pagination function if shared component is unavailable
    window.corpGoTo = (page) => loadCorporateRides(page);

    let searchDebounce = null;
    document.getElementById('globalSearchInput')?.addEventListener('input', (event) => {
      currentSearch = event.target.value.trim();
      if (searchDebounce) clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => loadCorporateRides(1), 250);
    });

    // Initial load
    loadCorporateRides(1);

  }); // end DOMContentLoaded
</script>

</body>
</html>