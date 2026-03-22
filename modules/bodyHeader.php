<div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 py-1">
  <div class="d-flex align-items-center gap-2">
    <a href="order.php"
      class="btn d-flex align-items-center gap-2 fw-semibold px-4"
      style="background:#f37a20; color:#fff; border:none; border-radius:8px; height:38px; font-size:0.85rem; box-shadow:0 4px 14px rgba(243,122,32,0.35); letter-spacing:0.01em;"
      onmouseover="this.style.background='#d96010'; this.style.boxShadow='0 4px 18px rgba(243,122,32,0.50)';"
      onmouseout="this.style.background='#f37a20'; this.style.boxShadow='0 4px 14px rgba(243,122,32,0.35)';">
      <i class="bi bi-plus-circle" style="font-size:15px;"></i>
      New Order
    </a>
    <a href="map.php"
      class="btn d-flex align-items-center gap-2 fw-semibold px-4"
      style="background:#fff; color:#18181B; border:1.5px solid #18181b; border-radius:8px; height:38px; font-size:0.85rem; letter-spacing:0.01em;"
      onmouseover="this.style.borderColor='#f37a20'; this.style.color='#f37a20';"
      onmouseout="this.style.borderColor='#18181B'; this.style.color='#18181B';">
      <i class="bi bi-map" style="font-size:15px;"></i>
      Open Map
    </a>
  </div>

  <div class="position-relative" style="width:100%; max-width:280px;">
    <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left:13px; font-size:13px; color:#A1A1AA; pointer-events:none;"></i>
    <input
      type="text"
      id="globalSearchInput"
      placeholder="Find rides, assign orders…"
      class="form-control w-100"
      style="height:38px; border-radius:8px; border:1.5px solid #18181b; padding-left:34px; font-size:0.8375rem; color:#18181B; background:#FAFAFA; outline:none;"
      onfocus="this.style.borderColor='#f37a20'; this.style.background='#fff'; this.style.boxShadow='0 0 0 3px rgba(243,122,32,0.10)';"
      onblur="this.style.borderColor='#18181b'; this.style.background='#FAFAFA'; this.style.boxShadow='none';"
    />
  </div>
</div>