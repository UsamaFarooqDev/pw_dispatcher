document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar .sidebar-link").forEach((link) => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
  if (currentPage === "liveorder.php") {
    document.getElementById("sidebarLiveOrdersToggle")?.classList.add("active");
  }

  const LO_OPEN_KEY = "sidebarLiveOrdersOpen";
  const loToggle = document.getElementById("sidebarLiveOrdersToggle");
  const loItem = document.getElementById("sidebarLiveOrdersItem");
  if (loToggle && loItem) {
    const wasOpen = localStorage.getItem(LO_OPEN_KEY) === "true";
    if (wasOpen) {
      loItem.classList.add("open");
      loToggle.setAttribute("aria-expanded", "true");
    }
    loToggle.addEventListener("click", function () {
      const isOpen = loItem.classList.toggle("open");
      loToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      localStorage.setItem(LO_OPEN_KEY, isOpen ? "true" : "false");
    });
  }
  document.querySelectorAll(".sidebar-sublink").forEach((link) => {
    link.addEventListener("click", function () {
      document.querySelectorAll(".sidebar-sublink.active").forEach((a) => a.classList.remove("active"));
      link.classList.add("active");
    });
  });
});
