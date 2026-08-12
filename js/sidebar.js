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
