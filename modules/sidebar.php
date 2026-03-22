<div class="sidebar d-flex flex-column p-3" style="z-index:1031; background:#fff; border-right:1px solid #EBEBEB; height:100vh; width:230px;">
  <div class="d-flex align-items-center justify-content-center px-2" style="height:50px; flex-shrink:0;">
    <img
      src="/assets/powercabs-logo-black.svg"
      alt="Navigation Logo"
      class="img-fluid"
      style="max-height:60px;"
    />
  </div>

  <ul class="nav flex-column mt-3 gap-1" id="sidebarMenu" style="flex:1;">
    <li class="nav-item">
      <a href="home.php" class="nav-link sidebar-link d-flex align-items-center gap-2 fw-semibold text-body rounded-2 px-3 py-1"
        style="font-size:0.92rem; color:#52525B; transition:all 0.15s;"
        onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
        onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
        <span class="d-flex align-items-center justify-content-center" style="width:30px; height:30px; background:#F4F4F5; border-radius:7px; flex-shrink:0; font-size:18px;">
          <i class="bi bi-speedometer2"></i>
        </span>
        Dashboard
      </a>
    </li>

    <li class="nav-item">
      <a href="preorder.php" class="nav-link sidebar-link d-flex align-items-center gap-2 fw-semibold text-body rounded-2 px-3 py-1"
        style="font-size:0.92rem; color:#52525B; transition:all 0.15s;"
        onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
        onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
        <span class="d-flex align-items-center justify-content-center" style="width:30px; height:30px; background:#F4F4F5; border-radius:7px; flex-shrink:0; font-size:18px;">
          <i class="bi bi-lightning-charge"></i>
        </span>
        Live Orders
      </a>
    </li>

    <li class="nav-item">
      <a href="corporate.php" class="nav-link sidebar-link d-flex align-items-center gap-2 fw-semibold text-body rounded-2 px-3 py-1"
        style="font-size:0.92rem; color:#52525B; transition:all 0.15s;"
        onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
        onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
        <span class="d-flex align-items-center justify-content-center" style="width:30px; height:30px; background:#F4F4F5; border-radius:7px; flex-shrink:0; font-size:18px;">
          <i class="bi bi-taxi-front"></i>
        </span>
        App Ride
      </a>
    </li>

    <li class="nav-item">
      <a href="profile.php" class="nav-link sidebar-link d-flex align-items-center gap-2 fw-semibold text-body rounded-2 px-3 py-1"
        style="font-size:0.92rem; color:#52525B; transition:all 0.15s;"
        onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
        onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
        <span class="d-flex align-items-center justify-content-center" style="width:30px; height:30px; background:#F4F4F5; border-radius:7px; flex-shrink:0; font-size:18px;">
          <i class="bi bi-person-lines-fill"></i>
        </span>
        Profile Setting
      </a>
    </li>

  </ul>

  <div class="px-2 pt-3" style="border-top:1px solid #EBEBEB; flex-shrink:0;">
    <div class="d-flex align-items-center gap-2 px-1 py-2">
      <span class="d-flex align-items-center justify-content-center" style="width:7px; height:7px; background:#22C55E; border-radius:50%; flex-shrink:0;"></span>
      <span style="font-size:0.72rem; color:#A1A1AA; font-weight:500;">System Online</span>
    </div>
  </div>

</div>

<!-- INLINE SCRIPT (local to sidebar only) -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const currentPage = window.location.pathname.split("/").pop();

    document.querySelectorAll("#sidebarMenu .sidebar-link").forEach(link => {
        const linkPage = link.getAttribute("href");

        if (linkPage === currentPage) {
            // Remove previous active classes
            document.querySelectorAll("#sidebarMenu .sidebar-link").forEach(l => {
                l.classList.remove("active");
                l.style.color = "";
                l.style.backgroundColor = "";
            });

            // Apply the same active UI as Instant tab
            link.classList.add("active");
            link.style.color = "#f37a20";
            link.style.boxShadow = "0 0 5px rgba(243, 122, 32, 0.5)";
            link.style.backgroundColor = "#fff2e9";
        }
    });
});
</script>

<!-- INLINE CSS (only for sidebar styling, optional) -->
<style>
.sidebar a.active i {
  color: #f37a20 !important;
}
.sidebar-link.active {
  color: #f37a20 !important;
}
</style>
