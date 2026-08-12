<?php
session_start();

require_once 'auth/require_login_redirect.php';
$pageTitle = 'Profile Management | Powercabs Dispatcher';
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>
    
     <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main id="app-content" class="main-content p-4" style="background:#F4F4F5; min-height:90vh;">

  <div class="row g-4">

    <div class="col-md-4">
      <div class="rounded-3 border overflow-hidden text-center" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="position-relative mx-auto mb-4" style="width:110px; height:110px;">
            <div id="profileInitials"
              class="d-flex align-items-center justify-content-center rounded-circle w-100 h-100"
              style="background:#FFF3E8; color:#f37a20; font-size:38px; font-weight:700; border:3px solid #fff; box-shadow:0 2px 10px rgba(0,0,0,0.08);">
              👤
            </div>
            <img id="profilePreview" src="" alt=""
              class="rounded-circle position-absolute top-0 start-0 w-100 h-100"
              style="object-fit:cover; border:3px solid #fff; box-shadow:0 2px 10px rgba(0,0,0,0.08); display:none;" />
            <input type="file" id="profileImage" accept="image/*" class="d-none" />

            <div class="dropdown position-absolute bottom-0 end-0" style="z-index:10;">
              <button id="cameraBtn" type="button"
                class="btn d-flex align-items-center justify-content-center rounded-circle p-0"
                data-bs-toggle="dropdown" aria-expanded="false"
                style="width:32px; height:32px; background:#f37a20; color:#fff; border:2px solid #fff; box-shadow:0 2px 6px rgba(243,122,32,0.35);"
                onmouseover="this.style.background='#d96010';"
                onmouseout="this.style.background='#f37a20';">
                <i class="bi bi-camera-fill" style="font-size:13px;"></i>
              </button>
              <ul id="cameraDropdown" class="dropdown-menu dropdown-menu-end p-1" style="min-width:170px; border:1px solid #EBEBEB; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.10);">
                <li>
                  <label for="profileImage" class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2"
                    style="font-size:0.8375rem; font-weight:500; color:#52525B; cursor:pointer;"
                    onmouseover="this.style.background='#FFF3E8'; this.style.color='#f37a20';"
                    onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
                    <i class="bi bi-upload" style="font-size:14px;"></i> Update Picture
                  </label>
                </li>
                <li><hr class="dropdown-divider my-1" style="border-color:#EBEBEB;"></li>
                <li>
                  <a id="removeAvatarBtn" class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2" href="#"
                    style="font-size:0.8375rem; font-weight:500; color:#52525B;"
                    onmouseover="this.style.background='#FFF1F2'; this.style.color='#E11D48';"
                    onmouseout="this.style.background='transparent'; this.style.color='#52525B';">
                    <i class="bi bi-trash3" style="font-size:14px;"></i> Remove Picture
                  </a>
                </li>
              </ul>
            </div>
          </div>

          <div id="userName" class="fw-bold mb-1" style="font-size:1.05rem; color:#18181B; letter-spacing:-0.01em;">Loading…</div>
          <div id="userEmail" style="font-size:0.8125rem; color:#A1A1AA; font-weight:500;">Loading…</div>

          <div class="my-4" style="border-top:1px solid #EBEBEB;"></div>

          <div class="d-flex flex-column gap-2 text-start">
            <div class="rounded-2 px-3 py-2 d-flex align-items-center gap-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <i class="bi bi-person-circle" style="color:#f37a20; font-size:15px; flex-shrink:0;"></i>
              <div>
                <div style="font-size:0.7rem; color:#A1A1AA; font-weight:500; text-transform:uppercase; letter-spacing:0.04em;">Role</div>
                <div style="font-size:0.845rem; color:#18181B; font-weight:600;">Dispatcher</div>
              </div>
            </div>
            <div class="rounded-2 px-3 py-2 d-flex align-items-center gap-2" style="background:#FAFAFA; border:1.5px solid #EBEBEB;">
              <i class="bi bi-shield-check" style="color:#22C55E; font-size:15px; flex-shrink:0;"></i>
              <div>
                <div style="font-size:0.7rem; color:#A1A1AA; font-weight:500; text-transform:uppercase; letter-spacing:0.04em;">Account Status</div>
                <div style="font-size:0.845rem; color:#22C55E; font-weight:600;">Active</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-8 d-flex flex-column gap-4">

      <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Change Password</span>
          </div>

          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Current Password</label>
              <div class="position-relative">
                <input type="password" class="form-control pe-5" id="currentPassword"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                <button type="button" onclick="togglePassword('currentPassword')"
                  class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                  style="width:28px; height:28px; background:transparent; border:none; z-index:10;">
                  <i class="bi bi-eye-slash" id="currentPasswordIcon" style="font-size:14px; color:#A1A1AA;"></i>
                </button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">New Password</label>
              <div class="position-relative">
                <input type="password" class="form-control pe-5" id="newPassword"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                <button type="button" onclick="togglePassword('newPassword')"
                  class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                  style="width:28px; height:28px; background:transparent; border:none; z-index:10;">
                  <i class="bi bi-eye-slash" id="newPasswordIcon" style="font-size:14px; color:#A1A1AA;"></i>
                </button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Confirm New Password</label>
              <div class="position-relative">
                <input type="password" class="form-control pe-5" id="confirmPassword"
                  style="height:38px; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.845rem; background:#FAFAFA;"
                  onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                  onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';" />
                <button type="button" onclick="togglePassword('confirmPassword')"
                  class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                  style="width:28px; height:28px; background:transparent; border:none; z-index:10;">
                  <i class="bi bi-eye-slash" id="confirmPasswordIcon" style="font-size:14px; color:#A1A1AA;"></i>
                </button>
              </div>
            </div>

          </div>

          <div class="d-flex align-items-center gap-2 mt-4">
            <button id="savePasswordBtn" class="btn fw-semibold d-flex align-items-center gap-2 px-4"
              style="height:38px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.875rem; box-shadow:0 4px 14px rgba(243,122,32,0.30);"
              onmouseover="this.style.background='#d96010';"
              onmouseout="this.style.background='#f37a20';">
              <i class="bi bi-check2-circle" style="font-size:15px;"></i> Save Changes
            </button>
            <button id="cancelBtn" type="button" class="btn fw-semibold px-4"
              style="height:38px; background:#fff; color:#18181B; border:1.5px solid #EBEBEB; border-radius:8px; font-size:0.875rem;"
              onmouseover="this.style.borderColor='#18181B';"
              onmouseout="this.style.borderColor='#EBEBEB';">
              Cancel
            </button>
          </div>

        </div>
      </div>

      <div class="rounded-3 border overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="p-4">

          <div class="mb-3 pb-2" style="border-bottom:1px solid #EBEBEB;">
            <span class="fw-bold" style="font-size:0.8rem; letter-spacing:0.05em; text-transform:uppercase; color:#A1A1AA;">Security</span>
          </div>

          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
              <div class="fw-semibold mb-1" style="font-size:0.875rem; color:#18181B;">Active Sessions</div>
              <div style="font-size:0.8rem; color:#71717A;">Sign out of all devices where your account is currently logged in.</div>
            </div>
            <button id="logoutBtnProfile" class="btn fw-semibold d-flex align-items-center gap-2 px-4"
              style="height:38px; background:#fff; color:#E11D48; border:1.5px solid #FECDD3; border-radius:8px; font-size:0.845rem; flex-shrink:0;"
              onmouseover="this.style.background='#FFF1F2'; this.style.borderColor='#E11D48';"
              onmouseout="this.style.background='#fff'; this.style.borderColor='#FECDD3';">
              <i class="bi bi-box-arrow-right" style="font-size:15px;"></i> Sign Out All
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1050;">
  <div id="toastMsg" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body fw-semibold" id="toastText" style="font-size:0.845rem;"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
</main>

    <!-- Profile Script -->
    <script src="js/profile.js"></script>
  </body>
</html>
