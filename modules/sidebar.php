<?php
  require_once __DIR__ . '/../auth/role_guard.php';
  $isDispatcher = isDispatcherRole();
?>
<div class="sidebar d-flex flex-column" style="z-index:1031; background:#0B1220; border-right:1px solid #1E293B; height:100vh; width:240px; color:#CBD5E1;">

  <!-- <div class="d-flex align-items-center justify-content-center" style="height:64px; flex-shrink:0; border-bottom:1px solid #1E293B;">
    <img
      src="/assets/powercabs-logo-black.svg"
      alt="PowerCabs"
      class="img-fluid sidebar-logo"
      style="max-height:60px;"
    />
  </div> -->
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
      <div class="sidebar-section-label">Overview</div>
      <ul class="nav flex-column gap-1" id="sidebarMenu-overview">
        <?php if (!$isDispatcher): ?>
        <li class="nav-item">
          <a href="home.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-house-door"></i></span>
            <span class="sidebar-label">Home</span>
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a href="fleetRegistry.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sidebar-label">Fleet Registry</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Operations</div>
      <ul class="nav flex-column gap-1" id="sidebarMenu-ops">
        <li class="nav-item">
          <a href="order.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-plus-circle"></i></span>
            <span class="sidebar-label">Create Order</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="preorder.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-lightning-charge"></i></span>
            <span class="sidebar-label">Live Orders</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="map.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-map"></i></span>
            <span class="sidebar-label">Live Map</span>
          </a>
        </li>
      </ul>
    </div>

    <?php if (!$isDispatcher): ?>
    <div class="sidebar-section">
      <div class="sidebar-section-label">Rides</div>
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
    <?php endif; ?>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Account</div>
      <ul class="nav flex-column gap-1" id="sidebarMenu-account">
        <li class="nav-item">
          <a href="profile.php" class="nav-link sidebar-link">
            <span class="sidebar-icon"><i class="bi bi-gear"></i></span>
            <span class="sidebar-label">Settings</span>
          </a>
        </li>
      </ul>
    </div>

  </div>

  <div class="px-3 py-3" style="border-top:1px solid #1E293B; flex-shrink:0;">
    <div class="d-flex align-items-center gap-2">
      <span class="sidebar-status-dot"></span>
      <span style="font-size:0.72rem; color:#94A3B8; font-weight:500; letter-spacing:0.02em;">System Online</span>
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
