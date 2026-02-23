<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');

$page_titles = [
    'home'            => 'Home',
    'corporate'       => 'Corporate Rides',
    'order'           => 'Create New Order',
    'map'             => 'Live Map',
    'orderassigned'   => 'Assigned Orders',
    'preorder'        => 'Pre-Scheduled Orders',
    'profile'         => 'Profile Management',
];

$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'John Doe';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'john@powercabs.com';
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : '';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white d-flex align-items-center justify-content-between px-3 py-2.5 sticky-top" style="z-index: 1030; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
  <div class="d-flex align-items-center w-100">
    <button
      class="navbar-toggler me-2 d-md-none btn p-1"
      type="button"
      id="sidebarToggle"
      style="border: none; outline: none; background: transparent;"
    >
      <span class="navbar-toggler-icon" style="width: 18px; height: 18px;"></span>
    </button>

    <h5 class="navbar-title m-0 fw-bold ms-lg-230" id="pageTitle">
      <?php echo htmlspecialchars($page_title); ?>
    </h5>

    <div class="dropdown ms-auto">
      <div 
        class="d-flex align-items-center text-decoration-none" 
        data-bs-toggle="dropdown" 
        aria-expanded="false"
        style="cursor: pointer; min-width: 0;"
      >
        <div id="navbarAvatarContainer" class="position-relative" style="width: 32px; height: 32px; flex-shrink: 0;">
          <img
            id="navbarAvatarImg"
            src=""
            alt="Profile"
            class="rounded-circle position-absolute top-0 start-0"
            style="width: 32px; height: 32px; object-fit: cover; display: none; z-index: 2;"
          />
          <div
            id="navbarAvatarInitials"
            class="d-flex align-items-center justify-content-center rounded-circle position-absolute top-0 start-0"
            style="
              width: 32px;
              height: 32px;
              background-color: #f37a20;
              color: white;
              font-size: 12px;
              font-weight: bold;
              z-index: 1;
            "
          >
            <?php 
              // Fallback initials from session or default
              $initials = 'JD';
              if (!empty($user_name)) {
                $parts = explode(' ', trim($user_name));
                if (count($parts) >= 2) {
                  $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
                } else {
                  $initials = strtoupper(substr($user_name, 0, 2));
                }
              } elseif (!empty($user_email)) {
                $initials = strtoupper(substr($user_email, 0, 2));
              }
              echo htmlspecialchars($initials);
            ?>
          </div>
        </div>
        <div class="d-none d-md-flex flex-column ms-2 me-2 overflow-hidden">
          <span id="navbarUserName" class="fw-bold text-truncate" style="font-size: 0.875rem;">
            <?php echo htmlspecialchars($user_name); ?>
          </span>
        </div>
        <i class="bi bi-chevron-down text-muted d-none d-md-inline"></i>
      </div>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><hr class="dropdown-divider"></li>
         <li><a id="logoutBtn" class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebarToggle = document.getElementById('sidebarToggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
      }
    });
  }

  const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  const sidebar = document.querySelector('.sidebar');
  if (sidebar && sidebarCollapsed) {
    sidebar.classList.add('collapsed');
  }
  
  // Load user profile data for navbar
  async function loadNavbarProfile() {
    try {
      const res = await fetch('api/get_profile.php');
      const json = await res.json();
      
      if (json.success && json.data) {
        // Update name and email
        const nameEl = document.getElementById('navbarUserName');
        const emailEl = document.getElementById('navbarUserEmail');
        const avatarImg = document.getElementById('navbarAvatarImg');
        const avatarInitials = document.getElementById('navbarAvatarInitials');
        
        if (nameEl) nameEl.textContent = json.data.name || 'User';
        if (emailEl) emailEl.textContent = json.data.email || '';
        
        // Update avatar
        if (json.data.profile_image) {
          if (avatarImg) {
            avatarImg.src = json.data.profile_image;
            avatarImg.style.display = 'block';
          }
          if (avatarInitials) avatarInitials.style.display = 'none';
        } else {
          // Show initials
          if (avatarImg) avatarImg.style.display = 'none';
          if (avatarInitials) {
            const name = json.data.name || json.data.email || 'User';
            const initials = getInitials(name);
            avatarInitials.textContent = initials;
            avatarInitials.style.display = 'flex';
          }
        }
      }
    } catch (err) {
      console.error('Error loading navbar profile:', err);
    }
  }
  
  // Get initials from name
  function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
  
  // Load profile data on page load
  loadNavbarProfile();
  
  // Logout handler
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async function (e) {
      e.preventDefault();
      try {
        const res = await fetch('auth/logout.php', { method: 'POST' });
        const json = await res.json();
        if (json.success) {
          window.location.href = 'login.php';
        } else {
          const msgEl = document.getElementById('msg');
          if (msgEl) msgEl.innerText = json.message || 'Logout failed';
        }
      } catch (err) {
        const msgEl = document.getElementById('msg');
        if (msgEl) msgEl.innerText = 'Network error';
      }
    });
  }
});
</script>