<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    header('Location: /');
    exit;
}

$user = $_SESSION['user'];
require('modules/head.php');

require_once __DIR__ . '/auth/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<body>
  
  <?php @require('modules/sidebar.php'); ?>
  <?php require_once 'modules/navbar.php'; ?>

  <main class="main-content p-4" style="background:#F4F4F5; min-height:92vh;">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
   <?php
  $dublinTime = new DateTime('now', new DateTimeZone('Europe/Dublin'));
  $hour       = (int) $dublinTime->format('H');
  $dateStr    = $dublinTime->format('l, d F Y');

  $greeting = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
  $icon     = $hour < 12
    ? '<i class="bi bi-sun-fill" style="color:#F59E0B; font-size:1.1rem;"></i>'
    : ($hour < 17
      ? '<i class="bi bi-brightness-high-fill" style="color:#f37a20; font-size:1.1rem;"></i>'
      : '<i class="bi bi-moon-stars-fill" style="color:#6366F1; font-size:1rem;"></i>');

  $firstName = htmlspecialchars(explode(' ', trim($user_name ?? ''))[0] ?? 'Dispatcher');
?>

<div>
  <div style="font-size:0.72rem; color:#A1A1AA; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; margin-bottom:4px;">
    <?php echo $dateStr; ?>
  </div>
  <h4 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size:1.25rem; color:#18181B; letter-spacing:-0.02em; line-height:1.3;">
    <?php echo $icon; ?>
    Good <?php echo $greeting; ?>, <?php echo $firstName; ?>
  </h4>
