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
    <div class="p-4">

      <div class="section-label"><span>Passenger Details</span></div>

      <input type="hidden" id="customerId" />
      <input type="hidden" id="passengerMode" value="existing" />

      <div class="row g-3 mt-1 mb-3">
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
      </div>

      <div class="section-label mt-4">
        <span>Ride Details</span>
      </div>

      <div class="row g-3 mt-1 mb-4">
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

        <div class="col-md-4">
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
        <div class="col-12 mt-2">
          <label class="form-label fw-semibold mb-2" style="font-size:0.8125rem; color:#18181B;">Ride Timing</label>
          <div class="d-flex gap-2">
            <button type="button" id="modeNowBtn"
              class="btn fw-semibold px-4"
              style="height:36px; border-radius:8px; font-size:0.845rem; background:#f37a20; color:#fff; border:1.5px solid #f37a20; transition:all 0.15s;"
              onclick="setRideMode('now')">
              <i class="bi bi-lightning-fill me-1"></i> Book Now
            </button>
            <button type="button" id="modeScheduleBtn"
              class="btn fw-semibold px-4"
              style="height:36px; border-radius:8px; font-size:0.845rem; background:#fff; color:#52525B; border:1.5px solid #EBEBEB; transition:all 0.15s;"
              onclick="setRideMode('schedule')">
              <i class="bi bi-calendar-event me-1"></i> Schedule for Later
            </button>
          </div>
          <input type="hidden" id="rideScheduleMode" value="now" />
        </div>

        <div id="scheduleDateTimeRow" class="col-12" style="display:none;">
          <div class="row g-3">
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
        .pill-group { display:flex; flex-wrap:wrap; gap:6px; }
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
        .pill-seat { min-width:46px; font-weight:700; font-size:0.9rem; padding:7px 10px; }

        /* Ride-type cards — icon on top center, label below */
        .ride-type-group {
          display: grid;
          grid-template-columns: repeat(7, minmax(0, 1fr));
          gap: 8px;
        }
        @media (max-width: 1399.98px) { .ride-type-group { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 767.98px)  { .ride-type-group { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 479.98px)  { .ride-type-group { grid-template-columns: repeat(2, 1fr); } }

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
          min-height: 112px;
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
          width: 60px;
          height: 56px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          background: linear-gradient(180deg,#FFFFFF 0%,#F4F4F5 100%);
          border: 1.5px solid #E4E4E7;
          border-radius: 12px;
          font-size: 24px;
          line-height: 1;
          color: #52525B;
          padding: 2px;
          box-shadow: inset 0 -1px 0 rgba(0,0,0,0.03);
          transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .ride-type-icon img {
          width: 52px;
          height: 48px;
          object-fit: contain;
          filter: drop-shadow(0 1px 1px rgba(0,0,0,0.10)) contrast(1.05);
        }
        .ride-type-icon .ride-type-emoji,
        .ride-type-icon .ride-type-emoji-fallback {
          font-size: 30px;
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
      </style>

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
              <span class="fw-bold d-block mb-2" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Payment</span>
              <div class="d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                  <input class="form-check-input m-0 flex-shrink-0" type="radio" name="paymentMethod" id="paymentCash" value="cash" checked style="accent-color:#f37a20; width:15px; height:15px;" />
                  <label class="form-check-label" for="paymentCash" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Cash</label>
                </div>
                <div class="d-flex align-items-center gap-2 rounded-2 px-2 py-1" style="background:#fff; border:1.5px solid #EBEBEB;">
                  <input class="form-check-input m-0 flex-shrink-0" type="radio" name="paymentMethod" id="paymentStripe" value="stripe" style="accent-color:#f37a20; width:15px; height:15px;" />
                  <label class="form-check-label" for="paymentStripe" style="font-size:0.8rem; color:#52525B; cursor:pointer;">Pay with Stripe</label>
                </div>
              </div>
              <a id="stripePayLink" href="https://buy.stripe.com/14A5kDeAx6fFec5fMjfQI05" target="_blank" rel="noopener noreferrer"
                 class="d-none align-items-center gap-2 text-decoration-none fw-semibold mt-2"
                 style="background:#635BFF; color:#fff; font-size:0.8125rem; padding:8px 14px; border-radius:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                Pay with Stripe
              </a>
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
      // Raw Google-Directions values; specials override these when set
      let googleDistance = null;
      let googleDuration = null;
      let googleFare = null;
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
  toastEl.className = `toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0`;

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
        setupPillGroups();
        setupSpecialCostListeners();
        loadRideTypes();
      });

      /* ---------------------- Pill button groups (service type, seats) ---------------------- */
      function wirePillGroup(group) {
        if (!group) return;
        const targetId = group.dataset.pillTarget;
        const hidden = targetId ? document.getElementById(targetId) : null;
        group.querySelectorAll('button[data-value]').forEach((btn) => {
          btn.addEventListener('click', () => {
            group.querySelectorAll('button[data-value]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            if (hidden) {
              hidden.value = btn.dataset.value ?? '';
              hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }
          });
        });
      }

      function setupPillGroups() {
        document.querySelectorAll('[data-pill-target]').forEach(wirePillGroup);
      }

      /* ---------------------- Ride types (loaded from ride_types table) ---------------------- */
      // Bootstrapped from PHP so calculateFare has multipliers on first paint — no fetch needed.
      let rideTypeMultipliers = <?php echo json_encode((object)$rideTypeMultiplierMap, JSON_UNESCAPED_SLASHES); ?>;

      function escapeAttr(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }
      function escapeText(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }

      async function loadRideTypes() {
        // Skip the fetch if the server already rendered the pills (the common case).
        const group = document.getElementById('rideTypePillGroup');
        if (!group) return;
        if (group.querySelector('button[data-value]')) return;

        try {
          const res = await fetch('api/get_ride_types.php');
          if (res.status === 401) { window.location.href = '/'; return; }
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const result = await res.json();
          if (!result || !result.success || !Array.isArray(result.data) || result.data.length === 0) {
            group.innerHTML = '<div class="text-muted" style="font-size:0.78rem; grid-column: 1 / -1;">No ride types available.</div>';
            return;
          }
          renderRideTypes(result.data);
        } catch (err) {
          console.error('Failed to load ride types:', err);
          group.innerHTML = '<div class="text-danger" style="font-size:0.78rem; grid-column: 1 / -1;">Failed to load ride types. Refresh to retry.</div>';
        }
      }

      function renderRideTypes(types) {
        const group = document.getElementById('rideTypePillGroup');
        if (!group) return;

        // Keep the multiplier lookup in sync with whatever the DB says.
        rideTypeMultipliers = {};
        types.forEach((t) => {
          rideTypeMultipliers[t.name] = Number(t.multiplier) || 1.0;
        });

        // Preserve an already-selected value (e.g. if user somehow landed after an earlier pick),
        // otherwise default to the first ride type.
        const hidden = document.getElementById('serviceType');
        const currentValue = hidden?.value || '';
        const defaultName = types.find((t) => t.name === currentValue) ? currentValue : types[0].name;
        if (hidden) hidden.value = defaultName;

        group.innerHTML = types.map((t) => {
          const isActive = t.name === defaultName;
          const icon = t.image_url
            ? `<img src="${escapeAttr(t.image_url)}" alt="" style="width:26px; height:26px; object-fit:contain;" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='inline');">`
              + (t.icon_emoji ? `<span style="display:none; font-size:20px; line-height:1;">${escapeText(t.icon_emoji)}</span>` : '')
            : (t.icon_emoji
                ? `<span style="font-size:22px; line-height:1;">${escapeText(t.icon_emoji)}</span>`
                : `<i class="bi bi-car-front"></i>`);

          return `<button type="button" class="ride-type-btn${isActive ? ' active' : ''}" data-value="${escapeAttr(t.name)}" title="${escapeAttr(t.description || t.name)}">
            <span class="ride-type-icon">${icon}</span>
            <span class="ride-type-label">${escapeText(t.name)}</span>
          </button>`;
        }).join('');

        wirePillGroup(group);

        // If fare was already computed before types loaded, re-run with the DB multiplier
        if (googleDistance != null && googleDuration != null) {
          recalculateFareForCurrentRoute();
        }
      }

      /* ---------------------- Customers ---------------------- */
      async function fetchPassengers() {
        try {
          const res = await fetch('api/get_passengers.php');
          const data = await res.json();
          if (data.success && data.data) {
            passengers = data.data;
            if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
          }
        } catch (err) {
          console.error('Error fetching passengers', err);
        }
      }

      // ── Passenger mode switching ──
      function switchPaxMode(mode) {
        document.getElementById('passengerMode').value = mode;
        document.getElementById('paxModeExisting').classList.toggle('is-active', mode === 'existing');
        document.getElementById('paxModeCustom').classList.toggle('is-active', mode === 'custom');
        const e2 = document.getElementById('paxModeExisting2');
        const c2 = document.getElementById('paxModeCustom2');
        if (e2) e2.classList.toggle('is-active', mode === 'existing');
        if (c2) c2.classList.toggle('is-active', mode === 'custom');
        document.getElementById('paxExistingPane').style.display = mode === 'existing' ? '' : 'none';
        document.getElementById('paxCustomPane').style.display = mode === 'custom' ? '' : 'none';

        // Reset state when switching
        selectedPassengerId = null;
        document.getElementById('customerId').value = '';
        document.getElementById('customerNameInput').value = '';
        document.getElementById('customPassengerName').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerSuggestions').style.display = 'none';
        document.getElementById('paxSelectedCard').style.display = 'none';
        document.getElementById('paxSearchWrapper').style.display = '';
        if (typeof selectCountry === 'function') selectCountry('ie');
      }

      function clearSelectedPassenger() {
        selectedPassengerId = null;
        document.getElementById('customerId').value = '';
        document.getElementById('customerNameInput').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('paxSelectedCard').style.display = 'none';
        document.getElementById('paxSearchWrapper').style.display = '';
        if (typeof selectCountry === 'function') selectCountry('ie');
        document.getElementById('customerNameInput').focus();
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
            const displayName = p.name || p.full_name || 'Unknown';
            const displayPhone = p.phone || p.phone_number || '';
            const div = document.createElement('button');
            div.type = 'button';
            div.className = 'list-group-item list-group-item-action';
            div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 12px;';
            div.innerHTML = '<span style="font-weight:600; font-size:0.84rem; color:#18181B;">' + displayName + '</span>' +
              (displayPhone ? '<span style="font-size:0.78rem; color:#71717A;">' + displayPhone + '</span>' : '');
            div.addEventListener('click', () => {
              nameInput.value = displayName;
              setPhoneFromFullNumber(displayPhone);
              customerIdInput.value = p.id || '';
              selectedPassengerId = p.id || null;
              suggestions.style.display = 'none';
              // Show selected card, hide search input
              document.getElementById('paxSelectedName').textContent = displayName;
              document.getElementById('paxSelectedCard').style.display = 'flex';
              document.getElementById('paxSearchWrapper').style.display = 'none';
            });
            suggestions.appendChild(div);
          });
          suggestions.style.display = 'block';
        };

        let debounceTimer = null;
        nameInput.addEventListener('input', (e) => {
          clearTimeout(debounceTimer);
          selectedPassengerId = null;
          customerIdInput.value = '';
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
          const res = await fetch('api/get_drivers.php?status=approved&limit=500');
          const data = await res.json();
          if (data.success && data.data) {
            drivers = data.data;
            renderOrderDriverDropdown(drivers);
          }
        } catch (err) {
          console.error('Error fetching drivers', err);
        }
      }

      function renderOrderDriverDropdown(list) {
        const container = document.getElementById('orderDriverDropdownList');
        if (!container) return;
        container.innerHTML = '';
        if (!list || list.length === 0) {
          container.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">No approved drivers found</div>';
          return;
        }
        list.forEach(d => {
          const name = d.full_name || d.name || 'Driver';
          const vehicle = d.vehicle_make || d.vehicle_number || '';
          const phone = d.phone || '';
          const initials = name.trim().split(/\s+/).map(p=>p[0]).slice(0,2).join('').toUpperCase();
          const item = document.createElement('button');
          item.type = 'button';
          item.className = 'list-group-item list-group-item-action border-0 px-3 py-2';
          item.style.cssText = 'font-size:0.8125rem; cursor:pointer; border-bottom:1px solid #F4F4F5 !important;';
          item.innerHTML = `
            <div class="d-flex align-items-center gap-2">
              <div style="width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:9px; flex-shrink:0;">${initials}</div>
              <div style="min-width:0;">
                <div class="fw-semibold text-truncate" style="color:#18181B; font-size:0.8125rem;">${name}</div>
                <div class="text-truncate" style="font-size:0.72rem; color:#71717A;">${vehicle}${phone ? ' &middot; ' + phone : ''}</div>
              </div>
            </div>
          `;
          item.addEventListener('mousedown', (e) => { e.preventDefault(); selectOrderDriver(d); });
          container.appendChild(item);
        });
      }

      function selectOrderDriver(driver) {
        const searchInput = document.getElementById('driverSearchModal');
        const hiddenInput = document.getElementById('driverSelectModal');
        const phoneInput = document.getElementById('driverPhoneModal');
        const vehicleInput = document.getElementById('vehicleSelectModal');
        const name = driver.full_name || driver.name || 'Driver';

        if (searchInput) searchInput.value = `${name} — ${driver.vehicle_make || ''}`;
        if (hiddenInput) hiddenInput.value = driver.id;

        selectedDriverId = driver.id;
        setDriverPhoneFromFullNumber(driver.phone || '');
        if (vehicleInput) vehicleInput.value = driver.vehicle_number || '';
        selectedVehicleNumber = driver.vehicle_number || null;
        hideOrderDriverDropdown();
      }

      function setDriverPhoneFromFullNumber(fullPhone) {
        const phoneInput = document.getElementById('driverPhoneModal');
        if (!fullPhone) { if (phoneInput) phoneInput.value = ''; return; }
        const sorted = [...COUNTRY_LIST].sort((a, b) => b.dial.length - a.dial.length);
        for (const c of sorted) {
          if (fullPhone.startsWith(c.dial)) {
            selectDriverCountry(c.iso);
            if (phoneInput) phoneInput.value = fullPhone.slice(c.dial.length);
            return;
          }
        }
        if (phoneInput) phoneInput.value = fullPhone.replace(/^\+/, '');
      }

      function selectDriverCountry(iso) {
        const c = COUNTRY_LIST.find(x => x.iso === iso);
        if (!c) return;
        document.getElementById('driverSelectedFlag').src = 'https://flagcdn.com/w20/' + c.iso + '.png';
        document.getElementById('driverSelectedFlag').alt = c.iso.toUpperCase();
        document.getElementById('driverSelectedDialCode').textContent = c.dial;
        document.getElementById('driverCountryDropdown').style.display = 'none';
        document.querySelectorAll('#driverCountryOptions .country-option').forEach(el => {
          el.classList.toggle('is-selected', el.dataset.iso === iso);
        });
      }

      function showOrderDriverDropdown() {
        const el = document.getElementById('orderDriverDropdownList');
        if (el) el.style.display = 'block';
      }
      function hideOrderDriverDropdown() {
        const el = document.getElementById('orderDriverDropdownList');
        if (el) el.style.display = 'none';
      }

      function setupDriverModal() {
        const searchInput = document.getElementById('driverSearchModal');
        const confirmBtn = document.getElementById('confirmAssignDriverBtn');

        if (searchInput) {
          searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const hiddenInput = document.getElementById('driverSelectModal');
            if (hiddenInput) hiddenInput.value = '';
            selectedDriverId = null;
            if (!term) {
              renderOrderDriverDropdown(drivers);
            } else {
              const filtered = drivers.filter(d => {
                const n = (d.full_name || d.name || '').toLowerCase();
                const v = (d.vehicle_make || d.vehicle_number || '').toLowerCase();
                const p = (d.phone || '').toLowerCase();
                return n.includes(term) || v.includes(term) || p.includes(term);
              });
              renderOrderDriverDropdown(filtered);
            }
            showOrderDriverDropdown();
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
            googleDistance = leg.distance.value / 1000;
            googleDuration = Math.round(leg.duration.value / 60);
            const pickupTimeStr = buildPickupDateTime();
            googleFare = calculateFare(googleDistance, googleDuration, pickupTimeStr, document.getElementById('serviceType')?.value || 'Economy');
            pickupLatLng = leg.start_location;
            dropoffLatLng = leg.end_location;
            applyFareOverrides();
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
        // Prefer DB-backed multipliers (loaded via loadRideTypes from ride_types table).
        // Fall back to 1.0 when the ride type hasn't been registered in the table.
        const multiplier = (rideTypeMultipliers && rideTypeMultipliers[rideType] != null)
          ? rideTypeMultipliers[rideType]
          : 1.0;
        return Math.round((rawFare * multiplier) * 100) / 100;
      }

      function recalculateFareForCurrentRoute() {
        if (googleDistance == null || googleDuration == null) return;
        const pickupTimeStr = buildPickupDateTime();
        const rideType = document.getElementById('serviceType')?.value || 'Economy';
        googleFare = calculateFare(googleDistance, googleDuration, pickupTimeStr, rideType);
        applyFareOverrides();
      }

      /**
       * Merge Google-derived values with Special Cost overrides and push the
       * effective values to the read-only summary fields + saved payload.
       * If a special input is non-empty, it overrides the corresponding
       * Google value. Empty → fall back to Google.
       */
      function applyFareOverrides() {
        const rawCost = document.getElementById('specialCost')?.value;
        const rawKm   = document.getElementById('specialKm')?.value;
        const rawMin  = document.getElementById('specialMinutes')?.value;

        const spCost = rawCost !== '' && rawCost != null ? parseFloat(rawCost) : NaN;
        const spKm   = rawKm   !== '' && rawKm   != null ? parseFloat(rawKm)   : NaN;
        const spMin  = rawMin  !== '' && rawMin  != null ? parseFloat(rawMin)  : NaN;

        currentFare     = !isNaN(spCost) ? spCost : googleFare;
        currentDistance = !isNaN(spKm)   ? spKm   : googleDistance;
        currentDuration = !isNaN(spMin)  ? spMin  : googleDuration;
        updateSummaryFields();
      }

      function setupSpecialCostListeners() {
        ['specialCost', 'specialKm', 'specialMinutes'].forEach((id) => {
          const el = document.getElementById(id);
          if (el) el.addEventListener('input', applyFareOverrides);
        });
      }

      function updateSummaryFields() {
        const fareEl = document.getElementById('estimatedFare');
        const distEl = document.getElementById('distanceKm');
        const timeEl = document.getElementById('travelTime');
        if (fareEl && currentFare != null) fareEl.value = `€${Number(currentFare).toFixed(2)}`;
        if (distEl && currentDistance != null) distEl.value = Number(currentDistance).toFixed(2);
        if (timeEl && currentDuration != null) timeEl.value = Math.round(Number(currentDuration)).toString();
      }

      /* ---------------------- Schedule Mode ---------------------- */
      function setRideMode(mode) {
        document.getElementById('rideScheduleMode').value = mode;
        const row = document.getElementById('scheduleDateTimeRow');
        const nowBtn = document.getElementById('modeNowBtn');
        const schBtn = document.getElementById('modeScheduleBtn');

        if (mode === 'now') {
          row.style.display = 'none';
          nowBtn.style.background = '#f37a20';
          nowBtn.style.color = '#fff';
          nowBtn.style.borderColor = '#f37a20';
          schBtn.style.background = '#fff';
          schBtn.style.color = '#52525B';
          schBtn.style.borderColor = '#EBEBEB';
          // Clear date/time so buildPickupDateTime falls back to current time
          const d = document.getElementById('rideDate');
          const t = document.getElementById('rideTime');
          if (d) d.value = '';
          if (t) t.value = '';
        } else {
          row.style.display = '';
          schBtn.style.background = '#f37a20';
          schBtn.style.color = '#fff';
          schBtn.style.borderColor = '#f37a20';
          nowBtn.style.background = '#fff';
          nowBtn.style.color = '#52525B';
          nowBtn.style.borderColor = '#EBEBEB';
          // Pre-fill with tomorrow's date and a round hour as a sensible default
          const d = document.getElementById('rideDate');
          const t = document.getElementById('rideTime');
          if (d && !d.value) {
            const tmr = new Date(); tmr.setDate(tmr.getDate() + 1);
            d.value = tmr.toISOString().slice(0, 10);
          }
          if (t && !t.value) {
            const hr = new Date(); hr.setHours(hr.getHours() + 2, 0, 0, 0);
            t.value = hr.toTimeString().slice(0, 5);
          }
        }
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
  const paxMode = document.getElementById('passengerMode')?.value || 'existing';
  const passengerId = paxMode === 'existing' ? selectedPassengerId : null;
  const customerName = paxMode === 'existing'
    ? (document.getElementById('customerNameInput')?.value?.trim() || '')
    : (document.getElementById('customPassengerName')?.value?.trim() || '');
  const phoneRaw = document.getElementById('customerPhone')?.value?.trim() || '';
  const serviceType = document.getElementById('serviceType')?.value || 'Economy';
  const seats = document.getElementById('seatCount')?.value || '';
  const pickup = document.getElementById('pickupInput')?.value?.trim() || '';
  const dropoff = document.getElementById('dropoffInput')?.value?.trim() || '';
  const scheduleMode = document.getElementById('rideScheduleMode')?.value || 'now';
  const isScheduled = scheduleMode === 'schedule';

  // For Book Now always use the current moment; for Schedule use the picker values.
  let rideDateVal, rideTimeVal;
  if (isScheduled) {
    rideDateVal = document.getElementById('rideDate')?.value || '';
    rideTimeVal = document.getElementById('rideTime')?.value || '';
  } else {
    const now = new Date();
    rideDateVal = now.toISOString().slice(0, 10);          // 'YYYY-MM-DD'
    rideTimeVal = now.toTimeString().slice(0, 5);          // 'HH:MM'
  }
  const pickupTimeStr = buildPickupDateTime();

  const dialCode = document.getElementById('selectedDialCode')?.textContent || '+353';
  const phone = phoneRaw
    ? phoneRaw.startsWith('+')
      ? phoneRaw
      : dialCode + phoneRaw.replace(/^0+/, '')
    : '';

  if (!customerName) {
    showToast(paxMode === 'existing' ? 'Please search and select a passenger' : 'Please enter passenger name');
    return;
  }

  if (paxMode === 'existing' && !passengerId) {
    showToast('Please select a passenger from the search results');
    return;
  }

  if (!phone) {
    showToast('Please enter customer phone');
    return;
  }

  if (isScheduled && (!rideDateVal || !rideTimeVal)) {
    showToast('Please select a date and time for the scheduled ride');
    return;
  }

  if (isScheduled) {
    const scheduledDt = new Date(rideDateVal + 'T' + rideTimeVal);
    if (scheduledDt <= new Date()) {
      showToast('Scheduled time must be in the future');
      return;
    }
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

        // Payment method: Cash → 'cash', Pay with Stripe → 'prepaid'
        const paymentChoice = document.querySelector('input[name="paymentMethod"]:checked');
        const paymentMethod = (paymentChoice && paymentChoice.value === 'stripe') ? 'prepaid' : 'cash';

        const isCustomPax = paxMode === 'custom';
        const finalName = isCustomPax ? customerName + ' (Powercabs Dispatch)' : customerName;

        const payload = {
          user_id: passengerId || null,
          customer_name: finalName,
          phone_number: phone,
          passenger_name: finalName,
          passenger_phone: phone,
          skip_passenger_lookup: isCustomPax,
          source_override: isCustomPax ? 'Powercabs Dispatch' : null,
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
          payment_method: paymentMethod,
          service_type_display: serviceType,
          scheduled_at: pickupTimeStr,
          is_scheduled: isScheduled,
          tz_offset_min: new Date().getTimezoneOffset(),
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

      document.addEventListener('DOMContentLoaded', function () {
        const yesBtn = document.getElementById('clearFieldsYesBtn');
        if (yesBtn) {
          yesBtn.addEventListener('click', function (ev) {
            ev.preventDefault();
            try { window.clearAllFields(); }
            catch (e) { console.error('clearAllFields failed:', e); }
          });
        } else {
          console.warn('clearFieldsYesBtn not found in DOM');
        }

        // Show the Stripe checkout link only when "Pay with Stripe" is selected.
        const stripeLink = document.getElementById('stripePayLink');
        const syncStripeLink = () => {
          const stripeSelected = document.getElementById('paymentStripe')?.checked;
          if (!stripeLink) return;
          stripeLink.classList.toggle('d-none', !stripeSelected);
          stripeLink.classList.toggle('d-inline-flex', !!stripeSelected);
        };
        document.querySelectorAll('input[name="paymentMethod"]').forEach((r) => {
          r.addEventListener('change', syncStripeLink);
        });
        syncStripeLink();
      });

      window.clearAllFields = function clearAllFields() {
        // Reset passenger mode to Existing
        if (typeof switchPaxMode === 'function') switchPaxMode('existing');

        const textIds = [
          'customerNameInput', 'customPassengerName', 'customerPhone', 'customerId',
          'pickupInput', 'dropoffInput',
          'estimatedFare', 'distanceKm', 'travelTime',
          'rideDate', 'rideTime',
          'specialCost', 'specialKm', 'specialMinutes'
        ];
        textIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        // Reset cached route/fare state so a new order starts clean
        currentDistance = currentDuration = currentFare = null;
        googleDistance = googleDuration = googleFare = null;

        // Reset country code selector to Ireland
        if (typeof selectCountry === 'function') selectCountry('ie');

        // Reset pill groups: Service Type → Economy, Seats → none
        const serviceTypeInput = document.getElementById('serviceType');
        if (serviceTypeInput) serviceTypeInput.value = 'Economy';
        const seatCountInput = document.getElementById('seatCount');
        if (seatCountInput) seatCountInput.value = '';
        document.querySelectorAll('[data-pill-target]').forEach((group) => {
          const targetId = group.dataset.pillTarget;
          const hidden = targetId ? document.getElementById(targetId) : null;
          const wantValue = hidden ? hidden.value : '';
          group.querySelectorAll('button[data-value]').forEach((b) => {
            b.classList.toggle('active', !!wantValue && b.dataset.value === wantValue);
          });
        });

        const checkboxIds = [
          'creditCard', 'personWithDisabilities', 'childSeat',
          'extraLuggage', 'petsAllowed', 'delivery'
        ];
        checkboxIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.checked = false;
        });

        // Reset payment method back to Cash and hide the Stripe link
        const cashRadio = document.getElementById('paymentCash');
        if (cashRadio) cashRadio.checked = true;
        const stripeLink = document.getElementById('stripePayLink');
        if (stripeLink) {
          stripeLink.classList.add('d-none');
          stripeLink.classList.remove('d-inline-flex');
        }

        const suggestions = document.getElementById('customerSuggestions');
        if (suggestions) {
          suggestions.innerHTML = '';
          suggestions.style.display = 'none';
        }

        try {
          if (typeof directionsRenderer !== 'undefined' && directionsRenderer) {
            directionsRenderer.set('directions', null);
          }
        } catch (e) { /* ignore map cleanup errors */ }

        try {
          if (typeof currentDistance !== 'undefined') currentDistance = null;
          if (typeof currentDuration !== 'undefined') currentDuration = null;
          if (typeof currentFare !== 'undefined') currentFare = null;
        } catch (e) { /* vars may not exist */ }

        const modalEl = document.getElementById('clearFieldsModal');
        if (modalEl && window.bootstrap) {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }

        if (typeof showToast === 'function') {
          showToast('Form cleared', true);
        }

        const firstField = document.getElementById('customerNameInput');
        if (firstField) firstField.focus();
      };

      // ── Country-code selector ──
      const COUNTRY_LIST = [
        { iso: 'ie', name: 'Ireland',        dial: '+353' },
        { iso: 'gb', name: 'United Kingdom', dial: '+44'  },
        { iso: 'us', name: 'United States',  dial: '+1'   },
        { iso: 'pl', name: 'Poland',         dial: '+48'  },
        { iso: 'ro', name: 'Romania',        dial: '+40'  },
        { iso: 'lt', name: 'Lithuania',       dial: '+370' },
        { iso: 'lv', name: 'Latvia',         dial: '+371' },
        { iso: 'ee', name: 'Estonia',        dial: '+372' },
        { iso: 'de', name: 'Germany',        dial: '+49'  },
        { iso: 'fr', name: 'France',         dial: '+33'  },
        { iso: 'es', name: 'Spain',          dial: '+34'  },
        { iso: 'it', name: 'Italy',          dial: '+39'  },
        { iso: 'pt', name: 'Portugal',       dial: '+351' },
        { iso: 'nl', name: 'Netherlands',    dial: '+31'  },
        { iso: 'be', name: 'Belgium',        dial: '+32'  },
        { iso: 'at', name: 'Austria',        dial: '+43'  },
        { iso: 'ch', name: 'Switzerland',    dial: '+41'  },
        { iso: 'se', name: 'Sweden',         dial: '+46'  },
        { iso: 'dk', name: 'Denmark',        dial: '+45'  },
        { iso: 'no', name: 'Norway',         dial: '+47'  },
        { iso: 'fi', name: 'Finland',        dial: '+358' },
        { iso: 'cz', name: 'Czech Republic', dial: '+420' },
        { iso: 'hu', name: 'Hungary',        dial: '+36'  },
        { iso: 'sk', name: 'Slovakia',       dial: '+421' },
        { iso: 'hr', name: 'Croatia',        dial: '+385' },
        { iso: 'bg', name: 'Bulgaria',       dial: '+359' },
        { iso: 'in', name: 'India',          dial: '+91'  },
        { iso: 'pk', name: 'Pakistan',       dial: '+92'  },
        { iso: 'ng', name: 'Nigeria',        dial: '+234' },
        { iso: 'za', name: 'South Africa',   dial: '+27'  },
        { iso: 'ph', name: 'Philippines',    dial: '+63'  },
        { iso: 'br', name: 'Brazil',         dial: '+55'  },
        { iso: 'au', name: 'Australia',      dial: '+61'  },
        { iso: 'cn', name: 'China',          dial: '+86'  },
        { iso: 'ca', name: 'Canada',         dial: '+1'   },
      ];

      function selectCountry(iso) {
        const c = COUNTRY_LIST.find(x => x.iso === iso);
        if (!c) return;
        document.getElementById('selectedFlag').src = 'https://flagcdn.com/w20/' + c.iso + '.png';
        document.getElementById('selectedFlag').alt = c.iso.toUpperCase();
        document.getElementById('selectedDialCode').textContent = c.dial;
        document.getElementById('countryDropdown').style.display = 'none';
        document.querySelectorAll('#countryOptions .country-option').forEach(el => {
          el.classList.toggle('is-selected', el.dataset.iso === iso);
        });
      }

      function setPhoneFromFullNumber(fullPhone) {
        const phoneInput = document.getElementById('customerPhone');
        if (!fullPhone) { phoneInput.value = ''; return; }
        const sorted = [...COUNTRY_LIST].sort((a, b) => b.dial.length - a.dial.length);
        for (const c of sorted) {
          if (fullPhone.startsWith(c.dial)) {
            selectCountry(c.iso);
            phoneInput.value = fullPhone.slice(c.dial.length);
            return;
          }
        }
        phoneInput.value = fullPhone.replace(/^\+/, '');
      }

      (function initCountrySelector() {
        const btn = document.getElementById('countryCodeBtn');
        const dropdown = document.getElementById('countryDropdown');
        const search = document.getElementById('countrySearch');
        const optionsContainer = document.getElementById('countryOptions');

        function renderOptions(filter) {
          const q = (filter || '').toLowerCase();
          optionsContainer.innerHTML = '';
          const selected = document.getElementById('selectedDialCode').textContent;
          COUNTRY_LIST.filter(c =>
            !q || c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.iso.includes(q)
          ).forEach(c => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'country-option' + (c.dial === selected && c.iso === document.getElementById('selectedFlag').alt.toLowerCase() ? ' is-selected' : '');
            el.dataset.iso = c.iso;
            el.innerHTML = '<img src="https://flagcdn.com/w20/' + c.iso + '.png" width="20" height="15" alt="' + c.iso.toUpperCase() + '"> ' +
              '<span>' + c.name + '</span>' +
              '<span class="dial">' + c.dial + '</span>';
            el.addEventListener('click', () => {
              selectCountry(c.iso);
              document.getElementById('customerPhone').focus();
            });
            optionsContainer.appendChild(el);
          });
        }

        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const open = dropdown.style.display === 'block';
          dropdown.style.display = open ? 'none' : 'block';
          if (!open) {
            search.value = '';
            renderOptions('');
            setTimeout(() => search.focus(), 50);
          }
        });

        search.addEventListener('input', (e) => {
          renderOptions(e.target.value);
        });
        search.addEventListener('click', (e) => e.stopPropagation());

        document.addEventListener('click', (e) => {
          if (!document.getElementById('countryCodeWrapper').contains(e.target)) {
            dropdown.style.display = 'none';
          }
        });

        renderOptions('');
      })();

      // ── Driver country-code selector (modal) ──
      (function initDriverCountrySelector() {
        const btn = document.getElementById('driverCountryCodeBtn');
        const dropdown = document.getElementById('driverCountryDropdown');
        const search = document.getElementById('driverCountrySearch');
        const optionsContainer = document.getElementById('driverCountryOptions');
        if (!btn || !dropdown) return;

        function renderOptions(filter) {
          const q = (filter || '').toLowerCase();
          optionsContainer.innerHTML = '';
          const selectedDial = document.getElementById('driverSelectedDialCode').textContent;
          COUNTRY_LIST.filter(c =>
            !q || c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.iso.includes(q)
          ).forEach(c => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'country-option' + (c.dial === selectedDial && c.iso === document.getElementById('driverSelectedFlag').alt.toLowerCase() ? ' is-selected' : '');
            el.dataset.iso = c.iso;
            el.innerHTML = '<img src="https://flagcdn.com/w20/' + c.iso + '.png" width="20" height="15" alt="' + c.iso.toUpperCase() + '"> ' +
              '<span>' + c.name + '</span>' +
              '<span class="dial">' + c.dial + '</span>';
            el.addEventListener('click', () => { selectDriverCountry(c.iso); });
            optionsContainer.appendChild(el);
          });
        }

        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const open = dropdown.style.display === 'block';
          dropdown.style.display = open ? 'none' : 'block';
          if (!open) { search.value = ''; renderOptions(''); setTimeout(() => search.focus(), 50); }
        });

        search.addEventListener('input', (e) => { renderOptions(e.target.value); });
        search.addEventListener('click', (e) => e.stopPropagation());

        document.addEventListener('click', (e) => {
          if (!document.getElementById('driverCountryCodeWrapper').contains(e.target)) {
            dropdown.style.display = 'none';
          }
        });

        renderOptions('');
      })();
    </script>
  </body>
</html>
