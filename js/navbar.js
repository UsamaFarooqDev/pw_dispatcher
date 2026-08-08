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
        const name = json.data.name || 'User';
        const email = json.data.email || '';
        const nameEl = document.getElementById('navbarUserName');
        const dropdownNameEl = document.getElementById('dropdownUserName');
        const dropdownEmailEl = document.getElementById('dropdownUserEmail');
        const greetingEl = document.getElementById('homeGreetingFirstName');
        const avatarImg = document.getElementById('navbarAvatarImg');
        const avatarInitials = document.getElementById('navbarAvatarInitials');

        if (nameEl) nameEl.textContent = name;
        if (dropdownNameEl) dropdownNameEl.textContent = name;
        if (dropdownEmailEl) dropdownEmailEl.textContent = email;
        if (greetingEl) greetingEl.textContent = (name.split(' ')[0] || 'Dispatcher');

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
