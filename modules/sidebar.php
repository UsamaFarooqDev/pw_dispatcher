<div class="sidebar text-white p-3" style="z-index: 1031">
  <div class="d-flex flex-column h-100">
    <div class="sidebar-header mb-10 d-flex justify-content-center align-items-center" style="height: 60px">
      <img
        src="/assets/powercabs-logo-black.svg"
        alt="Navigation Logo"
        class="img-fluid"
        style="max-height: 75px"
      />
    </div>

    <ul class="nav nav-pills flex-column mt-4" id="sidebarMenu">
      <li class="nav-item mb-2">
        <a href="home.php" class="nav-link sidebar-link d-flex align-items-center py-2 fw-semibold text-body">
          <i class="bi bi-houses fs-6 me-2"></i> Dashboard
        </a>
      </li>

      <li class="nav-item mb-2">
        <a href="preorder.php" class="nav-link sidebar-link d-flex align-items-center py-2 fw-semibold text-body">
          <i class="bi bi-lightning-charge fs-6 me-2"></i> Pre Order
        </a>
      </li>

      <!-- <li class="nav-item">
        <a href="orderassigned.php" class="nav-link sidebar-link d-flex align-items-center py-2 fw-semibold text-body">
          <i class="bi bi-clipboard-check fs-6 me-2"></i> Order Assign
        </a>
      </li> -->

      <li class="nav-item mb-2">
        <a href="corporate.php" class="nav-link sidebar-link d-flex align-items-center py-2 fw-semibold text-body">
          <i class="bi bi-taxi-front fs-6 me-2"></i> Corporate Ride
        </a>
      </li>

        <li class="nav-item mb-2">
        <a href="profile.php" class="nav-link sidebar-link d-flex align-items-center py-2 fw-semibold text-body">
          <i class="bi bi-person-lines-fill fs-6 me-2"></i> Profile Setting
        </a>
      </li>
    </ul>
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
