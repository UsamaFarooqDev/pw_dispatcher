// Mobile sidebar toggle now lives in js/sidebar.js (the persistent shell
// script, loaded on every page) instead of here — this file only loads
// once the dispatcher has visited Fleet Registry, so the toggle was
// unreliable everywhere else.

let passengersLoaded = false;

function switchTableView(view) {
  const driverTable   = document.getElementById('driverTable');
  const customerTable = document.getElementById('customerTable');
  const driverBtn     = document.getElementById('driverViewBtn');
  const customerBtn   = document.getElementById('customerViewBtn');
    const driverPagination = document.getElementById('driverPaginationContainer');
const passengerPagination = document.getElementById('passengerPaginationContainer');

  if (!driverTable || !customerTable || !driverBtn || !customerBtn) return;

  if (view === 'driver') {

    driverTable.classList.remove('d-none');
    driverTable.classList.add('d-block');
    customerTable.classList.remove('d-block');
    customerTable.classList.add('d-none');

    if (driverPagination)    driverPagination.style.display    = 'block';
    if (passengerPagination) passengerPagination.style.display = 'none';

    driverBtn.style.cssText  = 'border-radius:6px; height:32px; font-size:0.9125rem; background:#f37a20; color:#fff; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.08);';
    customerBtn.style.cssText = 'border-radius:6px; height:32px; font-size:0.9125rem; background:transparent; color:#71717A; border:none; box-shadow:none;';

  } else {

    customerTable.classList.remove('d-none');
    customerTable.classList.add('d-block');
    driverTable.classList.remove('d-block');
    driverTable.classList.add('d-none');

    if (passengerPagination) passengerPagination.style.display = 'block';
    if (driverPagination)    driverPagination.style.display    = 'none';

    customerBtn.style.cssText = 'border-radius:6px; height:32px; font-size:0.9125rem; background:#f37a20; color:#fff; border:none; box-shadow:0 1px 3px rgba(0,0,0,0.08);';
    driverBtn.style.cssText   = 'border-radius:6px; height:32px; font-size:0.9125rem; background:transparent; color:#71717A; border:none; box-shadow:none;';

    if (!passengersLoaded) {
      if (typeof loadPassengersData === 'function') {
        loadPassengersData(1);
        passengersLoaded = true;
      } else {
        console.error('loadPassengersData is not defined — check app.js is loaded correctly');
      }
    }
  }
}