</div>

      <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-2" style="background:#fff; border:1.5px solid #EBEBEB; font-size:0.8rem; color:#52525B; font-weight:600;">
        <span style="width:8px; height:8px; border-radius:50%; background:#22C55E; flex-shrink:0; box-shadow:0 0 0 3px rgba(34,197,94,0.15);"></span>
        System Online
      </div>
    </div>

    <div class="row g-3 mb-3">

      <div class="col-sm-6 col-xl-3">
        <div class="rounded-3 p-4 h-100 position-relative overflow-hidden" style="background:linear-gradient(135deg,#f37a20 0%,#c95e0a 100%); box-shadow:0 8px 24px rgba(243,122,32,0.30);">
          <div class="position-absolute" style="top:-18px; right:-18px; width:100px; height:100px; border-radius:50%; background:rgba(255,255,255,0.10);"></div>
          <div class="position-absolute" style="bottom:-30px; right:20px; width:70px; height:70px; border-radius:50%; background:rgba(255,255,255,0.07);"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:40px; height:40px; background:rgba(255,255,255,0.20);">
              <i class="bi bi-person-badge-fill" style="color:#fff; font-size:18px;"></i>
            </div>
            <span class="rounded-pill px-2 py-1 fw-bold" style="font-size:0.7rem; background:rgba(255,255,255,0.20); color:#fff;">Total</span>
          </div>
          <div style="font-size:2.2rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statTotalDrivers">0</div>
          <div style="font-size:0.8rem; color:rgba(255,255,255,0.80); font-weight:500; margin-top:6px;">Registered Drivers</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="rounded-3 p-4 h-100 position-relative overflow-hidden" style="background:#18181B; box-shadow:0 8px 24px rgba(0,0,0,0.18);">
          <div class="position-absolute" style="top:-18px; right:-18px; width:100px; height:100px; border-radius:50%; background:rgba(255,255,255,0.04);"></div>
          <div class="position-absolute" style="bottom:-30px; right:20px; width:70px; height:70px; border-radius:50%; background:rgba(255,255,255,0.03);"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:40px; height:40px; background:rgba(255,255,255,0.10);">
              <i class="bi bi-people-fill" style="color:#fff; font-size:18px;"></i>
            </div>
            <span class="rounded-pill px-2 py-1 fw-bold" style="font-size:0.7rem; background:rgba(255,255,255,0.10); color:rgba(255,255,255,0.80);">Total</span>
          </div>
          <div style="font-size:2.2rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statTotalPassengers">0</div>
          <div style="font-size:0.8rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:6px;">Registered Passengers</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="rounded-3 p-4 h-100 position-relative overflow-hidden" style="background:#fff; border:1.5px solid #EBEBEB; box-shadow:0 4px 16px rgba(0,0,0,0.05);">
          <div class="position-absolute" style="top:-18px; right:-18px; width:100px; height:100px; border-radius:50%; background:#F4F4F5;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:40px; height:40px; background:#FFF3E8;">
              <i class="bi bi-car-front-fill" style="color:#f37a20; font-size:18px;"></i>
            </div>
            <span class="rounded-pill px-2 py-1 fw-bold" style="font-size:0.7rem; background:#F4F4F5; color:#71717A;">All Time</span>
          </div>
          <div style="font-size:2.2rem; font-weight:800; color:#18181B; letter-spacing:-0.04em; line-height:1;" id="statTotalRides">0</div>
          <div style="font-size:0.8rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Application Rides</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="rounded-3 p-4 h-100 position-relative overflow-hidden" style="background:#fff; border:1.5px solid #EBEBEB; box-shadow:0 4px 16px rgba(0,0,0,0.05);">
          <div class="position-absolute" style="top:-18px; right:-18px; width:100px; height:100px; border-radius:50%; background:#F4F4F5;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:40px; height:40px; background:#F0FDF4;">
              <i class="bi bi-calendar-check-fill" style="color:#22C55E; font-size:18px;"></i>
            </div>
            <span class="rounded-pill px-2 py-1 fw-bold d-flex align-items-center gap-1" style="font-size:0.7rem; background:#F0FDF4; color:#22C55E;">
              <span style="width:5px; height:5px; border-radius:50%; background:#22C55E;"></span>All Time
            </span>
          </div>
          <div style="font-size:2.2rem; font-weight:800; color:#18181B; letter-spacing:-0.04em; line-height:1;" id="statTodayRides">0</div>
          <div style="font-size:0.8rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Corporate Rides</div>
        </div>
      </div>

    </div>

    <div class="rounded-3 mb-3 p-4 position-relative overflow-hidden" style="background:linear-gradient(145deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%); box-shadow:0 8px 32px rgba(0,0,0,0.20);">

      <div class="position-absolute" style="top:-60px; right:-60px; width:260px; height:260px; border-radius:50%; border:50px solid rgba(255,255,255,0.04);"></div>
      <div class="position-absolute" style="top:20px; right:40px; width:140px; height:140px; border-radius:50%; border:28px solid rgba(255,255,255,0.03);"></div>
      <div class="position-absolute" style="bottom:-80px; left:-40px; width:240px; height:240px; border-radius:50%; border:45px solid rgba(255,255,255,0.03);"></div>
      <div class="position-absolute" style="bottom:10px; left:160px; width:100px; height:100px; border-radius:50%; border:20px solid rgba(243,122,32,0.08);"></div>

      <div class="d-flex align-items-center justify-content-between mb-4" style="position:relative; z-index:2;">
        <div class="d-flex align-items-center gap-2">
          <div class="d-flex align-items-center justify-content-center rounded-2" style="width:30px; height:30px; background:rgba(243,122,32,0.25); border:1px solid rgba(243,122,32,0.30);">
            <i class="bi bi-pie-chart-fill" style="color:#f37a20; font-size:13px;"></i>
          </div>
          <span class="fw-bold" style="font-size:0.875rem; color:#fff; letter-spacing:0.01em;">Ride Status Breakdown</span>
        </div>
        <span style="font-size:0.75rem; color:rgba(255,255,255,0.40); font-weight:500;">Live counts</span>
      </div>

      <div class="row g-3" style="position:relative; z-index:2;">

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(243,122,32,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(243,122,32,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(243,122,32,0.18); border:1px solid rgba(243,122,32,0.30);">
              <i class="bi bi-hourglass-split" style="color:#f37a20; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statUnassigned">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">Unassigned</div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(59,130,246,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(59,130,246,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(59,130,246,0.18); border:1px solid rgba(59,130,246,0.30);">
              <i class="bi bi-person-check-fill" style="color:#60A5FA; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statAssigned">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">Assigned</div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(249,115,22,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(249,115,22,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(249,115,22,0.18); border:1px solid rgba(249,115,22,0.30);">
              <i class="bi bi-car-front-fill" style="color:#FB923C; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statOnTrip">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">On Trip</div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(139,92,246,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(139,92,246,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.30);">
              <i class="bi bi-calendar-event-fill" style="color:#A78BFA; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statScheduled">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">Scheduled</div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(34,197,94,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(34,197,94,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(34,197,94,0.18); border:1px solid rgba(34,197,94,0.30);">
              <i class="bi bi-check-circle-fill" style="color:#4ADE80; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statCompleted">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">Completed</div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div class="rounded-3 p-3 text-center h-100 position-relative overflow-hidden"
            style="background:rgba(255,255,255,0.06); border:1px solid rgba(225,29,72,0.30); backdrop-filter:blur(8px);">
            <div class="position-absolute" style="top:-20px; right:-20px; width:70px; height:70px; border-radius:50%; background:rgba(225,29,72,0.08);"></div>
            <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-2 position-relative" style="width:38px; height:38px; background:rgba(225,29,72,0.18); border:1px solid rgba(225,29,72,0.30);">
              <i class="bi bi-x-circle-fill" style="color:#FB7185; font-size:16px;"></i>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#fff; letter-spacing:-0.04em; line-height:1;" id="statCancelled">0</div>
            <div style="font-size:0.72rem; color:rgba(255,255,255,0.55); font-weight:500; margin-top:5px; letter-spacing:0.02em;">Cancelled</div>
          </div>
        </div>

      </div>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <div class="rounded-3 border" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
          <div class="px-4 py-1" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.875rem; color:#18181B;">Quick Actions</span>
          </div>
          <div class="p-3">
            <div class="row g-3">
              <div class="col-md-4 col-sm-12">
                <a href="order.php" class="d-flex align-items-center gap-3 rounded-2 px-3 py-2 text-decoration-none"
                  style="background:#FFF3E8; border:1.5px solid rgba(243,122,32,0.15);"
                  onmouseover="this.style.background='#FDECD8';"
                  onmouseout="this.style.background='#FFF3E8';">
                  <div class="d-flex align-items-center justify-content-center rounded-2" style="width:32px; height:32px; background:#f37a20; flex-shrink:0;">
                    <i class="bi bi-plus-circle-fill" style="color:#fff; font-size:14px;"></i>
                  </div>
                  <div>
                    <div class="fw-semibold" style="font-size:0.8375rem; color:#18181B;">New Order</div>
                    <div style="font-size:0.72rem; color:#A1A1AA;">Create a ride booking</div>
                  </div>
                  <i class="bi bi-chevron-right ms-auto" style="font-size:11px; color:#A1A1AA;"></i>
                </a>
              </div>
              <div class="col-md-4 col-sm-12">
                <a href="map.php" class="d-flex align-items-center gap-3 rounded-2 px-3 py-2 text-decoration-none"
                  style="background:#FAFAFA; border:1.5px solid #EBEBEB;"
                  onmouseover="this.style.borderColor='#f37a20'; this.style.background='#FFF3E8';"
                  onmouseout="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA';">
                  <div class="d-flex align-items-center justify-content-center rounded-2" style="width:32px; height:32px; background:#F4F4F5; flex-shrink:0;">
                    <i class="bi bi-map-fill" style="color:#52525B; font-size:14px;"></i>
                  </div>
                  <div>
                    <div class="fw-semibold" style="font-size:0.8375rem; color:#18181B;">Live Map</div>
                    <div style="font-size:0.72rem; color:#A1A1AA;">Track drivers in real-time</div>
                  </div>
                  <i class="bi bi-chevron-right ms-auto" style="font-size:11px; color:#A1A1AA;"></i>
                </a>
              </div>
              <div class="col-md-4 col-sm-12">
                <a href="preorder.php" class="d-flex align-items-center gap-3 rounded-2 px-3 py-2 text-decoration-none"
                  style="background:#FAFAFA; border:1.5px solid #EBEBEB;"
                  onmouseover="this.style.borderColor='#f37a20'; this.style.background='#FFF3E8';"
                  onmouseout="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA';">
                  <div class="d-flex align-items-center justify-content-center rounded-2" style="width:32px; height:32px; background:#F4F4F5; flex-shrink:0;">
                    <i class="bi bi-lightning-charge-fill" style="color:#52525B; font-size:14px;"></i>
                  </div>
                  <div>
                    <div class="fw-semibold" style="font-size:0.8375rem; color:#18181B;">Live Orders</div>
                    <div style="font-size:0.72rem; color:#A1A1AA;">Manage active rides</div>
                  </div>
                  <i class="bi bi-chevron-right ms-auto" style="font-size:11px; color:#A1A1AA;"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  async function loadDashboardStats() {
    try {
      const setText = (id, value) => {
        const node = document.getElementById(id);
        if (node) node.textContent = value;
      };

      const res = await fetch('api/get_dashboard_stats.php');
      if (res.status === 401) {
        window.location.href = '/';
        return;
      }
      if (!res.ok) {
        throw new Error(`Failed to fetch dashboard stats (HTTP ${res.status})`);
      }

      const result = await res.json();
      if (!result || !result.success || !result.data) {
        throw new Error(result && result.error ? result.error : 'Dashboard stats returned empty response');
      }

      const data = result.data;

      // Totals
      setText('statTotalDrivers', data.drivers ?? 0);
      setText('statTotalPassengers', data.passengers ?? 0);
      setText('statTotalRides', data.rides ?? 0);
      setText('statTodayRides', data.corporate_rides ?? 0);

      // Status breakdown (On Trip intentionally forced to 0 per your request)
      setText('statUnassigned', data.unassigned ?? 0);
      setText('statAssigned', data.assigned ?? 0);
      setText('statOnTrip', 0);
      setText('statScheduled', data.scheduled ?? 0);
      setText('statCompleted', data.completed ?? 0);
      setText('statCancelled', data.cancelled ?? 0);
    } catch (err) {
      console.error('Failed to load dashboard stats:', err);
    }
  }

  document.addEventListener('DOMContentLoaded', loadDashboardStats);
</script>