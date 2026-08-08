<?php
session_start();

require_once 'auth/require_login_redirect.php';
require_once __DIR__ . '/auth/config.php';
require('modules/head.php');

/**
 * Load ride types inline so the pill grid paints on first render — no
 * round-trip for the browser to wait on. Falls back silently if Supabase
 * is unreachable; the JS loader still runs as a safety net.
 */
$rideTypes = [];
try {
    $db = new SupabaseDB(null, true);
    $rows = $db->fetchData('ride_types', ['order' => 'sort_order.asc', 'limit' => 200]);
    foreach (is_array($rows) ? $rows : [] as $r) {
        if (isset($r['is_active']) && $r['is_active'] === false) continue;
        $rideTypes[] = [
            'name'       => $r['name'] ?? '',
            'image_url'  => $r['image_url'] ?? null,
            'icon_emoji' => $r['icon_emoji'] ?? null,
            'multiplier' => isset($r['multiplier']) ? floatval($r['multiplier']) : 1.0,
            'description'=> $r['description'] ?? null,
        ];
    }
} catch (Exception $e) {
    error_log('order.php: ride_types fetch failed: ' . $e->getMessage());
}

$defaultRideTypeName = !empty($rideTypes) ? $rideTypes[0]['name'] : '';
$rideTypeMultiplierMap = [];
foreach ($rideTypes as $t) {
    $rideTypeMultiplierMap[$t['name']] = $t['multiplier'];
}
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
.country-code-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #FAFAFA;
  border: 1.5px solid #EBEBEB;
  border-right: none;
  border-radius: 8px 0 0 8px;
  padding: 0 10px;
  height: 38px;
  font-size: 0.845rem;
  color: #18181B;
  cursor: pointer;
  white-space: nowrap;
}
.country-code-btn:hover { background: #F4F4F5; }
.country-code-btn img { border-radius: 2px; }
.country-code-btn .bi-chevron-down { font-size: 9px; color: #A1A1AA; margin-left: 2px; }
.country-dropdown {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1050;
  min-width: 280px;
  max-height: 300px;
  background: #fff;
  border: 1.5px solid #EBEBEB;
  border-radius: 10px;
  box-shadow: 0 12px 32px rgba(0,0,0,0.12);
  margin-top: 4px;
  overflow: hidden;
}
.country-search {
  width: 100%;
  border: none;
  border-bottom: 1px solid #F4F4F5;
  padding: 10px 12px;
  font-size: 0.83rem;
  outline: none;
  background: #FAFAFA;
}
.country-search:focus { background: #fff; }
.country-options {
  max-height: 240px;
  overflow-y: auto;
}
.country-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  font-size: 0.83rem;
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  color: #18181B;
}
.country-option:hover { background: #FFF7ED; }
.country-option.is-selected { background: #FFF7ED; font-weight: 600; }
.country-option img { border-radius: 2px; flex-shrink: 0; }
.country-option .dial { color: #71717A; margin-left: auto; font-size: 0.8rem; }
.pax-mode-toggle {
  display: inline-flex;
  background: #F4F4F5;
  border-radius: 6px;
  padding: 2px;
  gap: 1px;
}
.pax-mode-btn {
  padding: 3px 10px;
  font-size: 0.72rem;
  font-weight: 600;
  border: none;
  border-radius: 5px;
  background: transparent;
  color: #71717A;
  cursor: pointer;
  transition: all 0.15s;
  line-height: 1.4;
}
.pax-mode-btn:hover { color: #f37a20; }
.pax-mode-btn.is-active { background: #f37a20; color: #fff; box-shadow: 0 1px 2px rgba(243,122,32,0.25); }
.pax-selected-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: #F0FDF4;
  border: 1.5px solid #BBF7D0;
  border-radius: 8px;
  height: 38px;
}
.pax-selected-card .pax-name { font-weight: 600; font-size: 0.845rem; color: #16A34A; }
.pax-selected-card .pax-clear {
  margin-left: auto;
  background: none;
  border: none;
  color: #71717A;
  cursor: pointer;
  font-size: 14px;
  padding: 0 2px;
  line-height: 1;
}
.pax-selected-card .pax-clear:hover { color: #DC2626; }
</style>
  </head> 
  <body>
   
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

<main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">
  <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
    <div class="p-3 p-lg-4">

      <style>
        /* Section headers — uppercase label with subtle orange accent bar */
        .section-label {
          display: flex;
          align-items: center;
          gap: 8px;
          padding-bottom: 10px;
          margin-bottom: 4px;
          border-bottom: 1px solid #EBEBEB;
        }
        .section-label::before {
          content: '';
          display: inline-block;
          width: 3px;
          height: 13px;
          background: #f37a20;
          border-radius: 2px;
        }
        .section-label > span {
          font-weight: 700;
          font-size: 0.78rem;
          letter-spacing: 0.06em;
          text-transform: uppercase;
          color: #52525B;
        }

        /* Seat pills — compact horizontal */
        .pill-group { display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-start; }
        .pill-btn {
          display:inline-flex; align-items:center; justify-content:center; gap:6px;
          padding:7px 12px; min-height:40px;
          background:#FAFAFA; border:1.5px solid #EBEBEB; border-radius:8px;
          font-size:0.8125rem; font-weight:600; color:#52525B;
          cursor:pointer; user-select:none; white-space:nowrap;
          transition:background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        }
        .pill-btn:hover { border-color:#D4D4D8; color:#18181B; background:#fff; }
        .pill-btn.active {
          background:#FFF3E8; border-color:#f37a20; color:#f37a20;
          box-shadow:0 0 0 3px rgba(243,122,32,0.10);
        }
        .pill-btn:focus-visible { outline:2px solid #f37a20; outline-offset:2px; }
        .pill-seat { width:42px; height:42px; min-width:0; padding:0; margin:0; border-radius:50%; font-weight:700; font-size:0.9rem; }

        /* Ride-type cards — icon on top center, label below.
           auto-fit/minmax collapses empty tracks and stretches the actual
           buttons to fill the full row width instead of leaving a gap. */
        .ride-type-group {
          display: grid;
          grid-template-columns: repeat(5, minmax(0, 1fr));
          gap: 8px;
          justify-items: stretch;
        }

        .ride-type-btn {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 8px;
          padding: 14px 8px;
          background: #FAFAFA;
          border: 1.5px solid #EBEBEB;
          border-radius: 12px;
          font-size: 0.8rem;
          font-weight: 600;
          color: #52525B;
          cursor: pointer;
          user-select: none;
          text-align: center;
          line-height: 1.2;
          min-height: 100px;
          transition: background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .ride-type-btn:focus-visible { outline: 2px solid #f37a20; outline-offset: 2px; }
        .ride-type-btn.active {
          background: #FFF3E8;
          border-color: #f37a20;
          color: #f37a20;
          box-shadow: 0 0 0 3px rgba(243,122,32,0.10);
        }
        .ride-type-icon {
          width: 52px;
          height: 48px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          background: linear-gradient(180deg,#FFFFFF 0%,#F4F4F5 100%);
          border: 1.5px solid #E4E4E7;
          border-radius: 12px;
          font-size: 22px;
          line-height: 1;
          color: #52525B;
          padding: 2px;
          box-shadow: inset 0 -1px 0 rgba(0,0,0,0.03);
          transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .ride-type-icon img {
          width: 44px;
          height: 40px;
          object-fit: contain;
          filter: drop-shadow(0 1px 1px rgba(0,0,0,0.10)) contrast(1.05);
        }
        .ride-type-icon .ride-type-emoji,
        .ride-type-icon .ride-type-emoji-fallback {
          font-size: 26px;
          line-height: 1;
        }
        .ride-type-btn.active .ride-type-icon {
          background: linear-gradient(180deg,#FFF7ED 0%,#FFE9D2 100%);
          border-color: #FED7AA;
          color: #f37a20;
        }
        .ride-type-label { display: block; font-weight: 600; }

        /* Date / Time inputs with leading icon */
        .dt-input-wrap {
          position: relative;
          display: flex;
          align-items: center;
          height: 40px;
          background: #FAFAFA;
          border: 1.5px solid #E4E4E7;
          border-radius: 8px;
          padding: 0 10px 0 36px;
          transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .dt-input-wrap:focus-within {
          border-color: #f37a20;
          background: #fff;
          box-shadow: 0 0 0 3px rgba(243,122,32,0.12);
        }
        .dt-input-wrap .dt-icon {
          position: absolute;
          left: 12px;
          top: 50%;
          transform: translateY(-50%);
          color: #f37a20;
          font-size: 14px;
          pointer-events: none;
        }
        .dt-input-wrap .dt-input {
          border: none;
          background: transparent;
          box-shadow: none;
          padding: 0;
          height: 36px;
          font-size: 0.845rem;
          color: #18181B;
          font-weight: 500;
          width: 100%;
        }
        .dt-input-wrap .dt-input:focus { box-shadow: none; outline: none; background: transparent; }
        .dt-input-wrap .dt-input::-webkit-calendar-picker-indicator {
          opacity: 0.55;
          cursor: pointer;
          filter: grayscale(0.3);
        }
        .dt-input-wrap .dt-input::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        /* Google Places autocomplete dropdown */
        .pac-container {
          margin-top: 6px;
          border: 1.5px solid #E4E4E7 !important;
          border-radius: 10px !important;
          box-shadow: 0 12px 32px -8px rgba(24,24,27,0.18), 0 4px 10px rgba(24,24,27,0.06) !important;
          background: #fff;
          font-family: 'Inter', system-ui, sans-serif;
          padding: 6px;
          overflow: hidden;
        }
        .pac-container:after { display: none !important; } /* hide the "powered by Google" footer image padding */
        .pac-item {
          display: flex;
          align-items: center;
          padding: 7px 10px;
          border: none;
          border-radius: 8px;
          font-size: 0.74rem;
          color: #52525B;
          line-height: 1.3;
          cursor: pointer;
          transition: background .12s ease, color .12s ease;
        }
        .pac-item + .pac-item { margin-top: 2px; }
        .pac-item:hover,
        .pac-item-selected,
        .pac-item-selected:hover {
          background: #FFF3E8;
          color: #18181B;
        }
        .pac-icon { display: none !important; }
        .pac-item-query {
          font-size: 0.78rem;
          font-weight: 500;
          color: #18181B;
          padding-right: 4px;
        }
        .pac-matched { color: #f37a20; font-weight: 500; }

        /* ── Compact multi-column order form ──────────────────────────────── */
        .oc-card {
          background: #fff;
          border: 1.5px solid #EBEBEB;
          border-radius: 12px;
          padding: 12px 14px 14px;
        }
        .oc-card + .oc-card { margin-top: 10px; }
        .oc-map-wrap {
          border: 1.5px solid #EBEBEB;
          border-radius: 12px;
          overflow: hidden;
          height: 260px;
        }
        .oc-map-wrap #map { width: 100%; height: 100%; border: 0; }
        @media (min-width: 1200px) {
          .oc-map-wrap { height: 380px; }
        }
        .oc-driver-actions { display: flex; flex-direction: column; gap: 8px; }
        .oc-driver-actions .btn { width: 100%; justify-content: center; }

        /* Extras / Payment — toggle chips instead of checkbox rows */
        .oc-chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .oc-chip {
          display: inline-flex; align-items: center; gap: 7px;
          padding: 8px 14px; border-radius: 999px;
          border: 1.5px solid #EBEBEB; background: #FAFAFA;
          font-size: 0.8125rem; font-weight: 500; color: #52525B;
          cursor: pointer; user-select: none; white-space: nowrap;
          transition: background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .oc-chip input { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
        .oc-chip i { font-size: 14px; color: #A1A1AA; transition: color .15s ease; }
        .oc-chip:hover { border-color: #D4D4D8; background: #fff; }
        .oc-chip:has(input:checked) {
          background: #FFF3E8; border-color: #f37a20; color: #f37a20; font-weight: 600;
          box-shadow: 0 0 0 3px rgba(243,122,32,0.10);
        }
        .oc-chip:has(input:checked) i { color: #f37a20; }
        .oc-chip-lg { flex: 1 1 0; justify-content: center; padding: 10px 16px; font-size: 0.845rem; }
        .oc-chip-lg i { font-size: 16px; }
      </style>

      <div class="row g-3">
        <div class="col-12 col-xl-7">

          <div class="oc-card">
            <div class="section-label"><span>Passenger Details</span></div>

            <input type="hidden" id="customerId" />
            <input type="hidden" id="passengerMode" value="existing" />

            <div class="row g-2 mt-1">
              <!-- EXISTING mode: search + select from DB -->
              <div class="col-md-6 position-relative" id="paxExistingPane">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <label class="form-label fw-semibold mb-0" style="font-size:0.8125rem; color:#18181B;">Passenger Name</label>
                  <div class="pax-mode-toggle">
                    <button type="button" class="pax-mode-btn is-active" id="paxModeExisting" onclick="switchPaxMode('existing')">Existing</button>
                    <button type="button" class="pax-mode-btn" id="paxModeCustom" onclick="switchPaxMode('custom')">New</button>
                  </div>
                </div>
                <div id="paxSearchWrapper">
                  <input type="text" class="form-control" placeholder="Type name to search..."
                    id="customerNameInput" autocomplete="off"
                    style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
                    onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                    onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                  <div id="customerSuggestions" class="list-group position-absolute w-100"
                    style="z-index:10; max-height:200px; overflow-y:auto; display:none; border:1.5px solid #EBEBEB; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.10); top:100%; margin-top:4px;"></div>
                </div>
                <div id="paxSelectedCard" class="pax-selected-card" style="display:none;">
                  <i class="bi bi-person-check-fill" style="color:#16A34A; font-size:16px;"></i>
                  <span class="pax-name" id="paxSelectedName"></span>
                  <button type="button" class="pax-clear" title="Change passenger" onclick="clearSelectedPassenger()">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
              </div>

              <!-- CUSTOM mode: just type a name -->
              <div class="col-md-6" id="paxCustomPane" style="display:none;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <label class="form-label fw-semibold mb-0" style="font-size:0.8125rem; color:#18181B;">Passenger Name</label>
                  <div class="pax-mode-toggle">
                    <button type="button" class="pax-mode-btn" id="paxModeExisting2" onclick="switchPaxMode('existing')">Existing</button>
                    <button type="button" class="pax-mode-btn is-active" id="paxModeCustom2" onclick="switchPaxMode('custom')">New</button>
                  </div>
                </div>
                <input type="text" class="form-control" placeholder="Enter passenger name"
                  id="customPassengerName" autocomplete="off"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Phone Number</label>
                <div class="input-group" style="height:38px;">
                  <div style="position:relative;" id="countryCodeWrapper">
                    <button type="button" class="country-code-btn" id="countryCodeBtn">
                      <img src="https://flagcdn.com/w20/ie.png" width="20" height="15" alt="IE" id="selectedFlag">
                      <span id="selectedDialCode">+353</span>
                      <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="country-dropdown" id="countryDropdown">
                      <input type="text" class="country-search" id="countrySearch" placeholder="Search country...">
                      <div class="country-options" id="countryOptions"></div>
                    </div>
                  </div>
                  <input type="tel" class="form-control" id="customerPhone"
                    style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;"
                    onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                    onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                </div>
              </div>

              <div class="col-md-6">
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

              <div class="col-md-6">
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

              <div class="col-12">
                <div class="row g-2">
                  <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Est. Fare</label>
                    <input type="text" class="form-control" id="estimatedFare" readonly
                      style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B; font-weight:600;" />
                  </div>
                  <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Distance (km)</label>
                    <input type="text" class="form-control" id="distanceKm" readonly
                      style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B;" />
                  </div>
                  <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Time (min)</label>
                    <input type="text" class="form-control" id="travelTime" readonly
                      style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA; color:#18181B;" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="oc-card">
            <div class="section-label"><span>Ride Details</span></div>

            <div class="row g-2 mt-1">
              <div class="col-12">
                <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Service Type</label>
                <input type="hidden" id="serviceType" value="<?php echo htmlspecialchars($defaultRideTypeName, ENT_QUOTES); ?>" />
                <div class="ride-type-group" data-pill-target="serviceType" id="rideTypePillGroup">
                  <?php if (empty($rideTypes)): ?>
                    <div class="text-muted" style="font-size:0.78rem; grid-column: 1 / -1;">Loading ride types…</div>
                  <?php else: ?>
                    <?php foreach ($rideTypes as $idx => $t): ?>
                      <?php
                        $isActive   = $idx === 0;
                        $name       = htmlspecialchars($t['name'], ENT_QUOTES);
                        $desc       = htmlspecialchars($t['description'] ?? $t['name'], ENT_QUOTES);
                        $hasImage   = !empty($t['image_url']);
                        $imageUrl   = htmlspecialchars($t['image_url'] ?? '', ENT_QUOTES);
                        $emoji      = htmlspecialchars($t['icon_emoji'] ?? '', ENT_QUOTES, 'UTF-8');
                      ?>
                      <button type="button" class="ride-type-btn<?php echo $isActive ? ' active' : ''; ?>" data-value="<?php echo $name; ?>" title="<?php echo $desc; ?>">
                        <span class="ride-type-icon">
                          <?php if ($hasImage): ?>
                            <img src="<?php echo $imageUrl; ?>" alt=""
                                 onerror="this.style.display='none'; const s=this.nextElementSibling; if(s) s.style.display='inline-flex';" />
                            <?php if ($emoji !== ''): ?>
                              <span class="ride-type-emoji-fallback" style="display:none; align-items:center; justify-content:center;"><?php echo $emoji; ?></span>
                            <?php endif; ?>
                          <?php elseif ($emoji !== ''): ?>
                            <span class="ride-type-emoji"><?php echo $emoji; ?></span>
                          <?php else: ?>
                            <i class="bi bi-car-front"></i>
                          <?php endif; ?>
                        </span>
                        <span class="ride-type-label"><?php echo $name; ?></span>
                      </button>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Seats</label>
                <input type="hidden" id="seatCount" value="" />
                <div class="pill-group" data-pill-target="seatCount">
                  <button type="button" class="pill-btn pill-seat" data-value="4">4</button>
                  <button type="button" class="pill-btn pill-seat" data-value="5">5</button>
                  <button type="button" class="pill-btn pill-seat" data-value="6">6</button>
                  <button type="button" class="pill-btn pill-seat" data-value="7">7</button>
                  <button type="button" class="pill-btn pill-seat" data-value="8">8</button>
                </div>
              </div>

              <!-- Schedule mode toggle -->
              <div class="col-md-12">
                <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Ride Timing</label>
                <div class="d-flex gap-2">
                  <button type="button" id="modeNowBtn"
                    class="btn fw-semibold px-4 flex-fill"
                    style="height:36px; border-radius:8px; font-size:0.845rem; background:#f37a20; color:#fff; border:1.5px solid #f37a20; transition:all 0.15s;"
                    onclick="setRideMode('now')">
                    <i class="bi bi-lightning-fill me-1"></i> Book Now
                  </button>
                  <button type="button" id="modeScheduleBtn"
                    class="btn fw-semibold px-4 flex-fill"
                    style="height:36px; border-radius:8px; font-size:0.845rem; background:#fff; color:#52525B; border:1.5px solid #EBEBEB; transition:all 0.15s;"
                    onclick="setRideMode('schedule')">
                    <i class="bi bi-calendar-event me-1"></i> Schedule for Later
                  </button>
                </div>
                <input type="hidden" id="rideScheduleMode" value="now" />
              </div>

              <div id="scheduleDateTimeRow" class="col-12" style="display:none;">
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Date</label>
                    <div class="dt-input-wrap">
                      <i class="bi bi-calendar3 dt-icon"></i>
                      <input type="date" class="form-control dt-input" id="rideDate" min="<?php echo date('Y-m-d'); ?>" />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Time</label>
                    <div class="dt-input-wrap">
                      <i class="bi bi-clock dt-icon"></i>
                      <input type="time" class="form-control dt-input" id="rideTime" />
                    </div>
                  </div>
                </div>
                <div class="mt-2 px-1" style="font-size:0.775rem; color:#71717A;">
                  <i class="bi bi-info-circle me-1"></i>
                  Rides scheduled <strong>40+ minutes</strong> in the future will be saved as <em>Scheduled</em> and auto-activated when the time approaches.
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="col-12 col-xl-5">
          <div class="oc-map-wrap">
            <div id="map"></div>
          </div>

          <div class="oc-card mt-2">
            <span class="fw-bold d-block mb-1" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Special Cost</span>
            <p class="mb-2" style="font-size:0.75rem; color:#A1A1AA;">Leave blank to use default fare calculation</p>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Cost</label>
                <div class="input-group" style="height:38px;">
                  <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">EUR</span>
                  <input type="number" step="0.01" min="0" class="form-control" id="specialCost" placeholder="Override fare"
                    style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                    onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                    onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
                </div>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Km Included</label>
                <div class="input-group" style="height:38px;">
                  <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">km</span>
                  <input type="number" step="0.01" min="0" class="form-control" id="specialKm" placeholder="Override distance"
                    style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                    onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                    onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
                </div>
              </div>
            </div>
            <div>
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Minutes Included</label>
              <div class="input-group" style="height:38px;">
                <span class="input-group-text" style="background:#FAFAFA; border:1.5px solid #EBEBEB; border-right:none; border-radius:8px 0 0 8px; font-size:0.8rem; color:#71717A; height:38px;">min</span>
                <input type="number" step="1" min="0" class="form-control" id="specialMinutes" placeholder="Override duration"
                  style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none';" />
              </div>
            </div>
          </div>

          <div class="oc-card mt-2">
            <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Driver Assignment</span>
            <div class="d-flex gap-2 flex-wrap">
              <button class="btn d-flex align-items-center gap-2 fw-semibold px-3 flex-fill justify-content-center"
                style="height:36px; font-size:0.8125rem; color:#f37a20; border:1.5px solid #f37a20; background:#fff; border-radius:8px;"
                onmouseover="this.style.background='#FFF3E8';"
                onmouseout="this.style.background='#fff';"
                data-bs-toggle="modal" data-bs-target="#assignDriverModal">
                <i class="bi bi-person-check" style="font-size:14px;"></i> Assign Manually
              </button>
              <button type="button" class="btn d-flex align-items-center gap-2 fw-semibold px-3 flex-fill justify-content-center"
                style="height:36px; font-size:0.8125rem; color:#f37a20; border:1.5px solid #f37a20; background:#fff; border-radius:8px;"
                onmouseover="this.style.background='#FFF3E8';"
                onmouseout="this.style.background='#fff';"
                id="assignNearestDriverOpenBtn">
                <i class="bi bi-geo" style="font-size:14px;"></i> Assign Nearest
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">

        <div class="col-12 col-md-6">
          <div class="oc-card h-100">
            <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Extras</span>
            <div class="oc-chip-group">
              <label class="oc-chip">
                <input type="checkbox" id="creditCard" />
                <i class="bi bi-credit-card"></i> Accept Credit Card
              </label>
              <label class="oc-chip">
                <input type="checkbox" id="personWithDisabilities" />
                <i class="bi bi-universal-access"></i> Person With Disabilities
              </label>
              <label class="oc-chip">
                <input type="checkbox" id="childSeat" />
                <i class="bi bi-emoji-smile"></i> Child Seat
              </label>
              <label class="oc-chip">
                <input type="checkbox" id="extraLuggage" />
                <i class="bi bi-suitcase"></i> Extra Luggage Space
              </label>
              <label class="oc-chip">
                <input type="checkbox" id="petsAllowed" />
                <i class="bi bi-heart"></i> Pets Allowed
              </label>
              <label class="oc-chip">
                <input type="checkbox" id="delivery" />
                <i class="bi bi-box-seam"></i> Delivery
              </label>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="oc-card h-100">
            <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Payment</span>
            <div class="oc-chip-group">
              <label class="oc-chip oc-chip-lg">
                <input type="radio" name="paymentMethod" id="paymentCash" value="cash" checked />
                <i class="bi bi-cash-stack"></i> Cash
              </label>
              <label class="oc-chip oc-chip-lg">
                <input type="radio" name="paymentMethod" id="paymentStripe" value="stripe" />
                <i class="bi bi-credit-card-2-front"></i> Pay with Stripe
              </label>
            </div>
            <a id="stripePayLink" href="https://buy.stripe.com/14A5kDeAx6fFec5fMjfQI05" target="_blank" rel="noopener noreferrer"
               class="d-none align-items-center gap-2 text-decoration-none fw-semibold mt-2"
               style="background:#635BFF; color:#fff; font-size:0.8125rem; padding:8px 14px; border-radius:8px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              Pay with Stripe
            </a>
          </div>
        </div>

      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:1px solid #EBEBEB;">
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
          <button type="button" id="clearFieldsYesBtn" class="btn fw-semibold px-4"
            style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.845rem;"
            onmouseover="this.style.background='#d96010';"
            onmouseout="this.style.background='#f37a20';">Yes, clear it
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
        <div class="mb-3 position-relative">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Driver</label>
          <input type="text" id="driverSearchModal" class="form-control" placeholder="Type to search driver..."
            autocomplete="off"
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; color:#18181B; background:#FAFAFA;"
            onfocus="this.style.borderColor='#f37a20'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)'; showOrderDriverDropdown();"
            onblur="this.style.borderColor='#EBEBEB'; this.style.boxShadow='none'; setTimeout(hideOrderDriverDropdown,200);" />
          <input type="hidden" id="driverSelectModal" value="" />
          <div id="orderDriverDropdownList" class="list-group position-absolute w-100"
            style="z-index:100; max-height:200px; overflow-y:auto; display:none; border:1px solid #E4E4E7; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.10); background:#fff; margin-top:2px;">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Driver Phone</label>
          <div class="input-group" style="height:38px;">
            <div style="position:relative;" id="driverCountryCodeWrapper">
              <button type="button" class="country-code-btn" id="driverCountryCodeBtn">
                <img src="https://flagcdn.com/w20/ie.png" width="20" height="15" alt="IE" id="driverSelectedFlag">
                <span id="driverSelectedDialCode">+353</span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <div class="country-dropdown" id="driverCountryDropdown">
                <input type="text" class="country-search" id="driverCountrySearch" placeholder="Search country...">
                <div class="country-options" id="driverCountryOptions"></div>
              </div>
            </div>
            <input type="tel" class="form-control" id="driverPhoneModal" readonly
              style="border:1.5px solid #EBEBEB; border-left:none; border-radius:0 8px 8px 0; font-size:0.845rem; height:38px; background:#FAFAFA;" />
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Vehicle Number</label>
          <input type="text" class="form-control" id="vehicleSelectModal" readonly
            style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
            placeholder="Auto-filled on driver selection" />
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
      // Bootstrapped from PHP so calculateFare has multipliers on first paint — no fetch needed.
      let rideTypeMultipliers = <?php echo json_encode((object)$rideTypeMultiplierMap, JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="js/order.js"></script>
  </body>
</html>
