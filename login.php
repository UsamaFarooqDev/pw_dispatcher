<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">

    <body style="margin:0; background:#F4F4F5; overflow:hidden;">

<div class="container-fluid vh-100 g-0">
  <div class="row h-100 g-0">

    <div class="col-lg-5 d-flex flex-column justify-content-center align-items-center px-4" style="background:#fff; height:100vh; overflow-y:auto;">
      <div class="w-100" style="max-width:380px;">

        <div class="mb-3">
          <img src="assets/powercabs-logo-black.svg" alt="PowerCabs" style="height:63px; width:auto; display:block;" />
        </div>

        <div class="mb-4">
          <h2 style="font-size:1.75rem; font-weight:700; color:#18181B; letter-spacing:-0.03em; margin-bottom:6px; line-height:1.2;">Welcome back</h2>
          <p style="font-size:0.925rem; color:#71717A; margin:0;">Sign in to your dispatcher account to continue.</p>
        </div>

        <form id="loginForm" method="post" novalidate>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold" style="font-size:0.8325rem; color:#18181B;">Email Address</label>
            <div class="position-relative">
              <i class="bi bi-envelope position-absolute top-50 translate-middle-y" style="left:12px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
              <input name="email" type="email" id="email" class="form-control"
                placeholder="you@powercabs.ie"
                style="height:42px; border:1.5px solid #D4D4D8; border-radius:8px; padding-left:36px; font-size:0.945rem; background:#fff; color:#18181B; box-shadow:0 1px 2px rgba(24,24,27,0.04);"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.12)';"
                onblur="this.style.borderColor='#D4D4D8'; this.style.background='#fff'; this.style.boxShadow='0 1px 2px rgba(24,24,27,0.04)';"
                required />
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-semibold" style="font-size:0.8325rem; color:#18181B;">Password</label>
            <div class="position-relative">
              <i class="bi bi-lock position-absolute top-50 translate-middle-y" style="left:12px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
              <input name="password" type="password" id="password" class="form-control"
                placeholder="Enter your password"
                style="height:42px; border:1.5px solid #D4D4D8; border-radius:8px; padding-left:36px; padding-right:42px; font-size:0.945rem; background:#fff; color:#18181B; box-shadow:0 1px 2px rgba(24,24,27,0.04);"
                onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.12)';"
                onblur="this.style.borderColor='#D4D4D8'; this.style.background='#fff'; this.style.boxShadow='0 1px 2px rgba(24,24,27,0.04)';"
                required />
              <button type="button"
                class="btn position-absolute end-0 top-50 translate-middle-y me-2 p-0 d-flex align-items-center justify-content-center"
                style="width:28px; height:28px; background:transparent; border:none; z-index:10;"
                onclick="togglePasswordVisibility()">
                <i class="bi bi-eye-slash" id="passwordIcon" style="font-size:14px; color:#A1A1AA;"></i>
              </button>
            </div>
          </div>

          <button type="submit" id="loginBtn" class="btn w-100 fw-semibold d-flex align-items-center justify-content-center gap-2"
            style="height:42px; background:#f37a20; color:#fff; border:none; border-radius:8px; font-size:0.9rem; box-shadow:0 4px 14px rgba(243,122,32,0.35); letter-spacing:0.01em;"
            onmouseover="if(!this.disabled){this.style.background='#d96010'; this.style.boxShadow='0 4px 18px rgba(243,122,32,0.50)';}"
            onmouseout="if(!this.disabled){this.style.background='#f37a20'; this.style.boxShadow='0 4px 14px rgba(243,122,32,0.35)';}">
            <span id="loginBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <i class="bi bi-box-arrow-in-right" id="loginBtnIcon" style="font-size:16px;"></i>
            <span id="loginBtnText">Sign In</span>
          </button>
        </form>

        <p class="text-center mt-4 mb-0" style="font-size:0.79rem; color:#A1A1AA;">
          Authorised dispatcher access only &nbsp;·&nbsp; &copy; <?php echo date('Y'); ?> PowerCabs
        </p>

        <div class="mt-4 pt-3" style="border-top:1px solid #EBEBEB;">
          <p style="font-size:0.775rem; color:#A1A1AA; line-height:1.6; text-align:left;">
            By signing in you confirm that you are an authorised PowerCabs dispatcher and agree to the
            <a href="https://powercabs.ie/terms-conditions" target="_blank" rel="noopener"
              style="color:#f37a20; text-decoration:none; font-weight:600;"
              onmouseover="this.style.textDecoration='underline';"
              onmouseout="this.style.textDecoration='none';">
              Terms &amp; Conditions
            </a>.
            This system is for authorised personnel only. 
          </p>
        </div>

      </div>
    </div>

    <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-between p-4 position-relative overflow-hidden"
      style="background:linear-gradient(145deg, #f37a20 0%, #c95e0a 60%, #9c3f00 100%); height:100vh;">

      <div class="position-absolute" style="top:-80px; right:-80px; width:380px; height:380px; border-radius:50%; border:60px solid rgba(255,255,255,0.06);"></div>
      <div class="position-absolute" style="top:40px; right:40px; width:220px; height:220px; border-radius:50%; border:40px solid rgba(255,255,255,0.05);"></div>
      <div class="position-absolute" style="bottom:-100px; left:-60px; width:340px; height:340px; border-radius:50%; border:55px solid rgba(255,255,255,0.05);"></div>
      <div class="position-absolute" style="bottom:80px; left:80px; width:160px; height:160px; border-radius:50%; border:30px solid rgba(255,255,255,0.06);"></div>

      <svg viewBox="0 0 800 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg"
        style="position:absolute; inset:0; width:100%; height:100%; z-index:1; pointer-events:none;">
        <defs>
          <pattern id="bgGrid" width="44" height="44" patternUnits="userSpaceOnUse">
            <path d="M 44 0 L 0 0 0 44" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
          </pattern>
        </defs>
        <rect width="800" height="900" fill="url(#bgGrid)"/>

        <path d="M -40 720 Q 200 640 400 600 Q 600 560 860 510" stroke="rgba(255,255,255,0.10)" stroke-width="2.5" fill="none" stroke-dasharray="10 8"/>
        <path d="M -40 820 Q 220 760 420 720 Q 620 680 860 660" stroke="rgba(255,255,255,0.07)" stroke-width="2" fill="none" stroke-dasharray="6 8"/>
        <path d="M -40 60 Q 220 120 420 100 Q 620 80 860 140" stroke="rgba(255,255,255,0.07)" stroke-width="2" fill="none" stroke-dasharray="6 8"/>

        <g fill="rgba(255,255,255,0.07)">
          <circle cx="80"  cy="120" r="3"/>
          <circle cx="180" cy="60"  r="2.5"/>
          <circle cx="700" cy="180" r="3"/>
          <circle cx="640" cy="80"  r="2.5"/>
          <circle cx="120" cy="780" r="2.5"/>
          <circle cx="720" cy="820" r="3"/>
          <circle cx="60"  cy="540" r="2"/>
          <circle cx="760" cy="500" r="2"/>
          <circle cx="500" cy="60"  r="2"/>
          <circle cx="280" cy="850" r="2.5"/>
        </g>

        <g transform="translate(110,170) scale(1.55)" fill="none" stroke="rgba(255,255,255,0.13)" stroke-width="1.4" stroke-linejoin="round">
          <path d="M 4 22 L 8 10 Q 10 6 15 6 L 37 6 Q 42 6 44 10 L 48 22 L 48 30 Q 48 32 46 32 L 42 32 Q 40 32 40 30 L 40 28 L 12 28 L 12 30 Q 12 32 10 32 L 6 32 Q 4 32 4 30 Z"/>
          <path d="M 14 12 L 17 20 L 35 20 L 38 12 Z"/>
          <circle cx="14" cy="29" r="3"/>
          <circle cx="38" cy="29" r="3"/>
        </g>

        <g transform="translate(620,620) scale(1.55)" fill="none" stroke="rgba(255,255,255,0.13)" stroke-width="1.4" stroke-linejoin="round">
          <path d="M 4 22 L 8 10 Q 10 6 15 6 L 37 6 Q 42 6 44 10 L 48 22 L 48 30 Q 48 32 46 32 L 42 32 Q 40 32 40 30 L 40 28 L 12 28 L 12 30 Q 12 32 10 32 L 6 32 Q 4 32 4 30 Z"/>
          <path d="M 14 12 L 17 20 L 35 20 L 38 12 Z"/>
          <circle cx="14" cy="29" r="3"/>
          <circle cx="38" cy="29" r="3"/>
        </g>

        <g transform="translate(680,260) scale(1.5)" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.14)" stroke-width="1.2">
          <path d="M 0 -16 Q 12 -16 12 -4 Q 12 8 0 22 Q -12 8 -12 -4 Q -12 -16 0 -16 Z"/>
          <circle cx="0" cy="-4" r="4" fill="rgba(243,122,32,0.25)" stroke="none"/>
        </g>

        <g transform="translate(80,640) scale(1.5)" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.14)" stroke-width="1.2">
          <path d="M 0 -14 Q 10 -14 10 -3 Q 10 7 0 20 Q -10 7 -10 -3 Q -10 -14 0 -14 Z"/>
          <circle cx="0" cy="-3" r="3.5" fill="rgba(243,122,32,0.25)" stroke="none"/>
        </g>

        <g transform="translate(560,420) scale(1.4)" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="1.4">
          <circle cx="0" cy="0" r="18"/>
          <line x1="0" y1="0" x2="0" y2="-10" stroke-linecap="round"/>
          <line x1="0" y1="0" x2="7" y2="3" stroke-linecap="round"/>
          <circle cx="0" cy="0" r="1.6" fill="rgba(255,255,255,0.25)"/>
        </g>

        <g transform="translate(220,460) scale(1.4)" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="1.4">
          <circle cx="0" cy="0" r="14"/>
          <text x="0" y="4.5" text-anchor="middle" font-size="14" font-weight="700" fill="rgba(255,255,255,0.20)" stroke="none">€</text>
        </g>

        <g transform="translate(720,360) scale(1.5)" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.14)" stroke-width="1.2" stroke-linejoin="round">
          <path d="M 0 -10 L 3 -3 L 10 -3 L 4.5 1.5 L 6.5 9 L 0 4.5 L -6.5 9 L -4.5 1.5 L -10 -3 L -3 -3 Z"/>
        </g>

        <g transform="translate(60,360) scale(1.5)" stroke="rgba(255,255,255,0.14)" fill="none" stroke-linecap="round" stroke-width="1.5">
          <path d="M -8 6 L -8 -2"/>
          <path d="M -2 6 L -2 -8"/>
          <path d="M 4  6 L 4 -12"/>
          <path d="M 10 6 L 10 -16"/>
        </g>

        <g transform="translate(380,720) scale(1.5)" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="1.3">
          <rect x="-22" y="-14" width="44" height="28" rx="6"/>
          <line x1="-22" y1="-4" x2="22" y2="-4"/>
          <line x1="-22" y1="6"  x2="22" y2="6"/>
          <line x1="-12" y1="-14" x2="-12" y2="14"/>
          <line x1="0"   y1="-14" x2="0"   y2="14"/>
          <line x1="12"  y1="-14" x2="12"  y2="14"/>
          <path d="M -18 8 Q -4 -2 8 -8 Q 14 -10 18 -12" stroke="rgba(243,122,32,0.40)" stroke-width="1.6" stroke-dasharray="3 3"/>
        </g>

        <g transform="translate(420,300) scale(1.5)" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.14)" stroke-width="1.2" stroke-linejoin="round">
          <path d="M -16 -10 Q -16 -16 -10 -16 L 10 -16 Q 16 -16 16 -10 L 16 4 Q 16 10 10 10 L -4 10 L -10 16 L -10 10 Q -16 10 -16 4 Z"/>
          <circle cx="-6" cy="-3" r="1.3" fill="rgba(255,255,255,0.22)" stroke="none"/>
          <circle cx="0"  cy="-3" r="1.3" fill="rgba(255,255,255,0.22)" stroke="none"/>
          <circle cx="6"  cy="-3" r="1.3" fill="rgba(255,255,255,0.22)" stroke="none"/>
        </g>

        <g stroke="rgba(255,255,255,0.10)" stroke-width="1.3" stroke-linecap="round">
          <line x1="320" y1="100" x2="328" y2="100"/><line x1="324" y1="96" x2="324" y2="104"/>
          <line x1="600" y1="320" x2="608" y2="320"/><line x1="604" y1="316" x2="604" y2="324"/>
          <line x1="160" y1="540" x2="168" y2="540"/><line x1="164" y1="536" x2="164" y2="544"/>
          <line x1="540" y1="780" x2="548" y2="780"/><line x1="544" y1="776" x2="544" y2="784"/>
        </g>
      </svg>

      <div style="position:relative; z-index:2;">
        <div style="display:inline-flex; align-items:center; gap:10px; background:rgba(255,255,255,0.12); border-radius:100px; padding:6px 16px 6px 8px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.18);">
          <div style="width:28px; height:28px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-car-front-fill" style="color:#f37a20; font-size:14px;"></i>
          </div>
          <span style="color:#fff; font-size:0.85rem; font-weight:700; letter-spacing:-0.01em;">PowerCabs Ireland</span>
        </div>
      </div>

      <div style="position:relative; z-index:2; flex:1; display:flex; align-items:center; justify-content:center; padding:8px 0; min-height:0;">
        <svg viewBox="0 0 480 340" xmlns="http://www.w3.org/2000/svg" style="width:100%; max-width:620px; opacity:0.95;">

          <line x1="0" y1="170" x2="480" y2="170" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
          <line x1="240" y1="0" x2="240" y2="340" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
          <line x1="0" y1="85" x2="480" y2="85" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="0" y1="255" x2="480" y2="255" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="120" y1="0" x2="120" y2="340" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <line x1="360" y1="0" x2="360" y2="340" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>

          <path d="M 70 280 Q 140 220 200 195 Q 280 165 320 130 Q 380 80 410 60" stroke="rgba(255,255,255,0.55)" stroke-width="2.5" fill="none" stroke-dasharray="8 5"/>

          <!-- Stop 1: Create -->
          <circle cx="70" cy="280" r="10" fill="rgba(255,255,255,0.20)" stroke="#fff" stroke-width="2"/>
          <circle cx="70" cy="280" r="4"  fill="#fff"/>
          <text x="50" y="304" fill="#fff" font-size="8.5" font-weight="600" letter-spacing="0.2">Create</text>

          <!-- Stop 2: Assign -->
          <circle cx="200" cy="195" r="9" fill="rgba(255,255,255,0.18)" stroke="#fff" stroke-width="2"/>
          <circle cx="200" cy="195" r="3.5" fill="#fff"/>
          <text x="180" y="220" fill="#fff" font-size="8.5" font-weight="600" letter-spacing="0.2">Assign</text>

          <!-- Stop 3: Modify -->
          <circle cx="320" cy="130" r="9" fill="rgba(255,255,255,0.18)" stroke="#fff" stroke-width="2"/>
          <circle cx="320" cy="130" r="3.5" fill="#fff"/>
          <text x="300" y="118" fill="#fff" font-size="8.5" font-weight="600" letter-spacing="0.2">Modify</text>

          <!-- Stop 4: Complete -->
          <circle cx="410" cy="60" r="10" fill="rgba(74,222,128,0.30)" stroke="#fff" stroke-width="2"/>
          <circle cx="410" cy="60" r="4"  fill="#4ADE80"/>
          <text x="386" y="42" fill="#fff" font-size="8.5" font-weight="600" letter-spacing="0.2">Complete</text>

          <!-- Central car (smaller) -->
          <g transform="translate(248,165) scale(0.95)">
            <rect x="-22" y="-14" width="44" height="28" rx="7" fill="rgba(255,255,255,0.18)" stroke="#fff" stroke-width="1.5"/>
            <rect x="-14" y="-20" width="28" height="14" rx="4" fill="rgba(255,255,255,0.22)" stroke="#fff" stroke-width="1"/>
            <circle cx="-11" cy="14" r="5" fill="#fff" opacity="0.8"/>
            <circle cx="11" cy="14" r="5" fill="#fff" opacity="0.8"/>
          </g>

          <!-- Pulsing rings around the car -->
          <circle cx="248" cy="165" r="32" fill="none" stroke="rgba(255,255,255,0.30)" stroke-width="1.5">
            <animate attributeName="r" values="30;48;30" dur="2.4s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.55;0;0.55" dur="2.4s" repeatCount="indefinite"/>
          </circle>
          <circle cx="248" cy="165" r="48" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="1.3">
            <animate attributeName="r" values="46;66;46" dur="2.4s" begin="0.6s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.40;0;0.40" dur="2.4s" begin="0.6s" repeatCount="indefinite"/>
          </circle>

          <!-- ambient driver dots -->
          <circle cx="130" cy="100" r="6" fill="rgba(255,255,255,0.70)"/>
          <circle cx="130" cy="100" r="11" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
          <circle cx="370" cy="240" r="6" fill="rgba(255,255,255,0.70)"/>
          <circle cx="370" cy="240" r="11" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
          <circle cx="60"  cy="140" r="5" fill="rgba(255,255,255,0.50)"/>
          <circle cx="430" cy="200" r="5" fill="rgba(255,255,255,0.50)"/>
        </svg>
      </div>

      <div style="position:relative; z-index:2;">

        <h3 style="color:#fff; font-size:1.2rem; font-weight:800; letter-spacing:-0.03em; margin-bottom:4px; line-height:1.25;">
          Dublin's Smarter Dispatch Platform
        </h3>
        <p style="color:rgba(255,255,255,0.70); font-size:0.78rem; margin-bottom:10px;">
          Real-time driver tracking, intelligent ride assignment, and live order management.
        </p>

        <div class="d-flex gap-2 flex-wrap">
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:4px 11px; display:inline-flex; align-items:center; gap:6px; backdrop-filter:blur(8px);">
            <span style="width:7px; height:7px; border-radius:50%; background:#4ADE80; flex-shrink:0; box-shadow:0 0 6px #4ADE80;">
              <style>#live1{animation:livepulse 1.8s ease-in-out infinite;}@keyframes livepulse{0%,100%{opacity:1}50%{opacity:0.4}}</style>
            </span>
            <span style="color:#fff; font-size:0.72rem; font-weight:600;">Live Dispatch</span>
          </div>
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:4px 11px; display:inline-flex; align-items:center; gap:6px; backdrop-filter:blur(8px);">
            <i class="bi bi-map" style="color:rgba(255,255,255,0.85); font-size:11px;"></i>
            <span style="color:#fff; font-size:0.72rem; font-weight:600;">Dublin, Ireland</span>
          </div>
          <div style="background:rgba(255,255,255,0.13); border:1px solid rgba(255,255,255,0.20); border-radius:100px; padding:4px 11px; display:inline-flex; align-items:center; gap:6px; backdrop-filter:blur(8px);">
            <i class="bi bi-shield-check" style="color:rgba(255,255,255,0.85); font-size:11px;"></i>
            <span style="color:#fff; font-size:0.72rem; font-weight:600;">Secure Access</span>
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
        const toggleIcon = document.getElementById('passwordIcon');
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          toggleIcon.classList.remove('bi-eye-slash');
          toggleIcon.classList.add('bi-eye');
        } else {
          passwordInput.type = 'password';
          toggleIcon.classList.remove('bi-eye');
          toggleIcon.classList.add('bi-eye-slash');
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
      const loginBtn = document.getElementById('loginBtn');
      const loginBtnSpinner = document.getElementById('loginBtnSpinner');
      const loginBtnIcon = document.getElementById('loginBtnIcon');
      const loginBtnText = document.getElementById('loginBtnText');

      function setLoginBtnState(state) {
        // state: 'idle' | 'loading' | 'success'
        if (state === 'loading') {
          loginBtn.disabled = true;
          loginBtnSpinner.classList.remove('d-none');
          loginBtnIcon.classList.add('d-none');
          loginBtnText.textContent = 'Signing in...';
        } else if (state === 'success') {
          loginBtn.disabled = true;
          loginBtnSpinner.classList.add('d-none');
          loginBtnIcon.classList.remove('d-none');
          loginBtnIcon.classList.remove('bi-box-arrow-in-right');
          loginBtnIcon.classList.add('bi-check-circle');
          loginBtnText.textContent = 'Login successful';
        } else {
          loginBtn.disabled = false;
          loginBtnSpinner.classList.add('d-none');
          loginBtnIcon.classList.remove('d-none');
          loginBtnIcon.classList.remove('bi-check-circle');
          loginBtnIcon.classList.add('bi-box-arrow-in-right');
          loginBtnText.textContent = 'Sign In';
        }
      }

      document
        .getElementById('loginForm')
        .addEventListener('submit', async function (e) {
          e.preventDefault();

          const formData = new FormData(this);
          setLoginBtnState('loading');

          try {
            const resp = await fetch('auth/login.php', {
              method: 'POST',
              body: formData,
            });
            const json = await resp.json();

            if (json.success) {
              setLoginBtnState('success');
              showToast(json.message || 'Login successful', true);
              // Redirect after a short delay so user sees toast
              setTimeout(() => (window.location.href = 'home.php'), 900);
            } else {
              setLoginBtnState('idle');
              showToast(json.message || 'Invalid credentials', false);
            }
          } catch (err) {
            setLoginBtnState('idle');
            showToast('Network error, try again', false);
          }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  </body>
</html>
