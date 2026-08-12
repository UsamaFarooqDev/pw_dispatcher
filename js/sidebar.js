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
