      // User profile data
      let userProfile = null;

      // Load user profile data on page load
      async function loadUserProfile() {
        try {
          const res = await fetch('api/get_profile.php');
          if (res.status === 401) {
            window.location.href = '/';
            return;
          }
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
            profileShowToast('Failed to load profile data', false);
          }
        } catch (err) {
          console.error('Error loading profile:', err);
          profileShowToast('Error loading profile data', false);
        } finally {
          if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
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
      function wireProfileImageInput() {
      document.getElementById('profileImage')?.addEventListener('change', async function (e) {
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
            profileShowToast('Avatar uploaded successfully', true);
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
            profileShowToast(json.message || 'Failed to upload avatar', false);
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
          profileShowToast('Error uploading avatar', false);
        }
        
        // Reset file input
        e.target.value = '';
      });
      }

      // Password update
      function wireSavePasswordBtn() {
      document.getElementById('savePasswordBtn')?.addEventListener('click', async function() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validation
        if (!currentPassword || !newPassword || !confirmPassword) {
          profileShowToast('Please fill in all password fields', false);
          return;
        }
        
        if (newPassword.length < 6) {
          profileShowToast('New password must be at least 6 characters long', false);
          return;
        }
        
        if (newPassword !== confirmPassword) {
          profileShowToast('New password and confirm password do not match', false);
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
            profileShowToast('Password updated successfully', true);
            // Clear password fields
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
          } else {
            profileShowToast(json.message || 'Failed to update password', false);
          }
        } catch (err) {
          console.error('Error updating password:', err);
          profileShowToast('Error updating password', false);
        } finally {
          btn.disabled = false;
          btn.textContent = 'Save Changes';
        }
      });
      }

      // Cancel button
      function wireCancelBtn() {
      document.getElementById('cancelBtn')?.addEventListener('click', function() {
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
      });
      }

      // Remove avatar button
      function wireRemoveAvatarBtn() {
      document.getElementById('removeAvatarBtn')?.addEventListener('click', async function(e) {
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
            profileShowToast('Avatar removed successfully', true);
            
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
            profileShowToast(json.message || 'Failed to remove avatar', false);
            btn.disabled = false;
            btn.innerHTML = originalText;
          }
        } catch (err) {
          console.error('Error removing avatar:', err);
          profileShowToast('Error removing avatar', false);
          btn.disabled = false;
          btn.innerHTML = originalText;
        }
      });
      }

      // Logout button
      function wireLogoutBtnProfile() {
      document.getElementById('logoutBtnProfile')?.addEventListener('click', async function(e) {
        e.preventDefault();
        try {
          const res = await fetch('auth/logout.php', { method: 'POST' });
          const json = await res.json();
          if (json.success) {
            window.location.href = 'login.php';
          } else {
            profileShowToast(json.message || 'Logout failed', false);
          }
        } catch (err) {
          profileShowToast('Network error', false);
        }
      });
      }

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

      function profileShowToast(message, isSuccess = false) {
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

      // Runs all of this page's former top-level listener registrations in
      // sequence, so a revisit under SPA navigation re-wires everything
      // against the freshly-swapped-in markup exactly like a true page load did.
      function initProfilePage() {
        loadUserProfile();
        wireProfileImageInput();
        wireSavePasswordBtn();
        wireCancelBtn();
        wireRemoveAvatarBtn();
        wireLogoutBtnProfile();
      }
      initProfilePage();

      window.SPA_PAGES = window.SPA_PAGES || {};
      window.SPA_PAGES['profile.php'] = { init: initProfilePage, cleanup: null };
