      let passengers = [];
      let drivers = [];
      let selectedPassengerId = null;
      let selectedDriverId = null;
      let selectedVehicleNumber = null;
      let orderMap, orderDirectionsService, orderDirectionsRenderer;
      let pickupLatLng = null;
      let dropoffLatLng = null;
      let currentDistance = null;
      let currentDuration = null;
      let currentFare = null;
      // Raw Google-Directions values; specials override these when set
      let googleDistance = null;
      let googleDuration = null;
      let googleFare = null;
      let nearbyDriversList = [];
      let selectedNearbyDriverId = null;

      /* ============================================================================
       * DUBLIN-ONLY LOCATION RESTRICTION (ACTIVE)
       * ----------------------------------------------------------------------------
       * Pickup/drop-off must be within Dublin, Ireland. Addresses from other
       * cities/countries (e.g. Islamabad, London) must not appear in the
       * autocomplete dropdown, must not be accepted if somehow selected, and
       * must not let the order be confirmed.
       * ============================================================================ */
      const DUBLIN_REGION_BOUNDS = { north: 53.47, south: 53.15, west: -6.60, east: -6.05 };

      function isWithinDublinRegion(lat, lng) {
        return lat != null && lng != null &&
          lat <= DUBLIN_REGION_BOUNDS.north && lat >= DUBLIN_REGION_BOUNDS.south &&
          lng >= DUBLIN_REGION_BOUNDS.west && lng <= DUBLIN_REGION_BOUNDS.east;
      }

    function showToast(message, isSuccess = false) {
  const toastEl = document.getElementById('toastMsg');
  const toastText = document.getElementById('toastText');

  if (!toastEl || !toastText) {
    console.warn('Toast elements not found in DOM');
    return;
  }

  toastText.innerHTML = `<span style="font-weight: 500; font-size: 14px;">${message}</span>`;
  toastEl.classList.remove('bg-success', 'bg-danger');
  toastEl.className = `toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0`;

  let bsToast = bootstrap.Toast.getInstance(toastEl);
  if (!bsToast) {
    bsToast = new bootstrap.Toast(toastEl, {
      autohide: true,
      delay: 3000
    });
  }

  bsToast.show();
}

      function initOrderPageCore() {
        fetchPassengers();
        fetchDrivers();
        setupCustomerAutocomplete();
        setupDriverModal();
        setupRouteListeners();
        setupConfirmOrder();
        setupAssignNearestDriver();
        setupPillGroups();
        setupSpecialCostListeners();
        loadRideTypes();
        // The shell loads the Maps script once, without a callback= param, so
        // each page that needs it calls its own initGoogleMaps() directly —
        // it self-polls if google.maps isn't ready yet (ensures Places
        // autocomplete works either way).
        initGoogleMaps();
      }

      /* ---------------------- Pill button groups (service type, seats) ---------------------- */
      function wirePillGroup(group) {
        if (!group) return;
        const targetId = group.dataset.pillTarget;
        const hidden = targetId ? document.getElementById(targetId) : null;
        group.querySelectorAll('button[data-value]').forEach((btn) => {
          btn.addEventListener('click', () => {
            group.querySelectorAll('button[data-value]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            if (hidden) {
              hidden.value = btn.dataset.value ?? '';
              hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }
          });
        });
      }

      function setupPillGroups() {
        document.querySelectorAll('[data-pill-target]').forEach(wirePillGroup);
      }

      /* ---------------------- Ride types (loaded from ride_types table) ---------------------- */
      // `rideTypeMultipliers` is declared by a small inline <script> in order.php
      // (right before this file is loaded) so calculateFare has multipliers on
      // first paint without a fetch — see order.php for the PHP-bootstrapped value.

      function escapeAttr(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }
      function escapeText(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }

      async function loadRideTypes() {
        // Skip the fetch if the server already rendered the pills (the common case).
        const group = document.getElementById('rideTypePillGroup');
        if (!group) return;
        if (group.querySelector('button[data-value]')) return;

        try {
          const res = await fetch('api/get_ride_types.php');
          if (res.status === 401) { window.location.href = '/'; return; }
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const result = await res.json();
          if (!result || !result.success || !Array.isArray(result.data) || result.data.length === 0) {
            group.innerHTML = '<div class="text-muted" style="font-size:0.78rem; grid-column: 1 / -1;">No ride types available.</div>';
            return;
          }
          renderRideTypes(result.data);
        } catch (err) {
          console.error('Failed to load ride types:', err);
          group.innerHTML = '<div class="text-danger" style="font-size:0.78rem; grid-column: 1 / -1;">Failed to load ride types. Refresh to retry.</div>';
        }
      }

      function renderRideTypes(types) {
        const group = document.getElementById('rideTypePillGroup');
        if (!group) return;

        // Keep the multiplier lookup in sync with whatever the DB says.
        rideTypeMultipliers = {};
        types.forEach((t) => {
          rideTypeMultipliers[t.name] = Number(t.multiplier) || 1.0;
        });

        // Preserve an already-selected value (e.g. if user somehow landed after an earlier pick),
        // otherwise default to the first ride type.
        const hidden = document.getElementById('serviceType');
        const currentValue = hidden?.value || '';
        const defaultName = types.find((t) => t.name === currentValue) ? currentValue : types[0].name;
        if (hidden) hidden.value = defaultName;

        group.innerHTML = types.map((t) => {
          const isActive = t.name === defaultName;
          const icon = t.image_url
            ? `<img src="${escapeAttr(t.image_url)}" alt="" style="width:26px; height:26px; object-fit:contain;" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='inline');">`
              + (t.icon_emoji ? `<span style="display:none; font-size:20px; line-height:1;">${escapeText(t.icon_emoji)}</span>` : '')
            : (t.icon_emoji
                ? `<span style="font-size:22px; line-height:1;">${escapeText(t.icon_emoji)}</span>`
                : `<i class="bi bi-car-front"></i>`);

          return `<button type="button" class="ride-type-btn${isActive ? ' active' : ''}" data-value="${escapeAttr(t.name)}" title="${escapeAttr(t.description || t.name)}">
            <span class="ride-type-icon">${icon}</span>
            <span class="ride-type-label">${escapeText(t.name)}</span>
          </button>`;
        }).join('');

        wirePillGroup(group);

        // If fare was already computed before types loaded, re-run with the DB multiplier
        if (googleDistance != null && googleDuration != null) {
          recalculateFareForCurrentRoute();
        }
      }

      /* ---------------------- Customers ---------------------- */
      async function fetchPassengers() {
        try {
          const res = await fetch('api/get_passengers.php');
          const data = await res.json();
          if (data.success && data.data) {
            passengers = data.data;
            if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
          }
        } catch (err) {
          console.error('Error fetching passengers', err);
        }
      }

      // ── Passenger mode switching ──
      function switchPaxMode(mode) {
        document.getElementById('passengerMode').value = mode;
        document.getElementById('paxModeExisting').classList.toggle('is-active', mode === 'existing');
        document.getElementById('paxModeCustom').classList.toggle('is-active', mode === 'custom');
        const e2 = document.getElementById('paxModeExisting2');
        const c2 = document.getElementById('paxModeCustom2');
        if (e2) e2.classList.toggle('is-active', mode === 'existing');
        if (c2) c2.classList.toggle('is-active', mode === 'custom');
        document.getElementById('paxExistingPane').style.display = mode === 'existing' ? '' : 'none';
        document.getElementById('paxCustomPane').style.display = mode === 'custom' ? '' : 'none';

        // Reset state when switching
        selectedPassengerId = null;
        document.getElementById('customerId').value = '';
        document.getElementById('customerNameInput').value = '';
        document.getElementById('customPassengerName').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerSuggestions').style.display = 'none';
        document.getElementById('paxSelectedCard').style.display = 'none';
        document.getElementById('paxSearchWrapper').style.display = '';
        if (typeof selectCountry === 'function') selectCountry('ie');
      }

      function clearSelectedPassenger() {
        selectedPassengerId = null;
        document.getElementById('customerId').value = '';
        document.getElementById('customerNameInput').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('paxSelectedCard').style.display = 'none';
        document.getElementById('paxSearchWrapper').style.display = '';
        if (typeof selectCountry === 'function') selectCountry('ie');
        document.getElementById('customerNameInput').focus();
      }

      // Tracks the outside-click handler across re-invocations of
      // setupCustomerAutocomplete() (called fresh on every SPA revisit) so
      // it doesn't accumulate duplicate document-level listeners.
      let _customerAutocompleteOutsideClick = null;

      function setupCustomerAutocomplete() {
        const nameInput = document.getElementById('customerNameInput');
        const phoneInput = document.getElementById('customerPhone');
        const suggestions = document.getElementById('customerSuggestions');
        const customerIdInput = document.getElementById('customerId');

        const render = (items) => {
          suggestions.innerHTML = '';
          if (!items.length) {
            suggestions.style.display = 'none';
            return;
          }
          items.forEach((p) => {
            const displayName = p.name || p.full_name || 'Unknown';
            const displayPhone = p.phone || p.phone_number || '';
            const div = document.createElement('button');
            div.type = 'button';
            div.className = 'list-group-item list-group-item-action';
            div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 12px;';
            div.innerHTML = '<span style="font-weight:600; font-size:0.84rem; color:#18181B;">' + displayName + '</span>' +
              (displayPhone ? '<span style="font-size:0.78rem; color:#71717A;">' + displayPhone + '</span>' : '');
            div.addEventListener('click', () => {
              nameInput.value = displayName;
              setPhoneFromFullNumber(displayPhone);
              customerIdInput.value = p.id || '';
              selectedPassengerId = p.id || null;
              suggestions.style.display = 'none';
              // Show selected card, hide search input
              document.getElementById('paxSelectedName').textContent = displayName;
              document.getElementById('paxSelectedCard').style.display = 'flex';
              document.getElementById('paxSearchWrapper').style.display = 'none';
            });
            suggestions.appendChild(div);
          });
          suggestions.style.display = 'block';
        };

        let debounceTimer = null;
        nameInput.addEventListener('input', (e) => {
          clearTimeout(debounceTimer);
          selectedPassengerId = null;
          customerIdInput.value = '';
          const term = e.target.value.toLowerCase();
          debounceTimer = setTimeout(() => {
            if (!term) {
              suggestions.style.display = 'none';
              return;
            }
            const matches = passengers.filter((p) => {
              const name = (p.name || p.full_name || '').toLowerCase();
              return name.includes(term);
            });
            render(matches);
          }, 200);
        });

        if (_customerAutocompleteOutsideClick) document.removeEventListener('click', _customerAutocompleteOutsideClick);
        _customerAutocompleteOutsideClick = (e) => {
          if (!suggestions.contains(e.target) && e.target !== nameInput) {
            suggestions.style.display = 'none';
          }
        };
        document.addEventListener('click', _customerAutocompleteOutsideClick);
      }

      /* ---------------------- Drivers ---------------------- */
      async function fetchDrivers() {
        try {
          const res = await fetch('api/get_drivers.php?status=approved&limit=500');
          const data = await res.json();
          if (data.success && data.data) {
            drivers = data.data;
            renderOrderDriverDropdown(drivers);
          }
        } catch (err) {
          console.error('Error fetching drivers', err);
        }
      }

      function renderOrderDriverDropdown(list) {
        const container = document.getElementById('orderDriverDropdownList');
        if (!container) return;
        container.innerHTML = '';
        if (!list || list.length === 0) {
          container.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">No approved drivers found</div>';
          return;
        }
        list.forEach(d => {
          const name = d.full_name || d.name || 'Driver';
          const vehicle = d.vehicle_make || d.vehicle_number || '';
          const phone = d.phone || '';
          const initials = name.trim().split(/\s+/).map(p=>p[0]).slice(0,2).join('').toUpperCase();
          const item = document.createElement('button');
          item.type = 'button';
          item.className = 'list-group-item list-group-item-action border-0 px-3 py-2';
          item.style.cssText = 'font-size:0.8125rem; cursor:pointer; border-bottom:1px solid #F4F4F5 !important;';
          item.innerHTML = `
            <div class="d-flex align-items-center gap-2">
              <div style="width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:9px; flex-shrink:0;">${initials}</div>
              <div style="min-width:0;">
                <div class="fw-semibold text-truncate" style="color:#18181B; font-size:0.8125rem;">${name}</div>
                <div class="text-truncate" style="font-size:0.72rem; color:#71717A;">${vehicle}${phone ? ' &middot; ' + phone : ''}</div>
              </div>
            </div>
          `;
          item.addEventListener('mousedown', (e) => { e.preventDefault(); selectOrderDriver(d); });
          container.appendChild(item);
        });
      }

      function selectOrderDriver(driver) {
        const searchInput = document.getElementById('driverSearchModal');
        const hiddenInput = document.getElementById('driverSelectModal');
        const phoneInput = document.getElementById('driverPhoneModal');
        const vehicleInput = document.getElementById('vehicleSelectModal');
        const name = driver.full_name || driver.name || 'Driver';

        if (searchInput) searchInput.value = `${name} — ${driver.vehicle_make || ''}`;
        if (hiddenInput) hiddenInput.value = driver.id;

        selectedDriverId = driver.id;
        setDriverPhoneFromFullNumber(driver.phone || '');
        if (vehicleInput) vehicleInput.value = driver.vehicle_number || '';
        selectedVehicleNumber = driver.vehicle_number || null;
        hideOrderDriverDropdown();
      }

      function setDriverPhoneFromFullNumber(fullPhone) {
        const phoneInput = document.getElementById('driverPhoneModal');
        if (!fullPhone) { if (phoneInput) phoneInput.value = ''; return; }
        const sorted = [...COUNTRY_LIST].sort((a, b) => b.dial.length - a.dial.length);
        for (const c of sorted) {
          if (fullPhone.startsWith(c.dial)) {
            selectDriverCountry(c.iso);
            if (phoneInput) phoneInput.value = fullPhone.slice(c.dial.length);
            return;
          }
        }
        if (phoneInput) phoneInput.value = fullPhone.replace(/^\+/, '');
      }

      function selectDriverCountry(iso) {
        const c = COUNTRY_LIST.find(x => x.iso === iso);
        if (!c) return;
        document.getElementById('driverSelectedFlag').src = 'https://flagcdn.com/w20/' + c.iso + '.png';
        document.getElementById('driverSelectedFlag').alt = c.iso.toUpperCase();
        document.getElementById('driverSelectedDialCode').textContent = c.dial;
        document.getElementById('driverCountryDropdown').style.display = 'none';
        document.querySelectorAll('#driverCountryOptions .country-option').forEach(el => {
          el.classList.toggle('is-selected', el.dataset.iso === iso);
        });
      }

      function showOrderDriverDropdown() {
        const el = document.getElementById('orderDriverDropdownList');
        if (el) el.style.display = 'block';
      }
      function hideOrderDriverDropdown() {
        const el = document.getElementById('orderDriverDropdownList');
        if (el) el.style.display = 'none';
      }

      function setupDriverModal() {
        const searchInput = document.getElementById('driverSearchModal');
        const confirmBtn = document.getElementById('confirmAssignDriverBtn');

        if (searchInput) {
          searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const hiddenInput = document.getElementById('driverSelectModal');
            if (hiddenInput) hiddenInput.value = '';
            selectedDriverId = null;
            if (!term) {
              renderOrderDriverDropdown(drivers);
            } else {
              const filtered = drivers.filter(d => {
                const n = (d.full_name || d.name || '').toLowerCase();
                const v = (d.vehicle_make || d.vehicle_number || '').toLowerCase();
                const p = (d.phone || '').toLowerCase();
                return n.includes(term) || v.includes(term) || p.includes(term);
              });
              renderOrderDriverDropdown(filtered);
            }
            showOrderDriverDropdown();
          });
        }

        if (confirmBtn) {
          confirmBtn.addEventListener('click', () => {
            if (!selectedDriverId) {
              showToast('Please select a driver');
              return;
            }
            const modal = bootstrap.Modal.getInstance(
              document.getElementById('assignDriverModal')
            );
            modal?.hide();
          });
        }
      }

      /* ---------------------- Assign Nearest Driver ---------------------- */
      function setupAssignNearestDriver() {
        const openBtn = document.getElementById('assignNearestDriverOpenBtn');
        const modalEl = document.getElementById('searchDriverModal');
        const loadingEl = document.getElementById('searchDriverLoading');
        const resultsEl = document.getElementById('searchDriverResults');
        const emptyEl = document.getElementById('searchDriverEmpty');
        const listEl = document.getElementById('searchDriverList');
        const assignBtn = document.getElementById('assignNearestDriverBtn');

        if (!openBtn || !modalEl) return;

        openBtn.addEventListener('click', () => {
          const pickup = document.getElementById('pickupInput')?.value?.trim() || '';
          const dropoff = document.getElementById('dropoffInput')?.value?.trim() || '';
          if (!pickup || !dropoff) {
              showToast('Please enter both pickup and drop-off locations before assigning a driver.');
            return;
          }

          function showModalState(loading, results, empty) {
            if (loadingEl) loadingEl.classList.toggle('d-none', !loading);
            if (resultsEl) resultsEl.classList.toggle('d-none', !results);
            if (emptyEl) emptyEl.classList.toggle('d-none', !empty);
          }

          function getPickupLatLng(cb) {
            if (pickupLatLng && typeof pickupLatLng.lat === 'function') {
              cb(pickupLatLng.lat(), pickupLatLng.lng());
              return;
            }
            if (typeof google === 'undefined' || !google.maps || !google.maps.Geocoder) {
              cb(null, null);
              return;
            }
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: pickup }, (results, status) => {
              if (status === google.maps.GeocoderStatus.OK && results && results[0] && results[0].geometry) {
                const loc = results[0].geometry.location;
                cb(loc.lat(), loc.lng());
              } else {
                cb(null, null);
              }
            });
          }

          const modal = new bootstrap.Modal(modalEl);
          modal.show();
          showModalState(true, false, false);
          nearbyDriversList = [];
          selectedNearbyDriverId = null;
          if (listEl) listEl.innerHTML = '';
          // Reset empty state to default message
          if (emptyEl) {
            const h4 = emptyEl.querySelector('h4');
            const p = emptyEl.querySelector('.text-muted');
            if (h4) h4.textContent = 'No available drivers within 5km.';
            if (p) p.textContent = 'Try again later or assign a driver manually.';
          }

          getPickupLatLng((lat, lng) => {
            if (lat == null || lng == null) {
              showModalState(false, false, true);
              if (emptyEl) {
                const h4 = emptyEl.querySelector('h4');
                const p = emptyEl.querySelector('.text-muted');
                if (h4) h4.textContent = 'Could not find pickup location';
                if (p) p.textContent = 'Please select a valid pickup address from the suggestions.';
              }
              return;
            }
            const url = `api/get_nearby_drivers.php?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}&radius_km=5`;
            fetch(url)
              .then((res) => res.json())
              .then((data) => {
                if (data.success && data.data && data.data.length > 0) {
                  nearbyDriversList = data.data;
                  selectedNearbyDriverId = null;
                  listEl.innerHTML = '';
                  data.data.forEach((d) => {
                    const box = document.createElement('div');
                    box.className = 'border rounded p-3 mb-2 cursor-pointer';
                    box.style.cursor = 'pointer';
                    box.style.borderColor = '#dee2e6';
                    box.style.transition = 'border-color 0.2s, box-shadow 0.2s';
                    box.dataset.driverId = d.id;
                    box.innerHTML = `
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong class="d-block">${(d.full_name || d.name || 'Driver').escapeHtml()}</strong>
                          <span class="text-muted small">${(d.vehicle_make || d.vehicle_brand || 'Vehicle').escapeHtml()}</span>
                        </div>
                      </div>
                    `;
                    box.addEventListener('click', () => {
                      selectedNearbyDriverId = d.id;
                      listEl.querySelectorAll('.border').forEach((b) => {
                        b.style.borderColor = '#dee2e6';
                        b.style.boxShadow = 'none';
                      });
                      box.style.borderColor = '#f37a20';
                      box.style.boxShadow = '0 0 0 2px rgba(243,122,32,0.3)';
                    });
                    listEl.appendChild(box);
                  });
                  showModalState(false, true, false);
                } else {
                  showModalState(false, false, true);
                }
              })
              .catch(() => {
                showModalState(false, false, true);
                const h4 = emptyEl ? emptyEl.querySelector('h4') : null;
                const p = emptyEl ? emptyEl.querySelector('.text-muted') : null;
                if (h4) h4.textContent = 'Something went wrong';
                if (p) p.textContent = 'Try again later or assign a driver manually.';
              });
          });
        });

        if (assignBtn) {
          assignBtn.addEventListener('click', () => {
            if (!selectedNearbyDriverId || !nearbyDriversList.length) {
              showToast('Please select a driver from the list.');
              return;
            }
            const driver = nearbyDriversList.find((d) => d.id == selectedNearbyDriverId);
            if (driver) {
              selectedDriverId = driver.id;
              selectedVehicleNumber = driver.vehicle_number || null;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('searchDriverModal'));
            modal?.hide();
          });
        }
      }

      if (typeof String.prototype.escapeHtml !== 'function') {
        String.prototype.escapeHtml = function () {
          const div = document.createElement('div');
          div.textContent = this;
          return div.innerHTML;
        };
      }

      /* ---------------------- Google Maps ---------------------- */
      function initGoogleMaps() {
        if (typeof google === 'undefined' || !google.maps) {
          setTimeout(initGoogleMaps, 200);
          return;
        }
        const mapEl = document.getElementById('map');
        if (!mapEl) {
          setTimeout(initGoogleMaps, 100);
          return;
        }
        orderMap = new google.maps.Map(mapEl, {
          center: { lat: 53.349805, lng: -6.26031 },
          zoom: 12,
        });
        orderDirectionsService = new google.maps.DirectionsService();
        orderDirectionsRenderer = new google.maps.DirectionsRenderer({ map: orderMap });

        const pickupInput = document.getElementById('pickupInput');
        const dropoffInput = document.getElementById('dropoffInput');

        if (pickupInput && dropoffInput && google.maps.places) {
          // Shared Autocomplete options — DUBLIN-ONLY RESTRICTION (active).
          // Hides suggestions from outside Dublin (e.g. Islamabad, London).
          const placesOptions = {
            componentRestrictions: { country: 'ie' },
            bounds: new google.maps.LatLngBounds(
              { lat: DUBLIN_REGION_BOUNDS.south, lng: DUBLIN_REGION_BOUNDS.west },
              { lat: DUBLIN_REGION_BOUNDS.north, lng: DUBLIN_REGION_BOUNDS.east }
            ),
            strictBounds: true,
          };
          const pickupAC = new google.maps.places.Autocomplete(pickupInput, placesOptions);
          const dropoffAC = new google.maps.places.Autocomplete(dropoffInput, placesOptions);

          pickupAC.addListener('place_changed', () => {
            const place = pickupAC.getPlace();
            if (place && place.geometry) {
              const lat = place.geometry.location.lat();
              const lng = place.geometry.location.lng();
              if (!isWithinDublinRegion(lat, lng)) {
                showToast('Pickup must be within Dublin, Ireland. Please choose a different address.');
                pickupInput.value = '';
                pickupLatLng = null;
                return;
              }
              pickupLatLng = place.geometry.location;
            }
            tryCalculateRoute();
          });
          dropoffAC.addListener('place_changed', () => {
            const place = dropoffAC.getPlace();
            if (place && place.geometry) {
              const lat = place.geometry.location.lat();
              const lng = place.geometry.location.lng();
              if (!isWithinDublinRegion(lat, lng)) {
                showToast('Drop-off must be within Dublin, Ireland. Please choose a different address.');
                dropoffInput.value = '';
                dropoffLatLng = null;
                return;
              }
              dropoffLatLng = place.geometry.location;
            }
            tryCalculateRoute();
          });
        }
      }

      function setupRouteListeners() {
        const pickupInput = document.getElementById('pickupInput');
        const dropoffInput = document.getElementById('dropoffInput');
        const rideDate = document.getElementById('rideDate');
        const rideTime = document.getElementById('rideTime');
        const serviceType = document.getElementById('serviceType');
        const handler = () => tryCalculateRoute();
        [pickupInput, dropoffInput, rideDate, rideTime].forEach((el) => {
          if (el) el.addEventListener('change', handler);
          if (el) el.addEventListener('blur', handler);
        });
        if (serviceType) {
          serviceType.addEventListener('change', () => {
            recalculateFareForCurrentRoute();
          });
        }
      }

      function tryCalculateRoute() {
        const pickup = document.getElementById('pickupInput')?.value;
        const dropoff = document.getElementById('dropoffInput')?.value;
        if (!pickup || !dropoff || !orderDirectionsService) return;

        const req = {
          origin: pickupLatLng || pickup,
          destination: dropoffLatLng || dropoff,
          travelMode: google.maps.TravelMode.DRIVING,
          // Ask Google for every viable route so we can pick the cheapest one
          // below, instead of always taking its "recommended" routes[0].
          provideRouteAlternatives: true,
        };
        orderDirectionsService.route(req, (result, status) => {
          if (status === google.maps.DirectionsStatus.OK) {
            const pickupTimeStr = buildPickupDateTime();
            const rideType = document.getElementById('serviceType')?.value || 'Economy';

            // Pick the shortest/most economical route out of all alternatives
            // Google returned — routes[0] is just Google's default pick and can
            // be a longer, pricier route than another valid alternative.
            let bestIndex = 0;
            let bestFare = Infinity;
            let bestDistanceKm = 0;
            let bestDurationMin = 0;
            result.routes.forEach((route, idx) => {
              const routeLeg = route.legs[0];
              const distKm = routeLeg.distance.value / 1000;
              const durMin = Math.round(routeLeg.duration.value / 60);
              const fare = calculateFare(distKm, durMin, pickupTimeStr, rideType);
              if (fare < bestFare) {
                bestFare = fare;
                bestIndex = idx;
                bestDistanceKm = distKm;
                bestDurationMin = durMin;
              }
            });

            const leg = result.routes[bestIndex].legs[0];

            // DUBLIN-ONLY RESTRICTION (active): reject routes where either end
            // falls outside Dublin, so map markers and fare are never computed
            // for out-of-region addresses.
            if (!isWithinDublinRegion(leg.start_location.lat(), leg.start_location.lng()) ||
                !isWithinDublinRegion(leg.end_location.lat(), leg.end_location.lng())) {
              showToast('Pickup and drop-off must both be within Dublin, Ireland.');
              orderDirectionsRenderer.set('directions', null);
              currentDistance = currentDuration = currentFare = null;
              googleDistance = googleDuration = googleFare = null;
              pickupLatLng = null;
              dropoffLatLng = null;
              const fareEl = document.getElementById('estimatedFare');
              const distEl = document.getElementById('distanceKm');
              const timeEl = document.getElementById('travelTime');
              if (fareEl) fareEl.value = '';
              if (distEl) distEl.value = '';
              if (timeEl) timeEl.value = '';
              return;
            }

            orderDirectionsRenderer.setDirections(result);
            orderDirectionsRenderer.setRouteIndex(bestIndex);
            googleDistance = bestDistanceKm;
            googleDuration = bestDurationMin;
            googleFare = bestFare;
            pickupLatLng = leg.start_location;
            dropoffLatLng = leg.end_location;
            applyFareOverrides();
          }
        });
      }

      function buildPickupDateTime() {
        const d = document.getElementById('rideDate')?.value;
        const t = document.getElementById('rideTime')?.value;
        if (d && t) return `${d}T${t}`;
        const now = new Date();
        return now.toISOString().slice(0, 16);
      }

      function calculateFare(distanceKm, durationMin, pickupTimeStr, rideType) {
        const pickupDate = new Date(pickupTimeStr);
        const hour = pickupDate.getHours();
        const initialFare = 3.0;
        let baseFare, ratePerKm, ratePerMinute;
        if (hour >= 8 && hour < 20) {
          baseFare = 4.4;
          ratePerKm = 1.32;
          ratePerMinute = 0.20;
        } else {
          baseFare = 5.4;
          ratePerKm = 1.81;
          ratePerMinute = 0.30;
        }
        const rawFare = initialFare + baseFare + (distanceKm * ratePerKm) + ((durationMin || 0) * ratePerMinute);
        // Prefer DB-backed multipliers (loaded via loadRideTypes from ride_types table).
        // Fall back to 1.0 when the ride type hasn't been registered in the table.
        const multiplier = (rideTypeMultipliers && rideTypeMultipliers[rideType] != null)
          ? rideTypeMultipliers[rideType]
          : 1.0;
        return Math.round((rawFare * multiplier) * 100) / 100;
      }

      function recalculateFareForCurrentRoute() {
        if (googleDistance == null || googleDuration == null) return;
        const pickupTimeStr = buildPickupDateTime();
        const rideType = document.getElementById('serviceType')?.value || 'Economy';
        googleFare = calculateFare(googleDistance, googleDuration, pickupTimeStr, rideType);
        applyFareOverrides();
      }

      /**
       * Merge Google-derived values with Special Cost overrides and push the
       * effective values to the read-only summary fields + saved payload.
       * If a special input is non-empty, it overrides the corresponding
       * Google value. Empty → fall back to Google.
       */
      function applyFareOverrides() {
        const rawCost = document.getElementById('specialCost')?.value;
        const rawKm   = document.getElementById('specialKm')?.value;
        const rawMin  = document.getElementById('specialMinutes')?.value;

        const spCost = rawCost !== '' && rawCost != null ? parseFloat(rawCost) : NaN;
        const spKm   = rawKm   !== '' && rawKm   != null ? parseFloat(rawKm)   : NaN;
        const spMin  = rawMin  !== '' && rawMin  != null ? parseFloat(rawMin)  : NaN;

        currentFare     = !isNaN(spCost) ? spCost : googleFare;
        currentDistance = !isNaN(spKm)   ? spKm   : googleDistance;
        currentDuration = !isNaN(spMin)  ? spMin  : googleDuration;
        updateSummaryFields();
      }

      function setupSpecialCostListeners() {
        ['specialCost', 'specialKm', 'specialMinutes'].forEach((id) => {
          const el = document.getElementById(id);
          if (el) el.addEventListener('input', applyFareOverrides);
        });
      }

      function updateSummaryFields() {
        const fareEl = document.getElementById('estimatedFare');
        const distEl = document.getElementById('distanceKm');
        const timeEl = document.getElementById('travelTime');
        if (fareEl && currentFare != null) fareEl.value = `€${Number(currentFare).toFixed(2)}`;
        if (distEl && currentDistance != null) distEl.value = Number(currentDistance).toFixed(2);
        if (timeEl && currentDuration != null) timeEl.value = Math.round(Number(currentDuration)).toString();
      }

      /* ---------------------- Schedule Mode ---------------------- */
      function setRideMode(mode) {
        document.getElementById('rideScheduleMode').value = mode;
        const row = document.getElementById('scheduleDateTimeRow');
        const nowBtn = document.getElementById('modeNowBtn');
        const schBtn = document.getElementById('modeScheduleBtn');

        if (mode === 'now') {
          row.style.display = 'none';
          nowBtn.style.background = '#f37a20';
          nowBtn.style.color = '#fff';
          nowBtn.style.borderColor = '#f37a20';
          schBtn.style.background = '#fff';
          schBtn.style.color = '#52525B';
          schBtn.style.borderColor = '#EBEBEB';
          // Clear date/time so buildPickupDateTime falls back to current time
          const d = document.getElementById('rideDate');
          const t = document.getElementById('rideTime');
          if (d) d.value = '';
          if (t) t.value = '';
        } else {
          row.style.display = '';
          schBtn.style.background = '#f37a20';
          schBtn.style.color = '#fff';
          schBtn.style.borderColor = '#f37a20';
          nowBtn.style.background = '#fff';
          nowBtn.style.color = '#52525B';
          nowBtn.style.borderColor = '#EBEBEB';
          // Pre-fill with tomorrow's date and a round hour as a sensible default
          const d = document.getElementById('rideDate');
          const t = document.getElementById('rideTime');
          if (d && !d.value) {
            const tmr = new Date(); tmr.setDate(tmr.getDate() + 1);
            d.value = tmr.toISOString().slice(0, 10);
          }
          if (t && !t.value) {
            const hr = new Date(); hr.setHours(hr.getHours() + 2, 0, 0, 0);
            t.value = hr.toTimeString().slice(0, 5);
          }
        }
      }

      /* ---------------------- Confirm Order ---------------------- */
      function setupConfirmOrder() {
        const btn = document.getElementById('confirmOrderBtn');
        if (btn) {
          btn.addEventListener('click', createOrder);
        }
      }

      // Function to set button loading state
