<?php
session_start();

require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>
    <div class="container-fluid vh-100 g-0">
      <div class="row h-100 g-0">
        <div class="col-lg-6 d-flex flex-column justify-content-center px-5">
          <div class="mx-lg-5 px-lg-5">
            <h2 class="mb-3" style="font-size: 42px; font-weight: 700">
              Sign in
            </h2>
            <p class="mb-4 text-muted">
              Please login to continue to your account.
            </p>

            <form id="loginForm" method="post" novalidate>
              <div class="form-group mb-3">
                <label for="email">Email</label>
                <input
                  name="email"
                  type="email"
                  id="email"
                  class="form-control"
                  placeholder="Johndoe@gmail.com"
                  required
                />
              </div>

              <div class="form-group mb-3 position-relative">
                <label for="password">Password</label>
                <input
                  name="password"
                  type="password"
                  id="password"
                  class="form-control"
                  placeholder="Password"
                  required
                />
                <span
                  class="password-toggle"
                  style="
                    position: absolute;
                    right: 12px;
                    top: 38px;
                    cursor: pointer;
                  "
                  onclick="togglePasswordVisibility()"
                >
                  <i class="fas fa-eye-slash"></i>
                </span>
              </div>

              <button
                type="submit"
                class="btn w-100"
                style="background-color: #f37a20; color: #fff"
              >
                Sign in
              </button>
            </form>
          </div>
        </div>

        <div
          class="col-lg-6 d-none d-lg-block position-relative h-100 overflow-hidden"
        >
          <div class="h-100 w-100">
            <img
              src="assets/right-column.jpg"
              alt="Login Visual"
              class="login-img"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div
        id="toastMsg"
        class="toast align-items-center text-white border-0"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="d-flex">
          <div class="toast-body" id="toastText"></div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            data-bs-dismiss="toast"
            aria-label="Close"
          ></button>
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
