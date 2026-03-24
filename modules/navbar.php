<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');

$page_titles = [
    'home'            => 'Dashboard',
    'fleetRegistry'   => 'Fleet Registry',
    'corporate_rides'       => 'Corporate Rides',
    'application_rides'       => 'Application Rides',
    'order'           => 'Create New Order',
    'map'             => 'Live Map',
    'orderassigned'   => 'Assigned Orders',
    'preorder'        => 'Live Orders',
    'profile'         => 'Profile Management',
];

$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'John Doe';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'john@powercabs.com';
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : '';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top px-4" style="height:52px; box-shadow: 0 1px 0 #EBEBEB, 0 4px 20px rgba(0,0,0,0.05); z-index:1030;">
  <div class="d-flex align-items-center w-100 gap-2">

    <button
      class="btn p-0 d-flex align-items-center justify-content-center d-md-none"
      type="button"
      id="sidebarToggle"
      style="width:36px; height:36px; border:none; background:transparent; color:#71717A; border-radius:8px;"
    >
      <span class="navbar-toggler-icon" style="width:18px; height:18px;"></span>
    </button>

    <div class="d-flex align-items-center gap-2">
      <h5 class="m-0 fw-bold navbar-title ms-lg-230" id="pageTitle" style="font-size:1.3rem; color:#18181B; letter-spacing:-0.01em; white-space:nowrap;">
        <?php echo htmlspecialchars($page_title); ?>
      </h5>
    </div>


    <div class="dropdown ms-auto">
      <div
        class="d-flex align-items-center gap-2 text-decoration-none"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        style="cursor:pointer; padding:5px 10px 5px 5px; border-radius:100px; border:1.5px solid transparent; transition:all 0.15s;"
        onmouseover="this.style.background='#FAFAFA'; this.style.borderColor='#EBEBEB';"
        onmouseout="this.style.background='transparent'; this.style.borderColor='transparent';"
      >
        <div class="position-relative" style="width:34px; height:34px; flex-shrink:0;">
          <img
            id="navbarAvatarImg"
            src=""
            alt="Profile"
            class="rounded-circle position-absolute top-0 start-0"
            style="width:34px; height:34px; object-fit:cover; display:none; z-index:2;"
          />
          <div
            id="navbarAvatarInitials"
            class="d-flex align-items-center justify-content-center rounded-circle position-absolute top-0 start-0"
            style="width:34px; height:34px; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; font-size:12px; font-weight:700; z-index:1; letter-spacing:0.03em;"
          >
            <?php
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

        <div class="d-none d-md-flex flex-column overflow-hidden" style="line-height:1.25;">
          <span id="navbarUserName" class="fw-semibold text-truncate" style="font-size:0.8125rem; color:#18181B; max-width:120px;">
            <?php echo htmlspecialchars($user_name); ?>
          </span>
          <span style="font-size:0.6875rem; color:#A1A1AA; font-weight:500;">Dispatcher</span>
        </div>

        <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:11px; color:#A1A1AA;"></i>
      </div>

      <ul class="dropdown-menu dropdown-menu-end mt-1 p-1" style="min-width:210px; border:1px solid #EBEBEB; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.10);">
        <li>
          <div class="px-3 py-2 mb-1" style="border-bottom:1px solid #EBEBEB;">
            <div class="fw-bold" style="font-size:0.875rem; color:#18181B;"><?php echo htmlspecialchars($user_name); ?></div>
            <div style="font-size:0.72rem; color:#A1A1AA;"><?php echo htmlspecialchars($user_email ?? ''); ?></div>
          </div>
        </li>
        <li>
          <a class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2" href="profile.php"
            style="font-size:0.8375rem; font-weight:500; color:#52525B;"
            onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
            onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
            <i class="bi bi-person-circle" style="font-size:15px;"></i>My Profile
          </a>
        </li>
        <li><hr class="dropdown-divider my-1" style="border-color:#EBEBEB;"></li>
        <li>
          <a id="logoutBtn" class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2" href="#"
            style="font-size:0.8375rem; font-weight:500; color:#52525B;"
            onmouseover="this.style.background='#FFF1F2'; this.style.color='#E11D48';"
            onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
            <i class="bi bi-box-arrow-right" style="font-size:15px;"></i>Sign Out
          </a>
        </li>
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
      if (res.status === 401) {
        const nameEl = document.getElementById('navbarUserName');
        if (nameEl) {
          nameEl.textContent = 'Session expired';
          nameEl.classList.add('text-danger');
        }
        window.location.href = '/';
        return;
      }
      const json = await res.json();
      if (!json.success && json.error && (json.error.toLowerCase().includes('session') || json.error.toLowerCase().includes('log in'))) {
        const nameEl = document.getElementById('navbarUserName');
        if (nameEl) {
          nameEl.textContent = 'Session expired';
          nameEl.classList.add('text-danger');
        }
        window.location.href = '/';
        return;
      }
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