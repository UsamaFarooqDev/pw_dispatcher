<div class="sidebar d-flex flex-column" style="z-index:1031; background:#0B1220; border-right:1px solid #1E293B; height:100vh; width:240px; color:#CBD5E1;">

  <div class="d-flex align-items-center justify-content-center" style="flex-shrink:0; border-bottom:1px solid #1E293B; padding: 10px 0;">
  <img
    src="/assets/powercabs-logo-black.svg"
    alt="PowerCabs"
    class="img-fluid sidebar-logo"
    style="max-height:55px;"
  />
</div>

  <div class="d-flex flex-column px-2 py-3" style="flex:1; overflow-y:auto;">

    <div class="sidebar-section">
      <ul class="nav flex-column gap-1" id="sidebarMenu-overview">
        <li class="nav-item">
          <a href="home.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-house-door"></i></span>
            <span class="sidebar-label">Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="fleetRegistry.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sidebar-label">Fleet Registry</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-section">
      <ul class="nav flex-column gap-1" id="sidebarMenu-ops">
        <li class="nav-item">
          <a href="order.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-plus-circle"></i></span>
            <span class="sidebar-label">Create Order</span>
          </a>
        </li>
        <li class="nav-item sidebar-dropdown-item" id="sidebarLiveOrdersItem">
          <button type="button" class="nav-link sidebar-link sidebar-dropdown-toggle" id="sidebarLiveOrdersToggle" aria-expanded="false" aria-controls="sidebarLiveOrdersSubmenu">
            <span class="sidebar-icon"><i class="bi bi-lightning-charge"></i></span>
            <span class="sidebar-label">Live Orders</span>
            <i class="bi bi-chevron-down sidebar-dropdown-caret"></i>
          </button>
          <ul class="sidebar-submenu" id="sidebarLiveOrdersSubmenu">
            <li><a href="liveorder.php#tab-unassigned" class="sidebar-sublink" data-tab="tab-unassigned">
              <span class="sidebar-sublink-label">Unassigned</span>
              <span class="sidebar-sublink-count is-loading" id="count-unassigned"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-assigned" class="sidebar-sublink" data-tab="tab-assigned">
              <span class="sidebar-sublink-label">Assigned</span>
              <span class="sidebar-sublink-count is-loading" id="count-assigned"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-enroute" class="sidebar-sublink" data-tab="tab-enroute">
              <span class="sidebar-sublink-label">Enroute</span>
              <span class="sidebar-sublink-count is-loading" id="count-enroute"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-scheduled" class="sidebar-sublink" data-tab="tab-scheduled">
              <span class="sidebar-sublink-label">Pre-Order</span>
              <span class="sidebar-sublink-count is-loading" id="count-scheduled"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-cancelled" class="sidebar-sublink" data-tab="tab-cancelled">
              <span class="sidebar-sublink-label">Cancelled</span>
              <span class="sidebar-sublink-count is-loading" id="count-cancelled"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-finished" class="sidebar-sublink" data-tab="tab-finished">
              <span class="sidebar-sublink-label">Completed</span>
              <span class="sidebar-sublink-count is-loading" id="count-finished"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
            <li><a href="liveorder.php#tab-meet-greet" class="sidebar-sublink" data-tab="tab-meet-greet">
              <span class="sidebar-sublink-label">Meet &amp; Greet</span>
              <span class="sidebar-sublink-count is-loading" id="count-meet-greet"><span class="sb-count-dots"><i></i><i></i><i></i></span></span>
            </a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="map.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-map"></i></span>
            <span class="sidebar-label">Live Map</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-section">
      <ul class="nav flex-column gap-1" id="sidebarMenu-rides">
        <li class="nav-item">
          <a href="application_rides.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-phone"></i></span>
            <span class="sidebar-label">App Rides</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="corporate_rides.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-buildings"></i></span>
            <span class="sidebar-label">Corporate Rides</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar .sidebar-link").forEach(link => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
  // The Live Orders item is a dropdown toggle now (no href), so it needs its
  // own active-page check.
  if (currentPage === "liveorder.php") {
    document.getElementById("sidebarLiveOrdersToggle")?.classList.add("active");
  }

  // Live Orders dropdown — starts collapsed on every page load; only opens
  // on click. Sub-tab links carry their own #tab-id href (liveorder.php picks
  // it up on load, or via the hashchange listener if already on the page).
  const loToggle = document.getElementById("sidebarLiveOrdersToggle");
  const loItem = document.getElementById("sidebarLiveOrdersItem");
  if (loToggle && loItem) {
    loToggle.addEventListener("click", function () {
      const isOpen = loItem.classList.toggle("open");
      loToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  }
  document.querySelectorAll(".sidebar-sublink").forEach(link => {
    link.addEventListener("click", function () {
      document.querySelectorAll(".sidebar-sublink.active").forEach(a => a.classList.remove("active"));
      link.classList.add("active");
    });
  });
});
</script>

