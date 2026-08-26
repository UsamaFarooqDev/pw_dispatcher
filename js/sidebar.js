// Highlights whichever sidebar link matches the current page. The sidebar
// itself is part of the persistent shell (never replaced by SPA
// navigation, see js/spa-navigation.js), so this has to both set the new
// active link AND clear any previous one — on a real page load there's
// only ever one to begin with, but called again after an in-page
// navigation it would otherwise leave every visited page's link stuck
// "active" at once. Exposed on window so the router can call it again
// after every swap, since location.pathname only reflects the new page
// once history.pushState() has run.
function highlightActiveSidebarLink() {
  const currentPage = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar .sidebar-link.active").forEach((link) => {
    link.classList.remove("active");
  });
  document.querySelectorAll(".sidebar .sidebar-link").forEach((link) => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
  document.getElementById("sidebarLiveOrdersToggle")?.classList.toggle("active", currentPage === "liveorder.php");
}
window.highlightActiveSidebarLink = highlightActiveSidebarLink;

document.addEventListener("DOMContentLoaded", function () {
  highlightActiveSidebarLink();

  // Mobile sidebar toggle. This used to live only in js/fleetRegistry.js —
  // a page-specific script that only loads once the dispatcher has visited
  // Fleet Registry — so the toggle button did nothing on every other page
  // until then. sidebar.js is part of the persistent shell (loaded once,
  // on every page), so binding it here makes the toggle reliable from the
  // very first load, everywhere.
  const sidebarToggleBtn = document.getElementById("sidebarToggle");
  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener("click", function () {
      document.querySelector(".sidebar")?.classList.toggle("active");
    });
  }
  document.addEventListener("click", function (event) {
    const sidebarEl = document.querySelector(".sidebar");
    if (
      window.innerWidth < 768 &&
      sidebarEl &&
      sidebarEl.classList.contains("active") &&
      !event.target.closest(".sidebar") &&
      !event.target.closest("#sidebarToggle")
    ) {
      sidebarEl.classList.remove("active");
    }
  });

  // Close the mobile drawer when a nav link is actually followed. Under
  // SPA navigation (js/spa-navigation.js) the sidebar is part of the
  // persistent shell and never reloads, so without this it stayed open
  // (covering the new page) after navigating on mobile. Scoped to real
  // <a> links only — a.sidebar-link excludes the Live Orders <button>
  // dropdown toggle (which also carries the .sidebar-link class but
  // should just expand/collapse the submenu, not close the drawer).
  document.querySelectorAll(".sidebar a.sidebar-link, .sidebar-sublink").forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth < 768) {
        document.querySelector(".sidebar")?.classList.remove("active");
      }
    });
  });

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
