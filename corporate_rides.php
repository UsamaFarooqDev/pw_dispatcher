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

  <main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

    <?php @require('modules/bodyHeader.php'); ?>

    <!-- Header Actions -->
    <div class="d-flex align-items-center justify-content-between mt-2 mb-4 flex-wrap gap-2">
      <div>
        <h5 class="fw-bold m-0" style="font-size:1rem; color:#18181B; letter-spacing:-0.01em;">Corporate Rides</h5>
        <div style="font-size:0.78rem; color:#A1A1AA; margin-top:2px;">Manage corporate accounts and ride bookings</div>
      </div>
      <button class="btn d-flex align-items-center gap-2 fw-semibold px-4"
        style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.85rem; box-shadow:0 4px 14px rgba(243,122,32,0.35);"
        onmouseover="this.style.background='#d96010';"
        onmouseout="this.style.background='#f37a20';"
        data-bs-toggle="modal" data-bs-target="#newCorporateModal">
        <i class="bi bi-building-add" style="font-size:15px;"></i>
        New Corporate Account
      </button>
    </div>

    <!-- Corporate Rides Table -->
    <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
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


  <!-- ══════════════════════════════════════════════
       NEW CORPORATE ACCOUNT MODAL
  ══════════════════════════════════════════════ -->
  <div class="modal fade" id="newCorporateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0" style="border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.15);">

        <!-- Modal Header -->
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

        <!-- Modal Body -->
        <div class="modal-body p-4" style="overflow-y:auto;">

          <!-- Section: Company Info -->
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

          <!-- Section: Contact Person -->
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

          <!-- Section: Billing -->
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

          <!-- Section: Notes -->
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

          <!-- Section: Account Access -->
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

        <!-- Modal Footer -->
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


  <!-- Table row styles -->
  <style>
    #corporateRidesBody tr { border-bottom:1px solid #F4F4F5; transition:background 0.12s; }
    #corporateRidesBody tr:hover { background:#FAFAFA; }
    #corporateRidesBody td { padding:14px 24px; font-size:0.845rem; color:#18181B; vertical-align:middle; border:none; }
  </style>

  <!-- Fetch corporate rides from Supabase -->
  <script src="assets/js/config.js"></script>
  <script>
    const CORP_PAGE_SIZE = 15;
    let corpCurrentPage = 1;

    async function loadCorporateRides(page = 1) {
      const tbody = document.getElementById('corporateRidesBody');
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5" style="border:none; color:#A1A1AA; font-size:0.845rem;">
        <i class="bi bi-arrow-repeat d-block mb-2" style="font-size:1.4rem; color:#EBEBEB;"></i>Loading…</td></tr>`;

      const from = (page - 1) * CORP_PAGE_SIZE;
      const to   = from + CORP_PAGE_SIZE - 1;

      try {
        const res = await fetch(
          `${SUPABASE_URL}/rest/v1/corporate_rides?select=*&order=created_at.desc`,
          {
            headers: {
              'apikey': SUPABASE_ANON_KEY,
              'Authorization': `Bearer ${SUPABASE_ANON_KEY}`,
              'Range': `${from}-${to}`,
              'Prefer': 'count=exact'
            }
          }
        );

        const total = (() => {
          const cr = res.headers.get('content-range');
          return cr ? parseInt(cr.split('/')[1]) : 0;
        })();

        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5" style="border:none;">
            <i class="bi bi-building d-block mb-2" style="font-size:1.5rem; color:#EBEBEB;"></i>
            <span style="font-size:0.845rem; color:#A1A1AA;">No corporate rides found</span>
          </td></tr>`;
          document.getElementById('corporateRidesPagination').innerHTML = '';
          return;
        }

        tbody.innerHTML = data.map(ride => {
          const statusConfig = {
            completed:  { bg:'#F0FDF4', color:'#16A34A', label:'Completed' },
            assigned:   { bg:'#EFF6FF', color:'#2563EB', label:'Assigned'  },
            searching:  { bg:'#FFF3E8', color:'#f37a20', label:'Searching' },
            upcoming:   { bg:'#F5F3FF', color:'#7C3AED', label:'Upcoming'  },
            cancelled:  { bg:'#FFF1F2', color:'#E11D48', label:'Cancelled' },
            on_trip:    { bg:'#FFF7ED', color:'#EA580C', label:'On Trip'   },
          };
          const s = statusConfig[ride.status] || { bg:'#F4F4F5', color:'#71717A', label: ride.status ?? '—' };
          const date = ride.created_at ? new Date(ride.created_at).toLocaleDateString('en-IE', { day:'2-digit', month:'short', year:'numeric' }) : '—';

          return `<tr>
            <td style="color:#71717A; font-size:0.8rem;">${date}</td>
            <td class="fw-semibold">${ride.company_name ?? '—'}</td>
            <td>${ride.employee_name ?? '—'}</td>
            <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${ride.pickup ?? ''}">${ride.pickup ?? '—'}</td>
            <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${ride.destination ?? ''}">${ride.destination ?? '—'}</td>
            <td>${ride.payment_method ?? '—'}</td>
            <td class="fw-semibold">${ride.fare ? '€' + Number(ride.fare).toFixed(2) : '—'}</td>
            <td>
              <span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:${s.bg}; color:${s.color}; white-space:nowrap;">
                ${s.label}
              </span>
            </td>
          </tr>`;
        }).join('');

        renderCorporatePagination(total, page);

      } catch (err) {
        console.error('Corporate rides fetch error:', err);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4" style="border:none; color:#E11D48; font-size:0.845rem;">
          Failed to load rides. Please refresh.</td></tr>`;
      }
    }

    function renderCorporatePagination(total, current) {
      const pages = Math.ceil(total / CORP_PAGE_SIZE);
      const el = document.getElementById('corporateRidesPagination');
      if (pages <= 1) { el.innerHTML = ''; return; }

      let html = `<div class="d-flex align-items-center gap-2 flex-wrap">
        <span style="font-size:0.78rem; color:#A1A1AA;">${total} records</span>
        <div class="d-flex gap-1 ms-auto">`;

      html += `<button onclick="corpGoTo(${current - 1})" ${current===1?'disabled':''} class="btn px-2"
        style="height:30px; border:1.5px solid #EBEBEB; border-radius:6px; font-size:0.78rem; background:#fff; color:#52525B;">
        <i class="bi bi-chevron-left"></i></button>`;

      for (let p = 1; p <= pages; p++) {
        if (p === 1 || p === pages || (p >= current - 1 && p <= current + 1)) {
          html += `<button onclick="corpGoTo(${p})" class="btn px-3"
            style="height:30px; border:1.5px solid ${p===current?'#f37a20':'#EBEBEB'}; border-radius:6px; font-size:0.78rem;
            background:${p===current?'#f37a20':'#fff'}; color:${p===current?'#fff':'#52525B'}; font-weight:${p===current?'700':'400'};">
            ${p}</button>`;
        } else if (p === current - 2 || p === current + 2) {
          html += `<span style="line-height:30px; color:#A1A1AA; font-size:0.78rem;">…</span>`;
        }
      }

      html += `<button onclick="corpGoTo(${current + 1})" ${current===pages?'disabled':''} class="btn px-2"
        style="height:30px; border:1.5px solid #EBEBEB; border-radius:6px; font-size:0.78rem; background:#fff; color:#52525B;">
        <i class="bi bi-chevron-right"></i></button>`;

      html += `</div></div>`;
      el.innerHTML = html;
    }

    function corpGoTo(page) {
      corpCurrentPage = page;
      loadCorporateRides(page);
    }

    document.addEventListener('DOMContentLoaded', () => loadCorporateRides(1));
  </script>

</body>
</html>