<style>
  .sidebar { font-family: inherit; }

  .sidebar-logo {
    filter: brightness(0) invert(1);
    opacity: 0.95;
  }

  .sidebar-section { margin-bottom: 14px; }
  .sidebar-section:last-child { margin-bottom: 0; }

  .sidebar-section-label {
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #64748B;
    padding: 6px 14px 8px;
  }

  .sidebar .sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #CBD5E1;
    text-decoration: none;
    position: relative;
    transition: background 0.15s ease, color 0.15s ease;
  }

  .sidebar .sidebar-link:hover {
    background: #111C2E;
    color: #F1F5F9;
  }

  .sidebar .sidebar-link .sidebar-icon {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #111C2E;
    border: 1px solid #1E293B;
    border-radius: 7px;
    flex-shrink: 0;
    font-size: 15px;
    color: #94A3B8;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  }

  .sidebar .sidebar-link:hover .sidebar-icon {
    color: #f37a20;
    border-color: #2A3A55;
  }

  .sidebar .sidebar-link.active {
    background: linear-gradient(90deg, rgba(243,122,32,0.14) 0%, rgba(243,122,32,0.04) 100%);
    color: #fff;
    font-weight: 600;
  }

  .sidebar .sidebar-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    bottom: 6px;
    width: 3px;
    background: #f37a20;
    border-radius: 0 3px 3px 0;
    box-shadow: 0 0 10px rgba(243,122,32,0.55);
  }

  .sidebar .sidebar-link.active .sidebar-icon {
    background: rgba(243,122,32,0.16);
    border-color: rgba(243,122,32,0.35);
    color: #f37a20;
  }

  /* ── Live Orders dropdown ─────────────────────────────────────────── */
  .sidebar-dropdown-toggle {
    width: 100%;
    background: transparent;
    border: none;
    cursor: pointer;
  }
  .sidebar-dropdown-caret {
    margin-left: auto;
    font-size: 12px;
    color: #64748B;
    transition: transform 0.2s ease, color 0.15s ease;
    flex-shrink: 0;
  }
  .sidebar-dropdown-toggle:hover .sidebar-dropdown-caret { color: #94A3B8; }
  .sidebar-dropdown-item.open .sidebar-dropdown-caret { transform: rotate(180deg); }
  .sidebar-dropdown-item.open .sidebar-dropdown-toggle .sidebar-icon {
    color: #f37a20;
    border-color: rgba(243,122,32,0.35);
  }

  .sidebar-submenu {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.22s ease;
  }
  .sidebar-dropdown-item.open .sidebar-submenu {
    max-height: 400px;
  }

  .sidebar-sublink {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 7px 12px 7px 38px;
    margin-top: 2px;
    border-radius: 7px;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #94A3B8;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .sidebar-sublink:hover { background: #111C2E; color: #F1F5F9; }
  .sidebar-sublink.active {
    background: rgba(243,122,32,0.12);
    color: #fff;
    font-weight: 600;
  }
  .sidebar-sublink-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 17px;
    padding: 0 5px;
    background: #111C2E;
    border: 1px solid #1E293B;
    color: #94A3B8;
    font-size: 0.68rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    border-radius: 9px;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  }
  .sidebar-sublink.active .sidebar-sublink-count {
    background: rgba(243,122,32,0.18);
    border-color: rgba(243,122,32,0.35);
    color: #f37a20;
  }

  /* Tiny dotted loader shown in a count badge until its real number arrives */
  .sidebar-sublink-count.is-loading { min-width: 22px; padding: 0 6px; }
  .sb-count-dots { display: inline-flex; align-items: center; gap: 2px; }
  .sb-count-dots i {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #64748B;
    display: inline-block;
    animation: sbCountDotPulse 1.1s ease-in-out infinite;
  }
  .sb-count-dots i:nth-child(2) { animation-delay: 0.15s; }
  .sb-count-dots i:nth-child(3) { animation-delay: 0.3s; }
  @keyframes sbCountDotPulse {
    0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
    40% { opacity: 1; transform: scale(1); }
  }

  .sidebar-status-dot {
    width: 8px;
    height: 8px;
    background: #22C55E;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.15);
    animation: sidebarPulse 2s ease-in-out infinite;
  }

  @keyframes sidebarPulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(34,197,94,0.15); }
    50%      { box-shadow: 0 0 0 5px rgba(34,197,94,0.05); }
  }

  /* Thin custom scrollbar for dark sidebar */
  .sidebar > div::-webkit-scrollbar { width: 6px; }
  .sidebar > div::-webkit-scrollbar-track { background: transparent; }
  .sidebar > div::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 3px; }
  .sidebar > div::-webkit-scrollbar-thumb:hover { background: #334155; }
</style>
