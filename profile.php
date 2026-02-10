<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>
    
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background: #f5f7fa">
      <div class="container">
        <div class="card shadow border-0" style="border-radius: 25px">
          <div class="card-body p-5">
            <!-- Increased padding -->
            <h3 class="fw-semibold mb-5">Profile Settings</h3>

            <!-- Full-Width Profile Section -->
            <div class="row g-4 mb-5">
              <div
                class="col-12 d-flex flex-column align-items-center text-center"
              >
                <!-- Profile Image with Emoji Background -->
                <div
                  class="position-relative mb-4"
                  style="width: 130px; height: 130px"
                >
                  <!-- Initials background (shown when no profile image) -->
                  <div
                    id="profileInitials"
                    class="d-flex align-items-center justify-content-center rounded-circle"
                    style="
                      width: 100%;
                      height: 100%;
                      background-color: #f9ece1;
                      color: #f37a20;
                      font-size: 42px;
                      font-weight: bold;
                    "
                  >
                    👤
                  </div>
                  <!-- Profile image overlay -->
                  <img
                    id="profilePreview"
                    src=""
                    alt=""
                    class="rounded-circle position-absolute top-0 start-0"
                    style="
                      width: 100%;
                      height: 100%;
                      object-fit: cover;
                      border: 3px solid #fff;
                      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                    "
                  />
                  <!-- Upload input (hidden) -->
                  <input
                    type="file"
                    id="profileImage"
                    accept="image/*"
                    class="d-none"
                  />
                  
                  <!-- Camera button with dropdown when avatar exists -->
                  <div class="dropdown position-absolute bottom-0 end-0" style="z-index: 10;">
                    <button
                      id="cameraBtn"
                      class="btn btn-sm rounded-circle shadow-sm"
                      type="button"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                      style="
                        background: #f37a20;
                        color: white;
                        width: 36px;
                        height: 36px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 0;
                        cursor: pointer;
                        border: 2px solid #fff;
                      "
                    >
                      <i class="bi bi-camera" style="font-size: 16px"></i>
                    </button>
                    <ul id="cameraDropdown" class="dropdown-menu dropdown-menu-end" style="display: none;">
                      <li>
                        <label
                          for="profileImage"
                          class="dropdown-item"
                          style="cursor: pointer;"
                        >
                          <i class="bi bi-upload me-2"></i>Update Picture
                        </label>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a
                          id="removeAvatarBtn"
                          class="dropdown-item text-danger"
                          href="#"
                          style="cursor: pointer;"
                        >
                          <i class="bi bi-trash me-2"></i>Remove Picture
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Name & Email Full-Width Card -->
                <div
                  class="w-100 bg-light rounded-4 p-4"
                  style="background: #fafafa; border: 1px solid #eee"
                >
                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <div class="text-muted small">Full Name</div>
                      <div class="fw-semibold fs-5" id="userName">Loading...</div>
                    </div>
                    <div class="col-12 col-md-6">
                      <div class="text-muted small">Email Address</div>
                      <div class="fw-semibold fs-5" id="userEmail">Loading...</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Password Section - Two Rows, Two Columns -->
            <div class="row g-4 mb-5">
              <div class="col-12">
                <h5 class="fw-semibold mb-4">Change Password</h5>
              </div>

              <div class="col-12 col-md-6">
                <label
                  for="currentPassword"
                  class="form-label small fw-medium text-muted"
                  >Current Password</label
                >
                <div class="position-relative">
                  <input
                    type="password"
                    class="form-control py-2 pe-5"
                    id="currentPassword"
                    style="
                      border-radius: 5px;
                      padding-left: 14px;
                      border: 1px solid #ddd;
                    "
                  />
                  <button
                    type="button"
                    class="btn position-absolute end-0 top-50 translate-middle-y me-3"
                    style="
                      z-index: 10;
                      padding: 0;
                      background: none;
                      border: none;
                    "
                    onclick="togglePassword('currentPassword')"
                  >
                    <i
                      class="bi bi-eye-slash text-muted"
                      id="currentPasswordIcon"
                    ></i>
                  </button>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label
                  for="newPassword"
                  class="form-label small fw-medium text-muted"
                  >New Password</label
                >
                <div class="position-relative">
                  <input
                    type="password"
                    class="form-control py-2 pe-5"
                    id="newPassword"
                    style="
                      border-radius: 5px;
                      padding-left: 14px;
                      border: 1px solid #ddd;
                    "
                  />
                  <button
                    type="button"
                    class="btn position-absolute end-0 top-50 translate-middle-y me-3"
                    style="
                      z-index: 10;
                      padding: 0;
                      background: none;
                      border: none;
                    "
                    onclick="togglePassword('newPassword')"
                  >
                    <i
                      class="bi bi-eye-slash text-muted"
                      id="newPasswordIcon"
                    ></i>
                  </button>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label
                  for="confirmPassword"
                  class="form-label small fw-medium text-muted"
                  >Confirm New Password</label
                >
                <div class="position-relative">
                  <input
                    type="password"
                    class="form-control py-2 pe-5"
                    id="confirmPassword"
                    style="
                      border-radius: 5px;
                      padding-left: 14px;
                      border: 1px solid #ddd;
                    "
                  />
                  <button
                    type="button"
                    class="btn position-absolute end-0 top-50 translate-middle-y me-3"
                    style="
                      z-index: 10;
                      padding: 0;
                      background: none;
                      border: none;
                    "
                    onclick="togglePassword('confirmPassword')"
                  >
                    <i
                      class="bi bi-eye-slash text-muted"
                      id="confirmPasswordIcon"
                    ></i>
                  </button>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <!-- Empty spacer to balance layout -->
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-3 mb-5">
              <button
                id="savePasswordBtn"
                class="btn fw-semibold px-4 py-1.5"
                style="background: #f37a20; color: white; border-radius: 5px"
              >
                Save Changes
              </button>
              <button
                id="cancelBtn"
                type="button"
                class="btn btn-outline-secondary fw-semibold px-4 py-1.5"
                style="border-radius: 5px"
              >
                Cancel
              </button>
            </div>

            <!-- Logout Section -->
            <div class="pt-4 border-top">
              <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
              >
                <div>
                  <h6 class="fw-semibold">Security</h6>
                  <p class="text-muted small mb-0">
                    Log out from all active sessions
                  </p>
                </div>
                <button
                  id="logoutBtnProfile"
                  class="btn fw-semibold px-4 py-1.5"
                  style="background: #f37a20; color: white; border-radius: 5px"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
      <div id="toastMsg" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastText"></div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Profile Script -->
    <script>
      // User profile data
      let userProfile = null;

      // Load user profile data on page load
      async function loadUserProfile() {
        try {
          const res = await fetch('api/get_profile.php');
          const json = await res.json();
          
          if (json.success && json.data) {
            userProfile = json.data;
            
            // Update name and email
            document.getElementById('userName').textContent = json.data.name || 'User';
            document.getElementById('userEmail').textContent = json.data.email || '';
            
            // Update profile image
            const profilePreview = document.getElementById('profilePreview');
            const initialsContainer = document.getElementById('profileInitials');
            const cameraBtn = document.getElementById('cameraBtn');
            const cameraDropdown = document.getElementById('cameraDropdown');
            const profileImageInput = document.getElementById('profileImage');
            
            if (json.data.profile_image) {
              profilePreview.src = json.data.profile_image;
              profilePreview.style.display = 'block';
              if (initialsContainer) initialsContainer.style.display = 'none';
              // Show dropdown when avatar exists
              if (cameraBtn) {
                cameraBtn.setAttribute('data-bs-toggle', 'dropdown');
                cameraBtn.setAttribute('aria-expanded', 'false');
                cameraBtn.onclick = null;
              }
              if (cameraDropdown) cameraDropdown.style.display = '';
            } else {
              // Show initials if no image
              const name = json.data.name || json.data.email || 'User';
              const initials = getInitials(name);
              if (initialsContainer) {
                initialsContainer.textContent = initials;
                initialsContainer.style.display = 'flex';
              }
              profilePreview.style.display = 'none';
              // Direct upload when no avatar exists
              if (cameraBtn) {
                cameraBtn.removeAttribute('data-bs-toggle');
                cameraBtn.removeAttribute('aria-expanded');
                cameraBtn.onclick = (e) => {
                  e.preventDefault();
                  e.stopPropagation();
                  profileImageInput.click();
                };
              }
              if (cameraDropdown) cameraDropdown.style.display = 'none';
            }
          } else {
            showToast('Failed to load profile data', false);
          }
        } catch (err) {
          console.error('Error loading profile:', err);
          showToast('Error loading profile data', false);
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

      // Function to update navbar avatar dynamically
      async function updateNavbarAvatar() {
        try {
          const res = await fetch('api/get_profile.php');
          const json = await res.json();
          
          if (json.success && json.data) {
            const navbarAvatarImg = document.getElementById('navbarAvatarImg');
            const navbarAvatarInitials = document.getElementById('navbarAvatarInitials');
            
            if (json.data.profile_image) {
              if (navbarAvatarImg) {
                navbarAvatarImg.src = json.data.profile_image;
                navbarAvatarImg.style.display = 'block';
              }
              if (navbarAvatarInitials) {
                navbarAvatarInitials.style.display = 'none';
              }
            } else {
              if (navbarAvatarImg) {
                navbarAvatarImg.style.display = 'none';
              }
              if (navbarAvatarInitials) {
                const name = json.data.name || json.data.email || 'User';
                const initials = getInitials(name);
                navbarAvatarInitials.textContent = initials;
                navbarAvatarInitials.style.display = 'flex';
              }
            }
          }
        } catch (err) {
          console.error('Error updating navbar avatar:', err);
        }
      }

      // Image preview and upload
      document.getElementById('profileImage').addEventListener('change', async function (e) {
        const file = e.target.files[0];
        const img = document.getElementById('profilePreview');
        const initialsContainer = document.getElementById('profileInitials');
        const cameraBtn = document.getElementById('cameraBtn');
        const cameraDropdown = document.getElementById('cameraDropdown');
        
        if (!file) return;
        
        // Show preview immediately
        const reader = new FileReader();
        reader.onload = () => {
          img.src = reader.result;
          img.style.display = 'block';
          if (initialsContainer) initialsContainer.style.display = 'none';
        };
        reader.readAsDataURL(file);
        
        // Upload file
        const formData = new FormData();
        formData.append('avatar', file);
        
        try {
          const res = await fetch('api/upload_avatar.php', {
            method: 'POST',
            body: formData
          });
          
          const json = await res.json();
          
          if (json.success) {
            showToast('Avatar uploaded successfully', true);
            // Update profile image URL
            if (userProfile) {
              userProfile.profile_image = json.image_url;
            }
            // Enable dropdown for camera button
            if (cameraBtn) {
              cameraBtn.setAttribute('data-bs-toggle', 'dropdown');
              cameraBtn.setAttribute('aria-expanded', 'false');
              cameraBtn.onclick = null;
            }
            if (cameraDropdown) cameraDropdown.style.display = '';
            // Update navbar without reload
            updateNavbarAvatar();
          } else {
            showToast(json.message || 'Failed to upload avatar', false);
            // Revert preview
            if (userProfile && userProfile.profile_image) {
              img.src = userProfile.profile_image;
            } else {
              img.style.display = 'none';
              if (initialsContainer) {
                const name = userProfile?.name || userProfile?.email || 'User';
                initialsContainer.textContent = getInitials(name);
                initialsContainer.style.display = 'flex';
              }
            }
          }
        } catch (err) {
          console.error('Error uploading avatar:', err);
          showToast('Error uploading avatar', false);
        }
        
        // Reset file input
        e.target.value = '';
      });

      // Password update
      document.getElementById('savePasswordBtn').addEventListener('click', async function() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validation
        if (!currentPassword || !newPassword || !confirmPassword) {
          showToast('Please fill in all password fields', false);
          return;
        }
        
        if (newPassword.length < 6) {
          showToast('New password must be at least 6 characters long', false);
          return;
        }
        
        if (newPassword !== confirmPassword) {
          showToast('New password and confirm password do not match', false);
          return;
        }
        
        // Disable button during request
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        try {
          const formData = new FormData();
          formData.append('current_password', currentPassword);
          formData.append('new_password', newPassword);
          
          const res = await fetch('api/update_password.php', {
            method: 'POST',
            body: formData
          });
          
          const json = await res.json();
          
          if (json.success) {
            showToast('Password updated successfully', true);
            // Clear password fields
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
          } else {
            showToast(json.message || 'Failed to update password', false);
          }
        } catch (err) {
          console.error('Error updating password:', err);
          showToast('Error updating password', false);
        } finally {
          btn.disabled = false;
          btn.textContent = 'Save Changes';
        }
      });

      // Cancel button
      document.getElementById('cancelBtn').addEventListener('click', function() {
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
      });

      // Remove avatar button
      document.getElementById('removeAvatarBtn').addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Close dropdown
        const dropdownElement = document.querySelector('.dropdown');
        if (dropdownElement) {
          const bsDropdown = bootstrap.Dropdown.getInstance(dropdownElement);
          if (bsDropdown) bsDropdown.hide();
        }
        
        if (!confirm('Are you sure you want to remove your avatar?')) {
          return;
        }
        
        // Disable button during request
        const btn = this;
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Removing...';
        
        try {
          const res = await fetch('api/remove_avatar.php', {
            method: 'POST'
          });
          
          const json = await res.json();
          
          if (json.success) {
            showToast('Avatar removed successfully', true);
            
            // Update UI
            const profilePreview = document.getElementById('profilePreview');
            const initialsContainer = document.getElementById('profileInitials');
            const cameraBtn = document.getElementById('cameraBtn');
            const cameraDropdown = document.getElementById('cameraDropdown');
            const profileImageInput = document.getElementById('profileImage');
            
            profilePreview.style.display = 'none';
            if (initialsContainer) {
              const name = userProfile?.name || userProfile?.email || 'User';
              initialsContainer.textContent = getInitials(name);
              initialsContainer.style.display = 'flex';
            }
            
            // Switch camera button back to direct upload
            if (cameraBtn) {
              cameraBtn.removeAttribute('data-bs-toggle');
              cameraBtn.removeAttribute('aria-expanded');
              cameraBtn.onclick = () => profileImageInput.click();
            }
            if (cameraDropdown) cameraDropdown.style.display = 'none';
            
            // Update profile data
            if (userProfile) {
              userProfile.profile_image = null;
            }
            
            // Update navbar without reload
            updateNavbarAvatar();
          } else {
            showToast(json.message || 'Failed to remove avatar', false);
            btn.disabled = false;
            btn.innerHTML = originalText;
          }
        } catch (err) {
          console.error('Error removing avatar:', err);
          showToast('Error removing avatar', false);
          btn.disabled = false;
          btn.innerHTML = originalText;
        }
      });

      // Logout button
      document.getElementById('logoutBtnProfile').addEventListener('click', async function(e) {
        e.preventDefault();
        try {
          const res = await fetch('auth/logout.php', { method: 'POST' });
          const json = await res.json();
          if (json.success) {
            window.location.href = 'login.php';
          } else {
            showToast(json.message || 'Logout failed', false);
          }
        } catch (err) {
          showToast('Network error', false);
        }
      });

      function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + 'Icon');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        } else {
          input.type = 'password';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        }
      }

      function showToast(message, isSuccess = false) {
        const toast = document.getElementById('toastMsg');
        const toastText = document.getElementById('toastText');
        
        toastText.innerText = message;
        
        // Remove existing background classes
        toast.classList.remove('bg-success', 'bg-danger');
        
        // Add appropriate background class
        if (isSuccess) {
          toast.classList.add('bg-success');
        } else {
          toast.classList.add('bg-danger');
        }
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
      }

      // Load profile on page load
      loadUserProfile();
    </script>
  </body>
</html>
