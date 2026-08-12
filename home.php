<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: /');
    exit;
}
$pageTitle = 'Dashboard | Powercabs Dispatcher';
require('modules/head.php');

require_once __DIR__ . '/auth/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<body>

  <?php @require('modules/sidebar.php'); ?>
  <?php require_once 'modules/navbar.php'; ?>

  <main id="app-content" class="main-content p-4" style="background:#F4F4F5; min-height:92vh;">

    <!-- KPI CARDS -->
    <div class="row g-3 mb-3">
      <div class="col-sm-6 col-xl-3">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size:0.8125rem; color:#71717A; font-weight:500;">Registered Drivers</span>
            <div class="d-flex align-items-center justify-content-center" style="width:32px; height:32px; background:#FFF3E8; border-radius:8px;">
              <i class="bi bi-person-badge-fill" style="color:#f37a20; font-size:15px;"></i>
            </div>
          </div>
          <div id="statTotalDrivers" style="font-size:1.875rem; font-weight:700; color:#18181B; letter-spacing:-0.025em; line-height:1.1;">0</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size:0.8125rem; color:#71717A; font-weight:500;">Registered Passengers</span>
            <div class="d-flex align-items-center justify-content-center" style="width:32px; height:32px; background:#F4F4F5; border-radius:8px;">
              <i class="bi bi-people-fill" style="color:#18181B; font-size:15px;"></i>
            </div>
          </div>
          <div id="statTotalPassengers" style="font-size:1.875rem; font-weight:700; color:#18181B; letter-spacing:-0.025em; line-height:1.1;">0</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size:0.8125rem; color:#71717A; font-weight:500;">Application Rides</span>
            <div class="d-flex align-items-center justify-content-center" style="width:32px; height:32px; background:#FFF3E8; border-radius:8px;">
              <i class="bi bi-car-front-fill" style="color:#f37a20; font-size:15px;"></i>
            </div>
          </div>
          <div id="statTotalRides" style="font-size:1.875rem; font-weight:700; color:#18181B; letter-spacing:-0.025em; line-height:1.1;">0</div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span style="font-size:0.8125rem; color:#71717A; font-weight:500;">Corporate Rides</span>
            <div class="d-flex align-items-center justify-content-center" style="width:32px; height:32px; background:#F0FDF4; border-radius:8px;">
              <i class="bi bi-calendar-check-fill" style="color:#22C55E; font-size:15px;"></i>
            </div>
          </div>
          <div id="statTodayRides" style="font-size:1.875rem; font-weight:700; color:#18181B; letter-spacing:-0.025em; line-height:1.1;">0</div>
        </div>
      </div>
    </div>

    <!-- ANALYTICS ROW 1 -->
    <div class="row g-3 mb-3">
      <!-- Rides trend -->
      <div class="col-12 col-xl-8">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="d-flex align-items-center justify-content-center rounded-2" style="width:28px; height:28px; background:#FFF3E8;">
                  <i class="bi bi-graph-up-arrow" style="color:#f37a20; font-size:13px;"></i>
                </div>
                <span class="fw-bold" style="font-size:0.9rem; color:#18181B;">Rides Trend</span>
              </div>
              <div style="font-size:0.72rem; color:#A1A1AA; font-weight:500;">Last 7 days · totals vs completed vs cancelled</div>
            </div>
            <div class="d-flex align-items-center gap-3" style="font-size:0.72rem; color:#52525B; font-weight:600;">
              <span class="d-flex align-items-center gap-1"><span style="width:10px; height:10px; border-radius:3px; background:#f37a20;"></span> Total</span>
              <span class="d-flex align-items-center gap-1"><span style="width:10px; height:10px; border-radius:3px; background:#22C55E;"></span> Completed</span>
              <span class="d-flex align-items-center gap-1"><span style="width:10px; height:10px; border-radius:3px; background:#E11D48;"></span> Cancelled</span>
            </div>
          </div>
          <div style="position:relative; height:280px;">
            <canvas id="chartRidesTrend"></canvas>
          </div>
          <div class="row g-2 mt-3 pt-3" style="border-top:1px solid #F4F4F5;">
            <div class="col-4">
              <div style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">7-day total</div>
              <div id="trendTotal" style="font-size:1.25rem; font-weight:700; color:#18181B; letter-spacing:-0.02em;">0</div>
            </div>
            <div class="col-4">
              <div style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Daily average</div>
              <div id="trendAvg" style="font-size:1.25rem; font-weight:700; color:#18181B; letter-spacing:-0.02em;">0</div>
            </div>
            <div class="col-4">
              <div style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Peak day</div>
              <div id="trendPeak" style="font-size:1.25rem; font-weight:700; color:#18181B; letter-spacing:-0.02em;">—</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Status donut -->
      <div class="col-12 col-xl-4">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center gap-2 mb-1">
            <div class="d-flex align-items-center justify-content-center rounded-2" style="width:28px; height:28px; background:#F4F4F5;">
              <i class="bi bi-pie-chart-fill" style="color:#18181B; font-size:13px;"></i>
            </div>
            <span class="fw-bold" style="font-size:0.9rem; color:#18181B;">Status Breakdown</span>
          </div>
          <div style="font-size:0.72rem; color:#A1A1AA; font-weight:500; margin-bottom:12px;">Live ride-status distribution</div>

          <div style="position:relative; height:180px;">
            <canvas id="chartStatus"></canvas>
            <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
              <div id="statusCenterValue" style="font-size:1.6rem; font-weight:800; color:#18181B; letter-spacing:-0.03em; line-height:1;">0</div>
              <div style="font-size:0.68rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; margin-top:2px;">Active</div>
            </div>
          </div>

          <div class="mt-3" style="display:grid; grid-template-columns:1fr 1fr; gap:8px 14px;">
            <div class="status-legend-row" data-color="#f37a20"><span class="dot" style="background:#f37a20;"></span><span class="lbl">Unassigned</span><span class="val" id="statUnassigned">0</span></div>
            <div class="status-legend-row" data-color="#3B82F6"><span class="dot" style="background:#3B82F6;"></span><span class="lbl">Assigned</span><span class="val" id="statAssigned">0</span></div>
            <div class="status-legend-row" data-color="#FB923C"><span class="dot" style="background:#FB923C;"></span><span class="lbl">On Trip</span><span class="val" id="statOnTrip">0</span></div>
            <div class="status-legend-row" data-color="#8B5CF6"><span class="dot" style="background:#8B5CF6;"></span><span class="lbl">Scheduled</span><span class="val" id="statScheduled">0</span></div>
            <div class="status-legend-row" data-color="#22C55E"><span class="dot" style="background:#22C55E;"></span><span class="lbl">Completed</span><span class="val" id="statCompleted">0</span></div>
            <div class="status-legend-row" data-color="#E11D48"><span class="dot" style="background:#E11D48;"></span><span class="lbl">Cancelled</span><span class="val" id="statCancelled">0</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ANALYTICS ROW 2 -->
    <div class="row g-3 mb-3">
      <!-- Driver verification -->
      <div class="col-12 col-xl-5">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="d-flex align-items-center gap-2">
              <div class="d-flex align-items-center justify-content-center rounded-2" style="width:28px; height:28px; background:#F0FDF4;">
                <i class="bi bi-shield-check" style="color:#22C55E; font-size:13px;"></i>
              </div>
              <span class="fw-bold" style="font-size:0.9rem; color:#18181B;">Driver Verification</span>
            </div>
            <span id="driverApprovalRate" style="font-size:0.72rem; color:#22C55E; font-weight:700; background:#F0FDF4; padding:3px 8px; border-radius:999px;">0% approved</span>
          </div>
          <div style="font-size:0.72rem; color:#A1A1AA; font-weight:500; margin-bottom:16px;">Verification status across the fleet</div>

          <div style="position:relative; height:200px;">
            <canvas id="chartDrivers"></canvas>
          </div>

          <div class="row g-2 mt-3 pt-3" style="border-top:1px solid #F4F4F5;">
            <div class="col-4">
              <div class="d-flex align-items-center gap-1" style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">
                <span style="width:8px; height:8px; border-radius:2px; background:#22C55E;"></span> Approved
              </div>
              <div id="drvApproved" style="font-size:1.15rem; font-weight:700; color:#18181B;">0</div>
            </div>
            <div class="col-4">
              <div class="d-flex align-items-center gap-1" style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">
                <span style="width:8px; height:8px; border-radius:2px; background:#F59E0B;"></span> Pending
              </div>
              <div id="drvPending" style="font-size:1.15rem; font-weight:700; color:#18181B;">0</div>
            </div>
            <div class="col-4">
              <div class="d-flex align-items-center gap-1" style="font-size:0.7rem; color:#A1A1AA; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">
                <span style="width:8px; height:8px; border-radius:2px; background:#E11D48;"></span> Rejected
              </div>
              <div id="drvRejected" style="font-size:1.15rem; font-weight:700; color:#18181B;">0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance snapshot -->
      <div class="col-12 col-xl-7">
        <div class="h-100 p-4 dash-card">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="d-flex align-items-center justify-content-center rounded-2" style="width:28px; height:28px; background:#FFF3E8;">
                <i class="bi bi-speedometer2" style="color:#f37a20; font-size:13px;"></i>
              </div>
              <span class="fw-bold" style="font-size:0.9rem; color:#18181B;">Performance Snapshot</span>
            </div>
            <span style="font-size:0.72rem; color:#A1A1AA; font-weight:500;">Derived from live data</span>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 rounded-2" style="background:#FAFAFA; border:1px solid #F4F4F5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size:0.75rem; color:#71717A; font-weight:600;">Completion rate</span>
                  <span id="completionRateBadge" style="font-size:0.7rem; color:#22C55E; font-weight:700;">0%</span>
                </div>
                <div style="height:8px; background:#F4F4F5; border-radius:999px; overflow:hidden;">
                  <div id="completionRateBar" style="height:100%; width:0%; background:linear-gradient(90deg,#22C55E,#16A34A); border-radius:999px; transition:width .6s ease;"></div>
                </div>
                <div style="font-size:0.68rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Completed vs all rides</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-2" style="background:#FAFAFA; border:1px solid #F4F4F5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size:0.75rem; color:#71717A; font-weight:600;">Cancellation rate</span>
                  <span id="cancelRateBadge" style="font-size:0.7rem; color:#E11D48; font-weight:700;">0%</span>
                </div>
                <div style="height:8px; background:#F4F4F5; border-radius:999px; overflow:hidden;">
                  <div id="cancelRateBar" style="height:100%; width:0%; background:linear-gradient(90deg,#FB7185,#E11D48); border-radius:999px; transition:width .6s ease;"></div>
                </div>
                <div style="font-size:0.68rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Cancelled vs all rides</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-2" style="background:#FAFAFA; border:1px solid #F4F4F5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size:0.75rem; color:#71717A; font-weight:600;">Active in pipeline</span>
                  <span id="pipelineBadge" style="font-size:0.7rem; color:#f37a20; font-weight:700;">0</span>
                </div>
                <div style="height:8px; background:#F4F4F5; border-radius:999px; overflow:hidden;">
                  <div id="pipelineBar" style="height:100%; width:0%; background:linear-gradient(90deg,#FDBA74,#f37a20); border-radius:999px; transition:width .6s ease;"></div>
                </div>
                <div style="font-size:0.68rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Unassigned + Assigned + Scheduled</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 rounded-2" style="background:#FAFAFA; border:1px solid #F4F4F5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span style="font-size:0.75rem; color:#71717A; font-weight:600;">Driver utilisation</span>
                  <span id="utilisationBadge" style="font-size:0.7rem; color:#3B82F6; font-weight:700;">0%</span>
                </div>
                <div style="height:8px; background:#F4F4F5; border-radius:999px; overflow:hidden;">
                  <div id="utilisationBar" style="height:100%; width:0%; background:linear-gradient(90deg,#93C5FD,#3B82F6); border-radius:999px; transition:width .6s ease;"></div>
                </div>
                <div style="font-size:0.68rem; color:#A1A1AA; font-weight:500; margin-top:6px;">Assigned rides ÷ approved drivers</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

<script src="js/home.js"></script>
</body>
</html>