function setButtonLoading(isLoading, customText = null) {
  const btn = document.getElementById('confirmOrderBtn');
  const originalText = btn.innerHTML;
  
  if (isLoading) {
    // Store original text if not already stored
    if (!btn.hasAttribute('data-original-text')) {
      btn.setAttribute('data-original-text', originalText);
    }
    
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'not-allowed';
    btn.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      ${customText || 'Confirming...'}
    `;
  } else {
    btn.disabled = false;
    btn.style.opacity = '';
    btn.style.cursor = '';
    const original = btn.getAttribute('data-original-text') || 'Confirm Order';
    btn.innerHTML = original;
    btn.removeAttribute('data-original-text');
  }
}

async function createOrder() {
  const paxMode = document.getElementById('passengerMode')?.value || 'existing';
  const passengerId = paxMode === 'existing' ? selectedPassengerId : null;
  const customerName = paxMode === 'existing'
    ? (document.getElementById('customerNameInput')?.value?.trim() || '')
    : (document.getElementById('customPassengerName')?.value?.trim() || '');
  const phoneRaw = document.getElementById('customerPhone')?.value?.trim() || '';
  const serviceType = document.getElementById('serviceType')?.value || 'Economy';
  const seats = document.getElementById('seatCount')?.value || '';
  const pickup = document.getElementById('pickupInput')?.value?.trim() || '';
  const dropoff = document.getElementById('dropoffInput')?.value?.trim() || '';
  const scheduleMode = document.getElementById('rideScheduleMode')?.value || 'now';
  const isScheduled = scheduleMode === 'schedule';

  // For Book Now always use the current moment; for Schedule use the picker values.
  let rideDateVal, rideTimeVal;
  if (isScheduled) {
    rideDateVal = document.getElementById('rideDate')?.value || '';
    rideTimeVal = document.getElementById('rideTime')?.value || '';
  } else {
    const now = new Date();
    rideDateVal = now.toISOString().slice(0, 10);          // 'YYYY-MM-DD'
    rideTimeVal = now.toTimeString().slice(0, 5);          // 'HH:MM'
  }
  const pickupTimeStr = buildPickupDateTime();

  const dialCode = document.getElementById('selectedDialCode')?.textContent || '+353';
  const phone = phoneRaw
    ? phoneRaw.startsWith('+')
      ? phoneRaw
      : dialCode + phoneRaw.replace(/^0+/, '')
    : '';

  if (!customerName) {
    showToast(paxMode === 'existing' ? 'Please search and select a passenger' : 'Please enter passenger name');
    return;
  }

  if (paxMode === 'existing' && !passengerId) {
    showToast('Please select a passenger from the search results');
    return;
  }

  if (!phone) {
    showToast('Please enter customer phone');
    return;
  }

  if (isScheduled && (!rideDateVal || !rideTimeVal)) {
    showToast('Please select a date and time for the scheduled ride');
    return;
  }

  if (isScheduled) {
    const scheduledDt = new Date(rideDateVal + 'T' + rideTimeVal);
    if (scheduledDt <= new Date()) {
      showToast('Scheduled time must be in the future');
      return;
    }
  }

  if (!seats) {
    showToast('Please select seats');
    return;
  }

  if (!pickup || !dropoff) {
    showToast('Please enter pickup and drop-off locations');
    return;
  }

  if (!currentDistance || !currentDuration || !currentFare || !pickupLatLng || !dropoffLatLng) {
    showToast('Please wait for route/fare calculation to finish.');
    return;
  }

  // DUBLIN-ONLY RESTRICTION (active): final guard — never confirm an order
  // whose pickup/drop-off falls outside Dublin, Ireland.
  if (!isWithinDublinRegion(pickupLatLng.lat(), pickupLatLng.lng()) ||
      !isWithinDublinRegion(dropoffLatLng.lat(), dropoffLatLng.lng())) {
    showToast('Pickup and drop-off must both be within Dublin, Ireland.');
    return;
  }

        // Payment method: Cash → 'cash', Pay with Stripe → 'prepaid'
        const paymentChoice = document.querySelector('input[name="paymentMethod"]:checked');
        const paymentMethod = (paymentChoice && paymentChoice.value === 'stripe') ? 'prepaid' : 'cash';

        const isCustomPax = paxMode === 'custom';
        const finalName = customerName;

        const payload = {
          user_id: passengerId || null,
          customer_name: finalName,
          phone_number: phone,
          passenger_name: finalName,
          passenger_phone: phone,
          skip_passenger_lookup: isCustomPax,
          source_override: isCustomPax ? 'Powercabs Dispatch' : null,
          service_type: serviceType,
          seats,
          date: rideDateVal,
          time: rideTimeVal,
          pickup_addr: pickup,
          dest_addr: dropoff,
          pickup_lat: pickupLatLng ? pickupLatLng.lat() : null,
          pickup_lng: pickupLatLng ? pickupLatLng.lng() : null,
          dest_lat: dropoffLatLng ? dropoffLatLng.lat() : null,
          dest_lng: dropoffLatLng ? dropoffLatLng.lng() : null,
          distance_km: currentDistance,
          duration_min: currentDuration,
          fare_eur: currentFare,
          payment_method: paymentMethod,
          service_type_display: serviceType,
          scheduled_at: pickupTimeStr,
          is_scheduled: isScheduled,
          tz_offset_min: new Date().getTimezoneOffset(),
          driver_id: selectedDriverId || null,
          vehicle_number: selectedVehicleNumber || null,
        };

          // Set loading state before API call
  setButtonLoading(true, 'Creating Order...');

        try {
          const res = await fetch('api/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });

if (res.status === 401) {
      window.location.href = '/';
      return;
    }

          const data = await res.json();
          if (data.success) {
            const modal = new bootstrap.Modal(
  document.getElementById('orderCreatedModal')
);
modal.show();

      // Remove existing event listener to prevent multiple redirects
      const goToOrdersBtn = document.getElementById('goToOrdersBtn');
      const newBtn = goToOrdersBtn.cloneNode(true);
      goToOrdersBtn.parentNode.replaceChild(newBtn, goToOrdersBtn);
      
      newBtn.addEventListener('click', () => {
        window.location.href = 'order.php';
      });

          } else {
            showToast('Error creating order: ' + (data.error || 'Unknown error'));
          }
        } catch (err) {
          console.error('Order create error', err);
          showToast('Failed to create order.');
        } finally {
    // Reset button state after API call completes (success or error)
    setButtonLoading(false);
  }
      }

      function initOrderPageClearFields() {
        const yesBtn = document.getElementById('clearFieldsYesBtn');
        if (yesBtn) {
          yesBtn.addEventListener('click', function (ev) {
            ev.preventDefault();
            try { window.clearAllFields(); }
            catch (e) { console.error('clearAllFields failed:', e); }
          });
        } else {
          console.warn('clearFieldsYesBtn not found in DOM');
        }

        // Show the Stripe checkout link only when "Pay with Stripe" is selected.
        const stripeLink = document.getElementById('stripePayLink');
        const syncStripeLink = () => {
          const stripeSelected = document.getElementById('paymentStripe')?.checked;
          if (!stripeLink) return;
          stripeLink.classList.toggle('d-none', !stripeSelected);
          stripeLink.classList.toggle('d-inline-flex', !!stripeSelected);
        };
        document.querySelectorAll('input[name="paymentMethod"]').forEach((r) => {
          r.addEventListener('change', syncStripeLink);
        });
        syncStripeLink();
      }

      window.clearAllFields = function clearAllFields() {
        // Reset passenger mode to Existing
        if (typeof switchPaxMode === 'function') switchPaxMode('existing');

        const textIds = [
          'customerNameInput', 'customPassengerName', 'customerPhone', 'customerId',
          'pickupInput', 'dropoffInput',
          'estimatedFare', 'distanceKm', 'travelTime',
          'rideDate', 'rideTime',
          'specialCost', 'specialKm', 'specialMinutes'
        ];
        textIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        // Reset cached route/fare state so a new order starts clean
        currentDistance = currentDuration = currentFare = null;
        googleDistance = googleDuration = googleFare = null;

        // Reset country code selector to Ireland
        if (typeof selectCountry === 'function') selectCountry('ie');

        // Reset pill groups: Service Type → Economy, Seats → none
        const serviceTypeInput = document.getElementById('serviceType');
        if (serviceTypeInput) serviceTypeInput.value = 'Economy';
        const seatCountInput = document.getElementById('seatCount');
        if (seatCountInput) seatCountInput.value = '';
        document.querySelectorAll('[data-pill-target]').forEach((group) => {
          const targetId = group.dataset.pillTarget;
          const hidden = targetId ? document.getElementById(targetId) : null;
          const wantValue = hidden ? hidden.value : '';
          group.querySelectorAll('button[data-value]').forEach((b) => {
            b.classList.toggle('active', !!wantValue && b.dataset.value === wantValue);
          });
        });

        const checkboxIds = [
          'creditCard', 'personWithDisabilities', 'childSeat',
          'extraLuggage', 'petsAllowed', 'delivery'
        ];
        checkboxIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.checked = false;
        });

        // Reset payment method back to Cash and hide the Stripe link
        const cashRadio = document.getElementById('paymentCash');
        if (cashRadio) cashRadio.checked = true;
        const stripeLink = document.getElementById('stripePayLink');
        if (stripeLink) {
          stripeLink.classList.add('d-none');
          stripeLink.classList.remove('d-inline-flex');
        }

        const suggestions = document.getElementById('customerSuggestions');
        if (suggestions) {
          suggestions.innerHTML = '';
          suggestions.style.display = 'none';
        }

        try {
          if (typeof orderDirectionsRenderer !== 'undefined' && orderDirectionsRenderer) {
            orderDirectionsRenderer.set('directions', null);
          }
        } catch (e) { /* ignore map cleanup errors */ }

        try {
          if (typeof currentDistance !== 'undefined') currentDistance = null;
          if (typeof currentDuration !== 'undefined') currentDuration = null;
          if (typeof currentFare !== 'undefined') currentFare = null;
        } catch (e) { /* vars may not exist */ }

        const modalEl = document.getElementById('clearFieldsModal');
        if (modalEl && window.bootstrap) {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }

        if (typeof showToast === 'function') {
          showToast('Form cleared', true);
        }

        const firstField = document.getElementById('customerNameInput');
        if (firstField) firstField.focus();
      };

      // ── Country-code selector ──
      const COUNTRY_LIST = [
        { iso: 'ie', name: 'Ireland',        dial: '+353' },
        { iso: 'gb', name: 'United Kingdom', dial: '+44'  },
        { iso: 'us', name: 'United States',  dial: '+1'   },
        { iso: 'pl', name: 'Poland',         dial: '+48'  },
        { iso: 'ro', name: 'Romania',        dial: '+40'  },
        { iso: 'lt', name: 'Lithuania',       dial: '+370' },
        { iso: 'lv', name: 'Latvia',         dial: '+371' },
        { iso: 'ee', name: 'Estonia',        dial: '+372' },
        { iso: 'de', name: 'Germany',        dial: '+49'  },
        { iso: 'fr', name: 'France',         dial: '+33'  },
        { iso: 'es', name: 'Spain',          dial: '+34'  },
        { iso: 'it', name: 'Italy',          dial: '+39'  },
        { iso: 'pt', name: 'Portugal',       dial: '+351' },
        { iso: 'nl', name: 'Netherlands',    dial: '+31'  },
        { iso: 'be', name: 'Belgium',        dial: '+32'  },
        { iso: 'at', name: 'Austria',        dial: '+43'  },
        { iso: 'ch', name: 'Switzerland',    dial: '+41'  },
        { iso: 'se', name: 'Sweden',         dial: '+46'  },
        { iso: 'dk', name: 'Denmark',        dial: '+45'  },
        { iso: 'no', name: 'Norway',         dial: '+47'  },
        { iso: 'fi', name: 'Finland',        dial: '+358' },
        { iso: 'cz', name: 'Czech Republic', dial: '+420' },
        { iso: 'hu', name: 'Hungary',        dial: '+36'  },
        { iso: 'sk', name: 'Slovakia',       dial: '+421' },
        { iso: 'hr', name: 'Croatia',        dial: '+385' },
        { iso: 'bg', name: 'Bulgaria',       dial: '+359' },
        { iso: 'in', name: 'India',          dial: '+91'  },
        { iso: 'pk', name: 'Pakistan',       dial: '+92'  },
        { iso: 'ng', name: 'Nigeria',        dial: '+234' },
        { iso: 'za', name: 'South Africa',   dial: '+27'  },
        { iso: 'ph', name: 'Philippines',    dial: '+63'  },
        { iso: 'br', name: 'Brazil',         dial: '+55'  },
        { iso: 'au', name: 'Australia',      dial: '+61'  },
        { iso: 'cn', name: 'China',          dial: '+86'  },
        { iso: 'ca', name: 'Canada',         dial: '+1'   },
      ];

      function selectCountry(iso) {
        const c = COUNTRY_LIST.find(x => x.iso === iso);
        if (!c) return;
        document.getElementById('selectedFlag').src = 'https://flagcdn.com/w20/' + c.iso + '.png';
        document.getElementById('selectedFlag').alt = c.iso.toUpperCase();
        document.getElementById('selectedDialCode').textContent = c.dial;
        document.getElementById('countryDropdown').style.display = 'none';
        document.querySelectorAll('#countryOptions .country-option').forEach(el => {
          el.classList.toggle('is-selected', el.dataset.iso === iso);
        });
      }

      function setPhoneFromFullNumber(fullPhone) {
        const phoneInput = document.getElementById('customerPhone');
        if (!fullPhone) { phoneInput.value = ''; return; }
        const sorted = [...COUNTRY_LIST].sort((a, b) => b.dial.length - a.dial.length);
        for (const c of sorted) {
          if (fullPhone.startsWith(c.dial)) {
            selectCountry(c.iso);
            phoneInput.value = fullPhone.slice(c.dial.length);
            return;
          }
        }
        phoneInput.value = fullPhone.replace(/^\+/, '');
      }

      // Both country selectors bind a document-level outside-click handler,
      // which — unlike element-specific listeners — survives on `document`
      // itself across SPA revisits. Track and remove the previous one before
      // adding a new one each time these run, so repeat visits to order.php
      // don't accumulate duplicate handlers.
      let _countrySelectorOutsideClick = null;
      let _driverCountrySelectorOutsideClick = null;

      function initCountrySelector() {
        const btn = document.getElementById('countryCodeBtn');
        const dropdown = document.getElementById('countryDropdown');
        const search = document.getElementById('countrySearch');
        const optionsContainer = document.getElementById('countryOptions');
        if (!btn || !dropdown) return;

        function renderOptions(filter) {
          const q = (filter || '').toLowerCase();
          optionsContainer.innerHTML = '';
          const selected = document.getElementById('selectedDialCode').textContent;
          COUNTRY_LIST.filter(c =>
            !q || c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.iso.includes(q)
          ).forEach(c => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'country-option' + (c.dial === selected && c.iso === document.getElementById('selectedFlag').alt.toLowerCase() ? ' is-selected' : '');
            el.dataset.iso = c.iso;
            el.innerHTML = '<img src="https://flagcdn.com/w20/' + c.iso + '.png" width="20" height="15" alt="' + c.iso.toUpperCase() + '"> ' +
              '<span>' + c.name + '</span>' +
              '<span class="dial">' + c.dial + '</span>';
            el.addEventListener('click', () => {
              selectCountry(c.iso);
              document.getElementById('customerPhone').focus();
            });
            optionsContainer.appendChild(el);
          });
        }

        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const open = dropdown.style.display === 'block';
          dropdown.style.display = open ? 'none' : 'block';
          if (!open) {
            search.value = '';
            renderOptions('');
            setTimeout(() => search.focus(), 50);
          }
        });

        search.addEventListener('input', (e) => {
          renderOptions(e.target.value);
        });
        search.addEventListener('click', (e) => e.stopPropagation());

        if (_countrySelectorOutsideClick) document.removeEventListener('click', _countrySelectorOutsideClick);
        _countrySelectorOutsideClick = (e) => {
          const wrapper = document.getElementById('countryCodeWrapper');
          if (wrapper && !wrapper.contains(e.target)) {
            dropdown.style.display = 'none';
          }
        };
        document.addEventListener('click', _countrySelectorOutsideClick);

        renderOptions('');
      }

      // ── Driver country-code selector (modal) ──
      function initDriverCountrySelector() {
        const btn = document.getElementById('driverCountryCodeBtn');
        const dropdown = document.getElementById('driverCountryDropdown');
        const search = document.getElementById('driverCountrySearch');
        const optionsContainer = document.getElementById('driverCountryOptions');
        if (!btn || !dropdown) return;

        function renderOptions(filter) {
          const q = (filter || '').toLowerCase();
          optionsContainer.innerHTML = '';
          const selectedDial = document.getElementById('driverSelectedDialCode').textContent;
          COUNTRY_LIST.filter(c =>
            !q || c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.iso.includes(q)
          ).forEach(c => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'country-option' + (c.dial === selectedDial && c.iso === document.getElementById('driverSelectedFlag').alt.toLowerCase() ? ' is-selected' : '');
            el.dataset.iso = c.iso;
            el.innerHTML = '<img src="https://flagcdn.com/w20/' + c.iso + '.png" width="20" height="15" alt="' + c.iso.toUpperCase() + '"> ' +
              '<span>' + c.name + '</span>' +
              '<span class="dial">' + c.dial + '</span>';
            el.addEventListener('click', () => { selectDriverCountry(c.iso); });
            optionsContainer.appendChild(el);
          });
        }

        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const open = dropdown.style.display === 'block';
          dropdown.style.display = open ? 'none' : 'block';
          if (!open) { search.value = ''; renderOptions(''); setTimeout(() => search.focus(), 50); }
        });

        search.addEventListener('input', (e) => { renderOptions(e.target.value); });
        search.addEventListener('click', (e) => e.stopPropagation());

        if (_driverCountrySelectorOutsideClick) document.removeEventListener('click', _driverCountrySelectorOutsideClick);
        _driverCountrySelectorOutsideClick = (e) => {
          const wrapper = document.getElementById('driverCountryCodeWrapper');
          if (wrapper && !wrapper.contains(e.target)) {
            dropdown.style.display = 'none';
          }
        };
        document.addEventListener('click', _driverCountrySelectorOutsideClick);

        renderOptions('');
      }

      // Runs all of this page's former DOMContentLoaded/IIFE blocks in
      // sequence, so a revisit under SPA navigation re-wires everything
      // against the freshly-swapped-in markup exactly like a true page load did.
      function initOrderPage() {
        initOrderPageCore();
        initOrderPageClearFields();
        initCountrySelector();
        initDriverCountrySelector();
      }
      document.addEventListener('DOMContentLoaded', initOrderPage);

      window.SPA_PAGES = window.SPA_PAGES || {};
      window.SPA_PAGES['order.php'] = { init: initOrderPage, cleanup: null };
