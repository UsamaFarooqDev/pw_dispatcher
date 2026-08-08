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
