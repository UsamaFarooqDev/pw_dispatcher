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

  <style>
    .dash-card {
      background: #fff;
      border: 1px solid #E4E4E7;
      border-radius: 12px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .dash-card:hover {
      border-color: #D4D4D8;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .status-legend-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.78rem;
      font-weight: 500;
      color: #52525B;
    }
    .status-legend-row .dot {
      width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0;
    }
    .status-legend-row .lbl { flex: 1; }
    .status-legend-row .val {
      font-weight: 700; color: #18181B; font-variant-numeric: tabular-nums;
    }
  </style>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  let trendChart = null;
  let statusChart = null;
  let driversChart = null;

  function fmt(n) {
    n = Number(n) || 0;
    return n.toLocaleString();
  }

  function pct(num, den) {
    if (!den) return 0;
    return Math.round((num / den) * 100);
  }

  function renderTrendChart(series) {
    const ctx = document.getElementById('chartRidesTrend');
    if (!ctx) return;

    const labels = series.map(d => {
      const dt = new Date(d.date + 'T00:00:00');
      return dt.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit' });
    });
    const totals = series.map(d => d.total || 0);
    const completed = series.map(d => d.completed || 0);
    const cancelled = series.map(d => d.cancelled || 0);

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(243,122,32,0.28)');
    gradient.addColorStop(1, 'rgba(243,122,32,0.00)');

    if (trendChart) trendChart.destroy();
    trendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Total',
            data: totals,
            borderColor: '#f37a20',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#f37a20',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
          },
          {
            label: 'Completed',
            data: completed,
            borderColor: '#22C55E',
            backgroundColor: 'transparent',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 5,
            borderDash: [4, 4],
          },
          {
            label: 'Cancelled',
            data: cancelled,
            borderColor: '#E11D48',
            backgroundColor: 'transparent',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 5,
            borderDash: [4, 4],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#18181B',
            titleColor: '#fff',
            bodyColor: '#E4E4E7',
            padding: 10,
            displayColors: true,
            boxPadding: 4,
            cornerRadius: 8,
            borderColor: 'rgba(255,255,255,0.08)',
            borderWidth: 1,
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#A1A1AA', font: { size: 11 } },
            border: { color: '#E4E4E7' },
          },
          y: {
            beginAtZero: true,
            grid: { color: '#F4F4F5' },
            ticks: { color: '#A1A1AA', font: { size: 11 }, precision: 0 },
            border: { display: false },
          },
        },
      },
    });

    // Summary stats
    const total7 = totals.reduce((a, b) => a + b, 0);
    const avg = total7 / (series.length || 1);
    let peakIdx = 0;
    totals.forEach((v, i) => { if (v > totals[peakIdx]) peakIdx = i; });
    const peakLabel = series[peakIdx] ? new Date(series[peakIdx].date + 'T00:00:00')
      .toLocaleDateString('en-GB', { weekday: 'short' }) + ' · ' + (totals[peakIdx] || 0) : '—';

    document.getElementById('trendTotal').textContent = fmt(total7);
    document.getElementById('trendAvg').textContent = avg.toFixed(1);
    document.getElementById('trendPeak').textContent = peakLabel;
  }

  function renderStatusChart(d) {
    const ctx = document.getElementById('chartStatus');
    if (!ctx) return;

    const segments = [
      { label: 'Unassigned', val: d.unassigned ?? 0, color: '#f37a20' },
      { label: 'Assigned',   val: d.assigned ?? 0,   color: '#3B82F6' },
      { label: 'On Trip',    val: d.on_trip ?? 0,    color: '#FB923C' },
      { label: 'Scheduled',  val: d.scheduled ?? 0,  color: '#8B5CF6' },
      { label: 'Completed',  val: d.completed ?? 0,  color: '#22C55E' },
      { label: 'Cancelled',  val: d.cancelled ?? 0,  color: '#E11D48' },
    ];
    const totalAll = segments.reduce((a, s) => a + s.val, 0);
    const active = (d.unassigned ?? 0) + (d.assigned ?? 0) + (d.on_trip ?? 0) + (d.scheduled ?? 0);
    document.getElementById('statusCenterValue').textContent = fmt(active);

    if (statusChart) statusChart.destroy();
    statusChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: segments.map(s => s.label),
        datasets: [{
          data: segments.map(s => s.val),
          backgroundColor: segments.map(s => s.color),
          borderColor: '#fff',
          borderWidth: 3,
          hoverOffset: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#18181B',
            titleColor: '#fff',
            bodyColor: '#E4E4E7',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: (ctx) => {
                const v = ctx.parsed || 0;
                const p = totalAll ? Math.round((v / totalAll) * 100) : 0;
                return `${ctx.label}: ${v} (${p}%)`;
              },
            },
          },
        },
      },
    });
  }

  function renderDriversChart(ds, totalDrivers) {
    const ctx = document.getElementById('chartDrivers');
    if (!ctx) return;

    const approved = ds.approved ?? 0;
    const pending  = ds.pending ?? 0;
    const rejected = ds.rejected ?? 0;
    const sum = approved + pending + rejected;
    const denom = totalDrivers || sum || 1;

    document.getElementById('drvApproved').textContent = fmt(approved);
    document.getElementById('drvPending').textContent  = fmt(pending);
    document.getElementById('drvRejected').textContent = fmt(rejected);
    const approveRate = pct(approved, denom);
    document.getElementById('driverApprovalRate').textContent = approveRate + '% approved';

    if (driversChart) driversChart.destroy();
    driversChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
          data: [approved, pending, rejected],
          backgroundColor: ['#22C55E', '#F59E0B', '#E11D48'],
          borderRadius: 6,
          borderSkipped: false,
          barThickness: 26,
        }],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#18181B',
            titleColor: '#fff',
            bodyColor: '#E4E4E7',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: (ctx) => {
                const v = ctx.parsed.x || 0;
                const p = denom ? Math.round((v / denom) * 100) : 0;
                return `${v} (${p}%)`;
              },
            },
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: { color: '#F4F4F5' },
            ticks: { color: '#A1A1AA', font: { size: 11 }, precision: 0 },
            border: { display: false },
          },
          y: {
            grid: { display: false },
            ticks: { color: '#52525B', font: { size: 12, weight: 600 } },
            border: { display: false },
          },
        },
      },
    });
  }

  function renderPerformance(data) {
    const completed = data.completed ?? 0;
    const cancelled = data.cancelled ?? 0;
    const unassigned = data.unassigned ?? 0;
    const assigned = data.assigned ?? 0;
    const scheduled = data.scheduled ?? 0;
    const totalRides = data.rides ?? 0;
    const drivers = data.drivers ?? 0;
    const approvedDrivers = (data.drivers_status && data.drivers_status.approved) ?? 0;

    const completionRate = pct(completed, totalRides);
    const cancelRate = pct(cancelled, totalRides);
    const pipeline = unassigned + assigned + scheduled;
    const pipelinePct = pct(pipeline, totalRides || 1);
    const utilisation = approvedDrivers ? Math.min(100, Math.round((assigned / approvedDrivers) * 100)) : 0;

    document.getElementById('completionRateBadge').textContent = completionRate + '%';
    document.getElementById('completionRateBar').style.width   = completionRate + '%';

    document.getElementById('cancelRateBadge').textContent = cancelRate + '%';
    document.getElementById('cancelRateBar').style.width   = cancelRate + '%';

    document.getElementById('pipelineBadge').textContent = fmt(pipeline);
    document.getElementById('pipelineBar').style.width   = Math.min(100, pipelinePct) + '%';

    document.getElementById('utilisationBadge').textContent = utilisation + '%';
    document.getElementById('utilisationBar').style.width   = utilisation + '%';
  }

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
      if (!res.ok) throw new Error(`Failed to fetch dashboard stats (HTTP ${res.status})`);

      const result = await res.json();
      if (!result || !result.success || !result.data) {
        throw new Error(result && result.error ? result.error : 'Dashboard stats returned empty response');
      }
      const data = result.data;

      // KPI totals
      setText('statTotalDrivers',    fmt(data.drivers ?? 0));
      setText('statTotalPassengers', fmt(data.passengers ?? 0));
      setText('statTotalRides',      fmt(data.rides ?? 0));
      setText('statTodayRides',      fmt(data.corporate_rides ?? 0));

      // Status breakdown cells
      setText('statUnassigned', fmt(data.unassigned ?? 0));
      setText('statAssigned',   fmt(data.assigned ?? 0));
      setText('statOnTrip',     fmt(0));
      setText('statScheduled',  fmt(data.scheduled ?? 0));
      setText('statCompleted',  fmt(data.completed ?? 0));
      setText('statCancelled',  fmt(data.cancelled ?? 0));

      // Charts
      renderTrendChart(Array.isArray(data.rides_last_7_days) ? data.rides_last_7_days : []);
      renderStatusChart({ ...data, on_trip: 0 });
      renderDriversChart(data.drivers_status || {}, data.drivers ?? 0);
      renderPerformance(data);
    } catch (err) {
      console.error('Failed to load dashboard stats:', err);
    } finally {
      if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
    }
  }

  document.addEventListener('DOMContentLoaded', loadDashboardStats);
</script>