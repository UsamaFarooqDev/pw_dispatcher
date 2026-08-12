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
      </ul>
    </div>
  </div>
</div>

<script src="js/sidebar.js"></script>
