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
