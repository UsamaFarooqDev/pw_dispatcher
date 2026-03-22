<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">

    <body style="margin:0; font-family:'Inter', system-ui, sans-serif; background:#F4F4F5;">

<div class="container-fluid vh-100 g-0">
  <div class="row h-100 g-0">

    <div class="col-lg-5 d-flex flex-column justify-content-center align-items-center px-4" style="background:#fff; min-height:100vh;">
      <div class="w-100" style="max-width:380px;">

        <div class="d-flex align-items-center gap-2 mb-5">
          <div class="d-flex align-items-center justify-content-center" style="width:36px; height:36px; background:linear-gradient(135deg,#f37a20,#d96010); border-radius:10px; flex-shrink:0;">
            <i class="bi bi-car-front-fill" style="color:#fff; font-size:16px;"></i>
          </div>
          <div>
            <div style="font-size:1rem; font-weight:800; color:#18181B; letter-spacing:-0.02em; line-height:1;">PowerCabs</div>
            <div style="font-size:0.65rem; color:#A1A1AA; font-weight:500; letter-spacing:0.04em; text-transform:uppercase;">Dispatch Console</div>
          </div>
        </div>

        <div class="mb-4">
          <h2 style="font-size:1.75rem; font-weight:800; color:#18181B; letter-spacing:-0.03em; margin-bottom:6px; line-height:1.2;">Welcome back</h2>
          <p style="font-size:0.875rem; color:#71717A; margin:0;">Sign in to your dispatcher account to continue.</p>
        </div>

        <form id="loginForm" method="post" novalidate>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Email Address</label>
            <div class="position-relative">
              <i class="bi bi-envelope position-absolute top-50 translate-middle-y" style="left:12px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
              <input name="email" type="email" id="email" class="form-control"
                placeholder="you@powercabs.ie"
                style="height:42px; border:1.5px solid #EBEBEB; border-radius:8px; padding-left:36px; font-size:0.845rem; background:#FAFAFA; color:#18181B;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"
                required />
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-semibold" style="font-size:0.8125rem; color:#18181B;">Password</label>
            <div class="position-relative">
              <i class="bi bi-lock position-absolute top-50 translate-middle-y" style="left:12px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
              <input name="password" type="password" id="password" class="form-control"
                placeholder="Enter your password"
                style="height:42px; border:1.5px solid #EBEBEB; border-radius:8px; padding-left:36px; padding-right:42px; font-size:0.845rem; background:#FAFAFA; color:#18181B;"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
                onblur="this.style.borderColor='#EBEBEB'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"
                required />
              <button type="button"
                class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                style="width:28px; height:28px; background:transparent; border:none; z-index:10;"
                onclick="togglePasswordVisibility()">
                <i class="bi bi-eye-slash" id="passwordIcon" style="font-size:14px; color:#A1A1AA;"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn w-100 fw-semibold d-flex align-items-center justify-content-center gap-2"
            style="height:42px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.9rem; box-shadow:0 4px 14px rgba(243,122,32,0.35); letter-spacing:0.01em;"
            onmouseover="this.style.background='#d96010'; this.style.boxShadow='0 4px 18px rgba(243,122,32,0.50)';"
            onmouseout="this.style.background='#f37a20'; this.style.boxShadow='0 4px 14px rgba(243,122,32,0.35)';">
            <i class="bi bi-box-arrow-in-right" style="font-size:16px;"></i> Sign In
          </button>
        </form>

        <p class="text-center mt-4 mb-0" style="font-size:0.75rem; color:#A1A1AA;">
          Authorised dispatcher access only &nbsp;·&nbsp; &copy; <?php echo date('Y'); ?> PowerCabs
        </p>

      </div>
    </div>

    <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-between p-5 position-relative overflow-hidden"
      style="background:linear-gradient(145deg, #f37a20 0%, #c95e0a 60%, #9c3f00 100%); min-height:100vh;">

      <div class="position-absolute" style="top:-80px; right:-80px; width:380px; height:380px; border-radius:50%; border:60px solid rgba(255,255,255,0.06);"></div>
      <div class="position-absolute" style="top:40px; right:40px; width:220px; height:220px; border-radius:50%; border:40px solid rgba(255,255,255,0.05);"></div>
      <div class="position-absolute" style="bottom:-100px; left:-60px; width:340px; height:340px; border-radius:50%; border:55px solid rgba(255,255,255,0.05);"></div>
      <div class="position-absolute" style="bottom:80px; left:80px; width:160px; height:160px; border-radius:50%; border:30px solid rgba(255,255,255,0.06);"></div>

      <div style="position:relative; z-index:2;">
        <div style="display:inline-flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); border-radius:100px; padding:6px 16px 6px 8px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.18);">
          <div style="width:28px; height:28px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-car-front-fill" style="color:#f37a20; font-size:14px;"></i>
          </div>
          <span style="color:#fff; font-size:0.85rem; font-weight:700; letter-spacing:-0.01em;">PowerCabs Ireland</span>
        </div>
      </div>

      <div style="position:relative; z-index:2; flex:1; display:flex; align-items:center; justify-content:center; padding:40px 0;">
        <svg viewBox="0 0 480 340" xmlns="http://www.w3.org/2000/svg" style="width:100%; max-width:500px; opacity:0.92;">

          <line x1="0" y1="170" x2="480" y2="170" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
          <line x1="240" y1="0" x2="240" y2="340" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
          <line x1="0" y1="85" x2="480" y2="85" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="0" y1="255" x2="480" y2="255" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="120" y1="0" x2="120" y2="340" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="360" y1="0" x2="360" y2="340" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>

          <path d="M 90 260 Q 160 200 240 170 Q 310 140 390 90" stroke="rgba(255,255,255,0.55)" stroke-width="2.5" fill="none" stroke-dasharray="8 5"/>

          <circle cx="90" cy="260" r="10" fill="rgba(255,255,255,0.20)" stroke="#fff" stroke-width="2"/>
          <circle cx="90" cy="260" r="4" fill="#fff"/>

          <circle cx="390" cy="90" r="10" fill="rgba(255,255,255,0.20)" stroke="#fff" stroke-width="2"/>
          <circle cx="390" cy="90" r="4" fill="#fff"/>

          <g transform="translate(232,155)">
            <rect x="-22" y="-14" width="44" height="28" rx="7" fill="rgba(255,255,255,0.18)" stroke="#fff" stroke-width="1.5"/>
            <rect x="-14" y="-20" width="28" height="14" rx="4" fill="rgba(255,255,255,0.22)" stroke="#fff" stroke-width="1"/>
            <circle cx="-11" cy="14" r="5" fill="#fff" opacity="0.8"/>
            <circle cx="11" cy="14" r="5" fill="#fff" opacity="0.8"/>
          </g>

          <circle cx="240" cy="170" r="30" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="1.5">
            <animate attributeName="r" values="28;44;28" dur="2.4s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.5;0;0.5" dur="2.4s" repeatCount="indefinite"/>
          </circle>

          <circle cx="150" cy="120" r="6" fill="rgba(255,255,255,0.70)"/>
          <circle cx="150" cy="120" r="10" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
          <circle cx="340" cy="230" r="6" fill="rgba(255,255,255,0.70)"/>
          <circle cx="340" cy="230" r="10" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
          <circle cx="80" cy="110" r="5" fill="rgba(255,255,255,0.50)"/>
          <circle cx="400" cy="220" r="5" fill="rgba(255,255,255,0.50)"/>

          <text x="55" y="288" fill="rgba(255,255,255,0.85)" font-size="10" font-family="system-ui" font-weight="600">Pickup</text>
          <text x="368" y="82" fill="rgba(255,255,255,0.85)" font-size="10" font-family="system-ui" font-weight="600">Drop-off</text>
        </svg>
      </div>

      <div style="position:relative; z-index:2;">

        <h3 style="color:#fff; font-size:1.5rem; font-weight:800; letter-spacing:-0.03em; margin-bottom:8px; line-height:1.3;">
          Dublin's Smarter<br/>Dispatch Platform
        </h3>
        <p style="color:rgba(255,255,255,0.70); font-size:0.845rem; margin-bottom:24px;">
          Real-time driver tracking, intelligent ride assignment, and live order management — all in one console.
        </p>

        <div class="d-flex gap-2 flex-wrap">
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:6px 14px; display:inline-flex; align-items:center; gap:7px; backdrop-filter:blur(8px);">
            <span style="width:7px; height:7px; border-radius:50%; background:#4ADE80; flex-shrink:0; box-shadow:0 0 6px #4ADE80;">
              <style>#live1{animation:livepulse 1.8s ease-in-out infinite;}@keyframes livepulse{0%,100%{opacity:1}50%{opacity:0.4}}</style>
            </span>
            <span style="color:#fff; font-size:0.78rem; font-weight:600;">Live Dispatch</span>
          </div>
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:6px 14px; display:inline-flex; align-items:center; gap:7px; backdrop-filter:blur(8px);">
            <i class="bi bi-map" style="color:rgba(255,255,255,0.85); font-size:12px;"></i>
            <span style="color:#fff; font-size:0.78rem; font-weight:600;">Dublin, Ireland</span>
          </div>
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:6px 14px; display:inline-flex; align-items:center; gap:7px; backdrop-filter:blur(8px);">
            <i class="bi bi-shield-check" style="color:rgba(255,255,255,0.85); font-size:12px;"></i>
            <span style="color:#fff; font-size:0.78rem; font-weight:600;">Secure Access</span>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<div class="position-fixed top-0 end-0 p-3" style="z-index:9999;">
  <div id="toastMsg" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastText"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

    <script>
      function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.password-toggle i');
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          toggleIcon.classList.remove('fa-eye-slash');
          toggleIcon.classList.add('fa-eye');
        } else {
          passwordInput.type = 'password';
          toggleIcon.classList.remove('fa-eye');
          toggleIcon.classList.add('fa-eye-slash');
        }
      }

      function showToast(message, isSuccess = false) {
        const toast = document.getElementById('toastMsg');
        const toastText = document.getElementById('toastText');

        toastText.innerText = message;

        if (isSuccess) {
          toast.classList.remove('bg-danger');
          toast.classList.add('bg-success');
        } else {
          toast.classList.remove('bg-success');
          toast.classList.add('bg-danger');
        }

        let bsToast = bootstrap.Toast.getInstance(toast);
        if (!bsToast) bsToast = new bootstrap.Toast(toast);
        bsToast.show();
      }

      // On page load: check if already logged in
      (async function checkSession() {
        try {
          const res = await fetch('auth/session.php');
          const json = await res.json();
          if (json.loggedIn) {
            // Already logged in — redirect to home
            window.location.href = 'home.php';
          }
        } catch (err) {
          console.error('Session check error', err);
        }
      })();

      // AJAX submit
      document
        .getElementById('loginForm')
        .addEventListener('submit', async function (e) {
          e.preventDefault();

          const formData = new FormData(this);

          try {
            const resp = await fetch('auth/login.php', {
              method: 'POST',
              body: formData,
            });
            const json = await resp.json();

            if (json.success) {
              showToast(json.message || 'Login successful', true);
              // Redirect after a short delay so user sees toast
              setTimeout(() => (window.location.href = 'home.php'), 900);
            } else {
              showToast(json.message || 'Invalid credentials', false);
            }
          } catch (err) {
            showToast('Network error, try again', false);
          }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  </body>
</html>
