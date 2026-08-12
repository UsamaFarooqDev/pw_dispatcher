// Global variables for Google Maps
let oaMap, oaDirectionsService, oaDirectionsRenderer;
let currentRideId = null;
let oaCurrentDistance = null;
let oaCurrentDuration = null;
let oaCurrentFare = null;
let hasDbFare = false;
let passengersList = [];
let corporateEmployeesList = [];
let isCorporateMode = false;
let isCorporateViewMode = false;
let currentCorpId = null;
let currentPickupLat = null;
let currentPickupLng = null;
let currentDropLat = null;
let currentDropLng = null;

// Live driver tracking
let assignedDriverId = null;
let driverLiveMarker = null;
let driverTrackingInterval = null;
let mapReadyForTracking = false;

// View mode (Assigned → View Details)
let isViewMode = false;
let viewModePickupMarker = null;
let viewModeDropoffMarker = null;

// Pending route — set before map loads, consumed by initGoogleMaps
let pendingPickupAddr = null;
let pendingDropoffAddr = null;

// Driver → Pickup road route
let driverRouteRenderer = null;
let lastDriverRouteLat = null;
let lastDriverRouteLng = null;
const DRIVER_ROUTE_THRESHOLD_M = 40; // recalculate only when driver moves ≥ 40 m

// Smooth marker animation — glide across the full poll interval (continuous motion)
let driverAnimTimer = null;
let driverLastPosition = null;          // previous lat/lng for bearing when API heading is absent
let hasCenteredOnDriver = false;        // auto-fit map to driver on first live fix
const DRIVER_POLL_MS = 3000;            // match Live Map poll cadence
const OA_MARKER_ANIM_MS = DRIVER_POLL_MS;  // animate across the whole interval so the car never freezes
const OA_TELEPORT_SNAP_METERS = 1000;      // snap instead of animating across huge GPS jumps
const OA_ROUTE_REFRESH_MS = 15000;
const OA_ROUTE_REDRAW_METERS = 150;
let driverTrackPolyline = null;         // road route from driver → destination (Live Map style)
let driverTrackMeta = null;             // throttle state for route redraws

// Route progress visualization (switches on when ride status → on_trip)
let currentRoutePath = [];
let completedPolyline = null;
let remainingPolyline = null;
let routeProgressActive = false;

// Ride status state machine
let currentRideStatus = null;

// Driver heading for marker rotation
let currentDriverBearing = 0;

async function fetchAndUpdateDriverMarker(driverId) {
  if (!oaMap || !driverId) return;
  try {
    let lat, lng, driverName = 'Driver', vehicle = '', trackDriver = null;

    if (isViewMode && currentRideId) {
      // Active ride: same GPS priority as Live Map (ride GPS → driver table fallback)
      const resp = await fetch(`api/get_ride_location.php?ride_id=${encodeURIComponent(currentRideId)}`, { cache: 'no-store' });
      if (!resp.ok) return;
      const result = await resp.json();
      if (!result.success || !result.data) return;

      const loc = result.data;
      if (loc.lat === null || loc.lng === null) return;
      lat = parseFloat(loc.lat);
      lng = parseFloat(loc.lng);
      if (isNaN(lat) || isNaN(lng)) return;

      driverName = loc.full_name || loc.name || 'Driver';
      vehicle    = [loc.vehicle_number, loc.vehicle_make].filter(Boolean).join(' · ');
      trackDriver = loc;

      // Prefer compass heading from the driver app; fall back to computed bearing
      const apiHeading = parseFloat(loc.heading ?? loc.driver_heading ?? NaN);
      if (!isNaN(apiHeading) && apiHeading !== 0) {
        currentDriverBearing = apiHeading;
      } else if (driverLastPosition) {
        const comp = oaComputeBearing(driverLastPosition.lat, driverLastPosition.lng, lat, lng);
        if (comp !== null) currentDriverBearing = comp;
      }

      updateDispatcherOverlayFromDriver(loc);

      const newStatus = (loc.status || '').toLowerCase();
      if (newStatus && newStatus !== currentRideStatus) {
        handleRideStatusChange(currentRideStatus, newStatus);
        currentRideStatus = newStatus;
      }
    } else {
      // Idle/fleet: GPS in drivers.current_lat/lng
      const resp = await fetch(`api/get_live_drivers.php?driver_id=${encodeURIComponent(driverId)}`, { cache: 'no-store' });
      if (!resp.ok) return;
      const result = await resp.json();
      if (!result.success || !result.data || result.data.length === 0) return;
      const loc = result.data[0];
      lat = parseFloat(loc.current_lat ?? loc.lat);
      lng = parseFloat(loc.current_lng ?? loc.lng);
      driverName = loc.full_name || loc.name || 'Driver';
      vehicle    = loc.vehicle_number || loc.vehicle_make || '';
      const apiHeading = parseFloat(loc.heading ?? loc.driver_heading ?? NaN);
      if (!isNaN(apiHeading) && apiHeading !== 0) {
        currentDriverBearing = apiHeading;
      } else if (driverLastPosition) {
        const comp = oaComputeBearing(driverLastPosition.lat, driverLastPosition.lng, lat, lng);
        if (comp !== null) currentDriverBearing = comp;
      }
    }

    if (isNaN(lat) || isNaN(lng)) return;

    const icon = oaBuildDriverIcon(currentDriverBearing, currentRideStatus);

    if (driverLiveMarker) {
      animateDriverMarker(lat, lng, icon);
    } else {
      driverLiveMarker = new google.maps.Marker({
        position: { lat, lng },
        map: oaMap,
        icon,
        title: driverName,
        zIndex: 20,
      });
      const infoWin = new google.maps.InfoWindow({
        content: `<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;padding:4px 2px;min-width:160px;">
          <div style="font-weight:700;color:#18181B;font-size:13px;">${driverName}</div>
          ${vehicle ? `<div style="font-size:11px;color:#71717A;margin-top:2px;">${vehicle}</div>` : ''}
          <div style="margin-top:4px;display:inline-flex;align-items:center;gap:5px;">
            <span style="width:7px;height:7px;border-radius:50%;background:#22C55E;display:inline-block;"></span>
            <span style="font-size:11px;color:#22C55E;font-weight:600;">Live</span>
          </div>
        </div>`,
      });
      driverLiveMarker.addListener('click', () => infoWin.open(oaMap, driverLiveMarker));
    }

    driverLastPosition = { lat, lng };

    // Auto-center on driver the first time we get a live fix in view mode
    if (isViewMode && !hasCenteredOnDriver) {
      hasCenteredOnDriver = true;
      hideMapLoadingSkeleton();
      oaMap.panTo({ lat, lng });
      if (oaMap.getZoom() < 14) oaMap.setZoom(14);
    }

    // Draw the road track to pickup/destination (Live Map style)
    if (isViewMode && trackDriver && !routeProgressActive) {
      maybeUpdateDriverTrack(lat, lng, trackDriver);
    }

    // Route progress: active once trip starts
    if (routeProgressActive && currentRoutePath.length) {
      updateRouteProgress(lat, lng);
    }
  } catch (e) {
    console.warn('Driver live tracking error:', e);
  }
}

// Smooth interpolation across the full poll interval, driven by requestAnimationFrame
// (≈60 fps) so the marker glides continuously instead of jumping to the new point and
// freezing until the next poll. Rotates icon to face direction of travel.
function animateDriverMarker(toLat, toLng, icon) {
  if (!driverLiveMarker) return;
  if (driverAnimTimer) { cancelAnimationFrame(driverAnimTimer); driverAnimTimer = null; }

  const fromPos = driverLiveMarker.getPosition();
  if (!fromPos) { driverLiveMarker.setPosition({ lat: toLat, lng: toLng }); return; }

  const fromLat = fromPos.lat();
  const fromLng = fromPos.lng();
  if (fromLat === toLat && fromLng === toLng) return;

  if (icon) driverLiveMarker.setIcon(icon);

  // Guard against teleports — snap instead of animating across the city
  let jumpMeters = 0;
  if (google.maps.geometry && google.maps.geometry.spherical) {
    jumpMeters = google.maps.geometry.spherical.computeDistanceBetween(
      new google.maps.LatLng(fromLat, fromLng),
      new google.maps.LatLng(toLat, toLng)
    );
  }
  if (jumpMeters > OA_TELEPORT_SNAP_METERS) {
    driverLiveMarker.setPosition({ lat: toLat, lng: toLng });
    return;
  }

  const startTs = performance.now();
  const animate = (nowTs) => {
    const f = Math.min((nowTs - startTs) / OA_MARKER_ANIM_MS, 1);
    driverLiveMarker.setPosition({
      lat: fromLat + (toLat - fromLat) * f,
      lng: fromLng + (toLng - fromLng) * f,
    });
    if (f < 1) {
      driverAnimTimer = requestAnimationFrame(animate);
    } else {
      driverAnimTimer = null;
    }
  };
  driverAnimTimer = requestAnimationFrame(animate);
}

function startDriverTracking() {
  if (!assignedDriverId || !mapReadyForTracking) return;
  if (driverTrackingInterval) return;  // already running
  if (!isViewMode && driverRouteRenderer && oaMap) driverRouteRenderer.setMap(oaMap);
  fetchAndUpdateDriverMarker(assignedDriverId);
  driverTrackingInterval = setInterval(() => fetchAndUpdateDriverMarker(assignedDriverId), DRIVER_POLL_MS);
}

function stopDriverTracking() {
  if (driverTrackingInterval) {
    clearInterval(driverTrackingInterval);
    driverTrackingInterval = null;
  }
  if (driverAnimTimer) {
    cancelAnimationFrame(driverAnimTimer);
    driverAnimTimer = null;
  }
  if (driverLiveMarker) {
    driverLiveMarker.setMap(null);
    driverLiveMarker = null;
  }
  driverLastPosition = null;
  hasCenteredOnDriver = false;
  lastDriverRouteLat = null;
  lastDriverRouteLng = null;
  if (driverRouteRenderer) driverRouteRenderer.setMap(null);
  clearDriverTrack();
  clearRouteProgress();
}

// Wait for Google Maps API to load (called by script callback when API is ready)
function oaInitGoogleMaps() {
  if (typeof google === 'undefined' || !google.maps) {
    setTimeout(oaInitGoogleMaps, 200);
    return;
  }

  const mapElement = document.getElementById('map');
  if (!mapElement) {
    setTimeout(oaInitGoogleMaps, 100);
    return;
  }

  oaMap = new google.maps.Map(mapElement, {
    center: { lat: 53.349805, lng: -6.26031 }, // Dublin default
    zoom: 13,
  });

  oaDirectionsService = new google.maps.DirectionsService();
  // suppressMarkers: true so our custom green/red pins don't clash with Google's A/B pins
  oaDirectionsRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: true });
  oaDirectionsRenderer.setMap(oaMap);

  // Driver → Pickup: solid orange road route (assign mode only; view mode uses driverTrackPolyline)
  driverRouteRenderer = new google.maps.DirectionsRenderer({
    suppressMarkers: true,
    polylineOptions: {
      strokeColor: '#f37a20',
      strokeOpacity: 0.9,
      strokeWeight: 5,
    },
  });
  if (!isViewMode) driverRouteRenderer.setMap(oaMap);

  // Signal that the map is ready and start driver tracking if a driver is already set
  mapReadyForTracking = true;
  startDriverTracking();

  // View mode: trigger resize after layout has been applied
  if (isViewMode) google.maps.event.trigger(oaMap, 'resize');

  // Calculate route if ride data loaded before map was ready
  if (pendingPickupAddr && pendingDropoffAddr) {
    calculateRouteAndFare(pendingPickupAddr, pendingDropoffAddr);
    pendingPickupAddr = null;
    pendingDropoffAddr = null;
  }

  // Attach Places Autocomplete when API is ready (fixes suggestions not appearing)
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');
  if (pickupInput && dropoffInput && google.maps.places) {
    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
    const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);
    pickupAutocomplete.addListener('place_changed', () => {
      if (dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
    dropoffAutocomplete.addListener('place_changed', () => {
      if (pickupInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
  }
}

// Swaps the static "Assigned Orders" navbar title for the specific ride
// being viewed/assigned once its data is in, e.g. "Assigned Ride – Jane Doe".
// Handles both a normal ride's `passenger_name` and a corporate ride's
// `employee` string.
function updateNavbarRideTitle(ride) {
  const pageTitleEl = document.getElementById('pageTitle');
  if (!pageTitleEl || !ride) return;
  const name = (ride.passenger_name && ride.passenger_name !== 'N/A')
    ? ride.passenger_name
    : ((ride.employee && String(ride.employee).trim()) || 'Passenger');
  pageTitleEl.textContent = `Assigned Ride – ${name}`;
}

async function initOrderAssignedCore() {

  // Safety fallback: remove page loader after 6s no matter what
  setTimeout(() => { const pl = document.getElementById('oaPageLoader'); if (pl) { pl.style.opacity='0'; pl.style.transition='opacity .3s'; setTimeout(()=>pl.remove(),350); } }, 6000);

  // Detect mode from URL early — view mode skips heavy passenger/driver loading
  const urlParams = new URLSearchParams(window.location.search);
  const rideId = urlParams.get('id');
  const corpId = urlParams.get('corp_id');
  isCorporateMode = !!corpId;
  isCorporateViewMode = isCorporateMode && urlParams.get('view') === '1';
  isViewMode = !!rideId && !isCorporateMode && urlParams.get('view') === '1';
  const isNormalViewMode = isViewMode;

  if (!isNormalViewMode) {
    // Edit/assign mode: load passengers, drivers, setup form fields
    loadDrivers();

    document.querySelectorAll('input[readonly]').forEach(input => {
      input.removeAttribute('readonly');
      input.classList.remove('bg-light');
      input.classList.add('editable');
    });
    document.querySelectorAll('.editable-field').forEach(field => {
      field.setAttribute('contenteditable', 'true');
      field.classList.remove('bg-light');
      field.classList.add('editable');
    });

    const phoneReadonly = document.getElementById('phoneReadonly');
    const phoneEditable = document.getElementById('phoneEditable');
    if (phoneReadonly) phoneReadonly.style.display = 'none';
    if (phoneEditable) phoneEditable.style.display = 'flex';

    const serviceReadonly = document.getElementById('serviceReadonly');
    const serviceEditable = document.getElementById('serviceEditable');
    if (serviceReadonly) serviceReadonly.style.display = 'none';
    if (serviceEditable) serviceEditable.style.display = 'block';

    const dateReadonly = document.getElementById('dateReadonly');
    const dateEditable = document.getElementById('dateEditable');
    if (dateReadonly) dateReadonly.style.display = 'none';
    if (dateEditable) dateEditable.style.display = 'block';

    const timeReadonly = document.getElementById('timeReadonly');
    const timeEditable = document.getElementById('timeEditable');
    if (timeReadonly) timeReadonly.style.display = 'none';
    if (timeEditable) timeEditable.style.display = 'block';

    const creditCard = document.getElementById('creditCard');
    const extraLuggage = document.getElementById('extraLuggage');
    if (creditCard) creditCard.disabled = false;
    if (extraLuggage) extraLuggage.disabled = false;

    const driverServiceReadonly = document.getElementById('driverServiceReadonly');
    const driverServiceEditable = document.getElementById('driverServiceEditable');
    if (driverServiceReadonly) driverServiceReadonly.style.display = 'none';
    if (driverServiceEditable) driverServiceEditable.style.display = 'block';

    const driverPhoneReadonly = document.getElementById('driverPhoneReadonly');
    const driverPhoneEditable = document.getElementById('driverPhoneEditable');
    if (driverPhoneReadonly) driverPhoneReadonly.style.display = 'none';
    if (driverPhoneEditable) driverPhoneEditable.style.display = 'block';

    document.getElementById('assignToMeBtn')?.remove();
    document.getElementById('goBackBtn')?.remove();

    if (isCorporateMode) {
      const passengerLabel = document.querySelector('label[for="customerNameSelect"]')
        || document.querySelector('#customerNameSelect')?.previousElementSibling;
      if (passengerLabel && passengerLabel.tagName === 'LABEL') {
        passengerLabel.textContent = 'Corporate Employee';
      }
      const customerNameSelect = document.getElementById('customerNameSelect');
      if (customerNameSelect) {
        customerNameSelect.innerHTML = '<option value="">Select corporate employee from list</option>';
      }
      await loadCorporateRide(corpId);
      setupCorporateEmployeeSelect();
    } else {
      await loadPassengers();
      setupPassengerSelect();
    }
  } else {
    // View mode: show loading skeleton on the map immediately
    showMapLoadingSkeleton();
    setTimeout(hideMapLoadingSkeleton, 5000);
  }

  if (rideId && !isCorporateMode) {
    try {
      const response = await fetch(`api/get_ride.php?id=${encodeURIComponent(rideId)}`);
      if (response.status === 401) { window.location.href = '/'; return; }
      if (!response.ok) {
        throw new Error('Failed to fetch ride data');
      }

      const result = await response.json();
      if (result.success && result.data) {
        const ride = result.data;
        updateNavbarRideTitle(ride);

        const rideIsCorporate = String(ride.source || '').toLowerCase().startsWith('corporate');
        if (rideIsCorporate) {
          isCorporateMode = true;
          currentCorpId = rideId;
          const passengerLabel = document.querySelector('label[for="customerNameSelect"]')
            || document.querySelector('#customerNameSelect')?.previousElementSibling;
          if (passengerLabel && passengerLabel.tagName === 'LABEL') {
            passengerLabel.textContent = 'Corporate Employee';
          }
        }

        // Prefill Customer Details (passenger from dropdown)
        const customerNameSelect = document.getElementById('customerNameSelect');
        if (customerNameSelect && ride.user_id) {
          const exists = Array.from(customerNameSelect.options)
            .some((o) => String(o.value) === String(ride.user_id));
          if (!exists) {
            const opt = document.createElement('option');
            opt.value = ride.user_id;
            opt.textContent = (ride.passenger_name && ride.passenger_name !== 'N/A')
              ? ride.passenger_name : 'Passenger';
            customerNameSelect.appendChild(opt);
            // Keep passengersList in sync so the change-handler can resolve phone.
            if (Array.isArray(passengersList)) {
              passengersList.push({
                id: ride.user_id,
                name: ride.passenger_name,
                phone: ride.passenger_phone,
                email: ride.passenger_email,
              });
            }
          }
          customerNameSelect.value = String(ride.user_id);
        }
        const customerId = document.getElementById('customerId');
        if (customerId) customerId.value = ride.user_id || '';

        const phoneNumber = document.getElementById('phoneNumber');
        if (phoneNumber && ride.passenger_phone && ride.passenger_phone !== 'N/A') {
          // Remove country code if present, as it's already in the static field
          let phone = String(ride.passenger_phone);
          if (phone.startsWith('+353')) {
            phone = phone.substring(4).trim();
          } else if (phone.startsWith('353')) {
            phone = phone.substring(3).trim();
          }
          phoneNumber.value = phone;
        }

        if (rideIsCorporate && customerNameSelect) {
          customerNameSelect.disabled = true;
        }

        const serviceType = document.getElementById('serviceType');
        if (serviceType && (ride.ride_type || ride.service_type)) {
          const value = (ride.ride_type || ride.service_type).trim();
          const hasOption = Array.from(serviceType.options).some((o) => o.value === value);
          if (!hasOption && value) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            serviceType.appendChild(opt);
          }
          serviceType.value = value;
        }

        // Prefill Date and Time — use scheduled_at for pre-orders, created_at for instant rides
        const timeSource = ride.scheduled_at || ride.created_at;
        if (timeSource) {
          const rideDate = new Date(timeSource);

          const dateInput = document.getElementById('rideDate');
          if (dateInput && !isNaN(rideDate.getTime())) {
            dateInput.value = rideDate.getFullYear() + '-' +
              String(rideDate.getMonth() + 1).padStart(2, '0') + '-' +
              String(rideDate.getDate()).padStart(2, '0');
          }

          const timeInput = document.getElementById('rideTime');
          if (timeInput && !isNaN(rideDate.getTime())) {
            timeInput.value = String(rideDate.getHours()).padStart(2, '0') + ':' +
              String(rideDate.getMinutes()).padStart(2, '0');
          }
        }

        // Prefill Pickup and Drop-off locations
        const pickupLocation = document.getElementById('pickupLocation');
        if (pickupLocation) {
          pickupLocation.value = ride.pickup_addr || ride.actual_start_addr || '';
        }

        const dropoffLocation = document.getElementById('dropoffLocation');
        if (dropoffLocation) {
          dropoffLocation.value = ride.dest_addr || ride.actual_end_addr || '';
        }

        // Store ride ID for assignment
        currentRideId = rideId;
        if (ride.status) {
          currentRideStatus = String(ride.status).toLowerCase();
          const onTrip = ['on_trip','started','in_progress','trip_started'].includes(currentRideStatus);
          if (onTrip) routeProgressActive = true;
        }

        // Start live driver tracking if the ride already has an assigned driver
        if (ride.driver_id) {
          assignedDriverId = ride.driver_id;
          startDriverTracking();
        }

        // Calculate route if both locations are available
        if (pickupLocation && dropoffLocation && pickupLocation.value && dropoffLocation.value) {
          if (oaDirectionsService && oaDirectionsRenderer) {
            calculateRouteAndFare(pickupLocation.value, dropoffLocation.value);
          } else {
            // Map not loaded yet — store addresses for initGoogleMaps to consume
            pendingPickupAddr = pickupLocation.value;
            pendingDropoffAddr = dropoffLocation.value;
          }
        }

        // Prefill Fare Details (use stored fare from DB for app-booked rides)
        const estimatedFare = document.getElementById('estimatedFare');
        if (estimatedFare) {
          const fare = ride.fare_eur || ride.estimate_fare || null;
          if (fare !== null) {
            const numericFare = parseFloat(fare);
            estimatedFare.value = `€${numericFare.toFixed(2)}`;
            oaCurrentFare = numericFare;
            hasDbFare = true;
          }
        }

        // Prefill Distance if available
        const distance = document.getElementById('distance');
        if (distance && ride.distance_km) {
          distance.value = `${ride.distance_km} km`;
          oaCurrentDistance = parseFloat(ride.distance_km);
        }

        // Prefill Estimated Time if available
        const estimatedTime = document.getElementById('estimatedTime');
        if (estimatedTime && (ride.duration_min || ride.estimated_duration_minutes)) {
          const minutes = ride.duration_min || ride.estimated_duration_minutes;
          const hours = Math.floor(minutes / 60);
          const mins = minutes % 60;
          oaCurrentDuration = minutes;
          if (hours > 0) {
            estimatedTime.value = `${hours}h ${mins}m`;
          } else {
            estimatedTime.value = `${mins}m`;
          }
        }

        if (isNormalViewMode) {
          applyCorporateViewMode();
          applyViewModeLayout();
          populateDispatcherOverlay(ride);
          if (ride.driver_id) loadDriverOverlayInfo(ride.driver_id);
          updateRideProgressCard(currentRideStatus);
        }
      }
    } catch (error) {
      console.error('Error loading ride data:', error);
    } finally {
      if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
      const pl = document.getElementById('oaPageLoader');
      if (pl) { pl.style.opacity = '0'; pl.style.transition = 'opacity .3s'; setTimeout(() => pl.remove(), 350); }
    }
  }

  // initGoogleMaps is called by Maps API callback when script is ready (autocomplete set up there)
  // (driver list load was kicked off at the top of init so it runs in parallel)

  // Setup event listeners for route calculation
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');
  const rideDateInput = document.getElementById('rideDate');
  const rideTimeInput = document.getElementById('rideTime');

  // Recalculate fare when date/time changes
  if (rideDateInput && rideTimeInput) {
    rideDateInput.addEventListener('change', () => {
      if (pickupInput && dropoffInput && pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
    
    rideTimeInput.addEventListener('change', () => {
      if (pickupInput && dropoffInput && pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
  }

  if (pickupInput && dropoffInput) {
    pickupInput.addEventListener('blur', () => {
      if (pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
    dropoffInput.addEventListener('blur', () => {
      if (pickupInput.value && dropoffInput.value) {
        calculateRouteAndFare(pickupInput.value, dropoffInput.value);
      }
    });
  }

  // Recalculate fare when service type changes (similar to passenger app behaviour)
  const serviceTypeSelect = document.getElementById('serviceType');
  if (serviceTypeSelect) {
    serviceTypeSelect.addEventListener('change', () => {
      if (oaCurrentDistance == null || oaCurrentDuration == null) {
        return;
      }
      const rideDateInput = document.getElementById('rideDate');
      const rideTimeInput = document.getElementById('rideTime');
      let pickupTimeStr = null;
      if (rideDateInput && rideTimeInput && rideDateInput.value && rideTimeInput.value) {
        pickupTimeStr = rideDateInput.value + 'T' + rideTimeInput.value;
      } else {
        const now = new Date();
        pickupTimeStr = now.toISOString().slice(0, 16);
      }
      const rideType =
        serviceTypeSelect.value && serviceTypeSelect.value.trim()
          ? serviceTypeSelect.value.trim()
          : 'Economy';
      const newFare = oaCalculateFare(oaCurrentDistance, oaCurrentDuration, pickupTimeStr, rideType);
      oaCurrentFare = newFare;
      hasDbFare = false;
      const estimatedFareElem = document.getElementById('estimatedFare');
      if (estimatedFareElem) {
        estimatedFareElem.value = `€${newFare.toFixed(2)}`;
      }
    });
  }

  // Setup assign driver button
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    assignBtn.addEventListener('click', assignDriver);
  }

}

// Calculate route and fare using Google Maps API
function calculateRouteAndFare(pickup, dropoff) {
  if (!oaDirectionsService || !oaDirectionsRenderer) {
    console.error('Google Maps services not initialized');
    return;
  }

  const request = {
    origin: pickup,
    destination: dropoff,
    travelMode: google.maps.TravelMode.DRIVING,
  };

  oaDirectionsService.route(request, function (result, status) {
    if (status === google.maps.DirectionsStatus.OK) {
      oaDirectionsRenderer.setDirections(result);
      storeRoutePolyline(result);
      if (routeProgressActive) oaDirectionsRenderer.setOptions({ suppressPolylines: true });
      const leg = result.routes[0].legs[0];
      const distanceInKm = leg.distance.value / 1000;
      const durationInMin = Math.round(leg.duration.value / 60);

      // Capture lat/lng so corporate-ride inserts can satisfy NOT NULL columns on rides
      if (leg.start_location) {
        currentPickupLat = leg.start_location.lat();
        currentPickupLng = leg.start_location.lng();
      }
      if (leg.end_location) {
        currentDropLat = leg.end_location.lat();
        currentDropLng = leg.end_location.lng();
      }
      // Place custom pickup/dropoff markers in view mode once coordinates are known
      if (isViewMode && oaMap) placeViewModeMarkers();
      
      // Update fields
      const distanceElem = document.getElementById('distance');
      const estimatedTimeElem = document.getElementById('estimatedTime');
      const estimatedFareElem = document.getElementById('estimatedFare');
      
      if (distanceElem) {
        distanceElem.value = `${distanceInKm.toFixed(2)} km`;
      }
      
      if (estimatedTimeElem) {
        const hours = Math.floor(durationInMin / 60);
        const mins = durationInMin % 60;
        if (hours > 0) {
          estimatedTimeElem.value = `${hours}h ${mins}m`;
        } else {
          estimatedTimeElem.value = `${mins}m`;
        }
      }
      
      // Only (re)calculate fare from distance/time when we are not using a DB fare
      if (!hasDbFare) {
        const rideDateInput = document.getElementById('rideDate');
        const rideTimeInput = document.getElementById('rideTime');
        let pickupTimeStr = null;

        if (rideDateInput && rideTimeInput && rideDateInput.value && rideTimeInput.value) {
          pickupTimeStr = rideDateInput.value + 'T' + rideTimeInput.value;
        } else {
          // Use current time if not specified
          const now = new Date();
          pickupTimeStr = now.toISOString().slice(0, 16);
        }

        const rideType =
          document.getElementById('serviceType')?.value?.trim() || 'Economy';
        const fareAmount = oaCalculateFare(
          distanceInKm,
          durationInMin,
          pickupTimeStr,
          rideType
        );

        if (estimatedFareElem) {
          estimatedFareElem.value = `€${fareAmount.toFixed(2)}`;
        }
        oaCurrentFare = fareAmount;
      }
      
      // Store values for assignment
      oaCurrentDistance = distanceInKm;
      oaCurrentDuration = durationInMin;
    } else {
      console.error('DirectionsService failed:', status);
    }
  });
}

// Calculate fare based on distance, duration and time 
function oaCalculateFare(distanceInKm, durationInMin, pickupTimeStr, rideType) {
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
  const rawFare = initialFare + baseFare + (distanceInKm * ratePerKm) + ((durationInMin || 0) * ratePerMinute);
  const multipliers = {
    'Economy': 1.0,
    'Economy XL': 1.2,
    'Business': 1.0,
    'Business Plus': 1.2,
    'Limousine': 2.0,
    'Wheelchair accessible': 1.1,
    'Wheelchair Taxi': 1.1,
    'Pets Taxi': 1.15,
    'Courier / Parcel': 0.9
  };
  const multiplier = multipliers[rideType] ?? 1.0;
  return Math.round((rawFare * multiplier) * 100) / 100;
}

// Load passengers and populate dropdown (select only, no custom typing)
async function loadPassengers() {
  try {
    const response = await fetch('api/get_passengers.php?limit=500');
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) return;
    const result = await response.json();
    if (result.success && result.data) {
      passengersList = result.data;
      const select = document.getElementById('customerNameSelect');
      if (!select) return;
      select.innerHTML = '<option value="">Select passenger from list</option>';
      passengersList.forEach((p) => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.name || p.full_name || 'Unknown';
        select.appendChild(opt);
      });
    }
  } catch (err) {
    console.error('Error loading passengers', err);
  }
}

function setupPassengerSelect() {
  const select = document.getElementById('customerNameSelect');
  const phoneInput = document.getElementById('phoneNumber');
  const customerIdInput = document.getElementById('customerId');
  if (!select || !phoneInput) return;
  select.addEventListener('change', () => {
    const id = select.value;
    if (!id) {
      phoneInput.value = '';
      if (customerIdInput) customerIdInput.value = '';
      return;
    }
    const passenger = passengersList.find((p) => String(p.id) === String(id));
    if (passenger) {
      let phone = passenger.phone || passenger.phone_number || '';
      phone = String(phone).replace(/^\+353/, '').replace(/^353/, '').trim();
      phoneInput.value = phone;
      if (customerIdInput) customerIdInput.value = id;
    }
  });
}

function stripIrelandCountryCode(raw) {
  let phone = String(raw ?? '').trim();
  if (phone.startsWith('+353')) phone = phone.substring(4).trim();
  else if (phone.startsWith('353')) phone = phone.substring(3).trim();
  return phone;
}

async function loadCorporateRide(corpId) {
  try {
    const response = await fetch(`api/get_corporate_ride.php?id=${encodeURIComponent(corpId)}`);
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) throw new Error('Failed to fetch corporate ride');
    const result = await response.json();
    if (!result.success || !result.data) {
      oaShowToast(result.error || 'Corporate ride not found');
      return;
    }

    const ride = result.data.ride || {};
    const employees = Array.isArray(result.data.employees) ? result.data.employees : [];
    corporateEmployeesList = employees;
    currentCorpId = corpId;
    updateNavbarRideTitle(ride);

    // Populate the employees dropdown
    const select = document.getElementById('customerNameSelect');
    if (select) {
      select.innerHTML = '<option value="">Select corporate employee from list</option>';
      employees.forEach((emp) => {
        const opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.name || 'Unknown';
        select.appendChild(opt);
      });
      // Ensure the current ride's employee is in the list (fallback option if missing)
      if (ride.employee_id) {
        const exists = employees.some((e) => String(e.id) === String(ride.employee_id));
        if (!exists) {
          const opt = document.createElement('option');
          opt.value = ride.employee_id;
          opt.textContent = (ride.employee || '').trim() || 'Employee';
          select.appendChild(opt);
        }
        select.value = String(ride.employee_id);
      }
    }
    const customerIdInput = document.getElementById('customerId');
    if (customerIdInput) customerIdInput.value = ride.employee_id || '';

    // Phone: try to match by employee_id
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
      const matchedEmp = employees.find((e) => String(e.id) === String(ride.employee_id));
      phoneInput.value = matchedEmp ? stripIrelandCountryCode(matchedEmp.phone) : '';
    }

    const serviceType = document.getElementById('serviceType');
    if (serviceType && ride.ride_type) {
      const val = String(ride.ride_type).trim();
      const has = Array.from(serviceType.options).some((o) => o.value === val);
      if (!has) {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = val;
        serviceType.appendChild(opt);
      }
      serviceType.value = val;
    }

    const scheduledAt = ride.enroute_at ?? ride.created_at ?? null;
    if (scheduledAt) {
      const d = new Date(scheduledAt);
      if (!isNaN(d.getTime())) {
        const dateInput = document.getElementById('rideDate');
        const timeInput = document.getElementById('rideTime');
        if (dateInput) {
          const yyyy = d.getFullYear();
          const mm = String(d.getMonth() + 1).padStart(2, '0');
          const dd = String(d.getDate()).padStart(2, '0');
          dateInput.value = `${yyyy}-${mm}-${dd}`;
        }
        if (timeInput) {
          const hh = String(d.getHours()).padStart(2, '0');
          const min = String(d.getMinutes()).padStart(2, '0');
          timeInput.value = `${hh}:${min}`;
        }
      }
    }

    // Pickup / drop-off
    const pickupLocation = document.getElementById('pickupLocation');
    const dropoffLocation = document.getElementById('dropoffLocation');
    if (pickupLocation) pickupLocation.value = ride.pickup_addr || '';
    if (dropoffLocation) dropoffLocation.value = ride.dest_addr || '';

    // Recalculate route on the map
    if (pickupLocation && dropoffLocation && pickupLocation.value && dropoffLocation.value) {
      if (oaDirectionsService && oaDirectionsRenderer) {
        calculateRouteAndFare(pickupLocation.value, dropoffLocation.value);
      } else {
        pendingPickupAddr = pickupLocation.value;
        pendingDropoffAddr = dropoffLocation.value;
      }
    }

    // Fare / distance / eta
    const estimatedFare = document.getElementById('estimatedFare');
    if (estimatedFare && ride.fare_eur != null && ride.fare_eur !== '') {
      const numericFare = parseFloat(ride.fare_eur);
      if (!isNaN(numericFare)) {
        estimatedFare.value = `€${numericFare.toFixed(2)}`;
        oaCurrentFare = numericFare;
        hasDbFare = true;
      }
    }
    const distance = document.getElementById('distance');
    if (distance && ride.distance_km != null && ride.distance_km !== '') {
      const distKm = parseFloat(ride.distance_km);
      if (!isNaN(distKm)) {
        distance.value = `${distKm} km`;
        oaCurrentDistance = distKm;
      }
    }
    const estimatedTime = document.getElementById('estimatedTime');
    if (estimatedTime && ride.duration_min != null && ride.duration_min !== '') {
      const mins = parseInt(ride.duration_min, 10);
      if (!isNaN(mins)) {
        const hours = Math.floor(mins / 60);
        const rem = mins % 60;
        oaCurrentDuration = mins;
        estimatedTime.value = hours > 0 ? `${hours}h ${rem}m` : `${rem}m`;
      }
    }

    // Prefill selected driver if ride is already assigned
    const driver = result.data.driver;
    if (driver) {
      const driverInput = document.getElementById('driverSearchInput');
      const driverHidden = document.getElementById('driverSelect');
      const name = driver.full_name || driver.name || 'Driver';
      if (driverInput) driverInput.value = `${name} — ${driver.vehicle_make || ''}`;
      if (driverHidden) driverHidden.value = driver.id;
      // Start live tracking for the already-assigned driver
      assignedDriverId = driver.id;
      startDriverTracking();
    }

    if (isCorporateViewMode) {
      applyCorporateViewMode();
    }
  } catch (err) {
    console.error('Error loading corporate ride:', err);
    oaShowToast('Failed to load corporate ride data');
  }
}

function setupCorporateEmployeeSelect() {
  const select = document.getElementById('customerNameSelect');
  const phoneInput = document.getElementById('phoneNumber');
  const customerIdInput = document.getElementById('customerId');
  if (!select || !phoneInput) return;
  select.addEventListener('change', () => {
    const id = select.value;
    if (!id) {
      phoneInput.value = '';
      if (customerIdInput) customerIdInput.value = '';
      return;
    }
    const emp = corporateEmployeesList.find((e) => String(e.id) === String(id));
    if (emp) {
      phoneInput.value = stripIrelandCountryCode(emp.phone);
      if (customerIdInput) customerIdInput.value = id;
    }
  });
}

function applyCorporateViewMode() {
  // Disable every form control on the page for read-only inspection
  const root = document.querySelector('main');
  if (root) {
    root.querySelectorAll('input, select, textarea').forEach((el) => {
      el.setAttribute('disabled', 'disabled');
    });
  }
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    assignBtn.disabled = true;
    assignBtn.style.opacity = '0.55';
    assignBtn.style.cursor = 'not-allowed';
    const btnText = document.getElementById('btnText');
    if (btnText) btnText.textContent = 'Driver Assigned';
  }
  const cancelBtn = document.getElementById('cancelRideBtn');
  if (cancelBtn) cancelBtn.style.display = 'none';
}

// Load approved drivers from database
let approvedDrivers = [];

async function loadDrivers() {
  try {
    const response = await fetch('api/get_drivers.php?status=approved&limit=500');
    if (response.status === 401) { window.location.href = '/'; return; }
    if (!response.ok) throw new Error('Failed to fetch drivers');
    const result = await response.json();
    if (result.success && result.data) {
      approvedDrivers = result.data;
      renderDriverDropdown(approvedDrivers);
    }
  } catch (error) {
    console.error('Error loading drivers:', error);
  }
}

function renderDriverDropdown(drivers) {
  const list = document.getElementById('driverDropdownList');
  if (!list) return;
  list.innerHTML = '';
  if (!drivers || drivers.length === 0) {
    list.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">No approved drivers found</div>';
    return;
  }
  drivers.forEach((driver) => {
    const name = driver.full_name || driver.name || 'Driver';
    const vehicle = driver.vehicle_make || driver.vehicle_number || '';
    const phone = driver.phone || '';
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action border-0 px-3 py-2';
    item.style.cssText = 'font-size:0.8125rem; cursor:pointer; border-bottom:1px solid #F4F4F5 !important;';
    item.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#f37a20,#d96010); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px; flex-shrink:0;">${name.trim().split(/\s+/).map(p=>p[0]).slice(0,2).join('').toUpperCase()}</div>
        <div style="min-width:0;">
          <div class="fw-semibold text-truncate" style="color:#18181B; font-size:0.8125rem;">${name}</div>
          <div class="text-truncate" style="font-size:0.72rem; color:#71717A;">${vehicle}${phone ? ' &middot; ' + phone : ''}</div>
        </div>
      </div>
    `;
    item.addEventListener('mousedown', (e) => { e.preventDefault(); selectDriver(driver); });
    list.appendChild(item);
  });
}

function selectDriver(driver) {
  const input = document.getElementById('driverSearchInput');
  const hidden = document.getElementById('driverSelect');
  const name = driver.full_name || driver.name || 'Driver';
  if (input) input.value = `${name} — ${driver.vehicle_make || ''}`;
  if (hidden) hidden.value = driver.id;
  hideDriverDropdown();
}

function showDriverDropdown() {
  const list = document.getElementById('driverDropdownList');
  if (!list) return;
  const input = document.getElementById('driverSearchInput');
  // When opened with no search term, show the full approved list — or a loading
  // hint if the fetch hasn't finished yet (it auto-renders on completion).
  if (!input || !input.value.trim()) {
    if (approvedDrivers && approvedDrivers.length) {
      renderDriverDropdown(approvedDrivers);
    } else {
      list.innerHTML = '<div class="px-3 py-2 text-muted" style="font-size:0.8125rem;">Loading drivers…</div>';
    }
  }
  list.style.display = 'block';
}

function hideDriverDropdown() {
  const list = document.getElementById('driverDropdownList');
  if (list) list.style.display = 'none';
}

// Filter drivers as user types
function initOrderAssignedDriverFilter() {
  const searchInput = document.getElementById('driverSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const term = this.value.toLowerCase().trim();
      const hidden = document.getElementById('driverSelect');
      if (hidden) hidden.value = '';
      if (!term) {
        renderDriverDropdown(approvedDrivers);
      } else {
        const filtered = approvedDrivers.filter(d => {
          const name = (d.full_name || d.name || '').toLowerCase();
          const vehicle = (d.vehicle_make || d.vehicle_number || '').toLowerCase();
          const phone = (d.phone || '').toLowerCase();
          return name.includes(term) || vehicle.includes(term) || phone.includes(term);
        });
        renderDriverDropdown(filtered);
      }
      showDriverDropdown();
    });
  }
}

function oaSetButtonLoading(isLoading, customText = null) {
  const btn = document.getElementById('assignDriverBtn');
  const btnText = document.getElementById('btnText');
  const btnSpinner = document.getElementById('btnSpinner');
  
  if (isLoading) {
    btn.classList.add('btn-loading');
    btn.disabled = true;
    btnSpinner.style.display = 'inline-block';
    btnText.textContent = customText || 'Assigning Driver...';
  } else {
    btn.classList.remove('btn-loading');
    btn.disabled = false;
    btnSpinner.style.display = 'none';
    btnText.textContent = 'Assign Driver';
  }
}

// Validate required fields (alerts for now; replace with toast later)
function validateOrderAssignedForm() {
  const passengerSelect = document.getElementById('customerNameSelect');
  const phoneInput = document.getElementById('phoneNumber');
  const rideDate = document.getElementById('rideDate');
  const rideTime = document.getElementById('rideTime');
  const pickupInput = document.getElementById('pickupLocation');
  const dropoffInput = document.getElementById('dropoffLocation');

  if (!passengerSelect?.value?.trim()) {
    oaShowToast('Please select a passenger.');
    return false;
  }
  if (!phoneInput?.value?.trim()) {
    oaShowToast('Please enter a phone number.');
    return false;
  }
  if (!rideDate?.value?.trim()) {
    oaShowToast('Please select a date.');
    return false;
  }
  if (!rideTime?.value?.trim()) {
    oaShowToast('Please select a time.');
    return false;
  }
  if (!pickupInput?.value?.trim()) {
    oaShowToast('Please enter a pickup location.');
    return false;
  }
  if (!dropoffInput?.value?.trim()) {
    oaShowToast('Please enter a drop-off location.');
    return false;
  }
  return true;
}

function oaShowToast(message, isSuccess = false) {
  const toastEl = document.getElementById('globalToast');
  const toastMsg = document.getElementById('toastMessage');
  if (!toastEl || !toastMsg) return;

  toastMsg.textContent = message;
  toastEl.className = `toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0`;
  
  const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, {
    autohide: true,
    delay: 3000
  });
  bsToast.show();
}

// Function to collect form data
function getFormData() {
  return {
    customerId: document.getElementById('customerId')?.value || '',
    customerName: document.getElementById('customerNameSelect')?.value || '',
    phoneNumber: document.getElementById('phoneNumber')?.value || '',
    serviceType: document.getElementById('serviceType')?.value || '',
    rideDate: document.getElementById('rideDate')?.value || '',
    rideTime: document.getElementById('rideTime')?.value || '',
    pickupLocation: document.getElementById('pickupLocation')?.value || '',
    dropoffLocation: document.getElementById('dropoffLocation')?.value || '',
    estimatedFare: document.getElementById('estimatedFare')?.value || '',
    estimatedTime: document.getElementById('estimatedTime')?.value || '',
    distance: document.getElementById('distance')?.value || '',
    creditCard: document.getElementById('creditCard')?.checked || false,
    extraLuggage: document.getElementById('extraLuggage')?.checked || false,
    petsAllowed: document.querySelectorAll('#extraLuggage')[1]?.checked || false,
    delivery: document.querySelectorAll('#extraLuggage')[2]?.checked || false,
    driverId: document.getElementById('driverSelect')?.value || ''
  };
}

// Updated assignDriver function with loading state
async function assignDriver() {
  if (!validateOrderAssignedForm()) {
    return;
  }

  if (!currentRideId && !currentCorpId) {
    oaShowToast('No order selected. Please open this page from an order (e.g. from the orders list) to assign a driver.');
    return;
  }

  const driverSelect = document.getElementById('driverSelect');
  const selectedDriverId = driverSelect?.value?.trim();

  if (!selectedDriverId) {
    oaShowToast('Please select a driver');
    return;
  }

  if (!oaCurrentDistance || !oaCurrentDuration || !oaCurrentFare) {
    oaShowToast('Please ensure route is calculated. Make sure pickup and drop-off locations are filled.');
    return;
  }
  
   const serviceTypeEl = document.getElementById('serviceType');
  const serviceType = serviceTypeEl?.value?.trim() || null;

  // Set loading state before API call
  oaSetButtonLoading(true, 'Assigning Driver...');

  const endpoint = isCorporateMode ? 'api/assign_corporate_driver.php' : 'api/assign_driver.php';
  const pickupLocationVal = document.getElementById('pickupLocation')?.value || '';
  const dropoffLocationVal = document.getElementById('dropoffLocation')?.value || '';
  const rideDateVal = document.getElementById('rideDate')?.value || '';
  const rideTimeVal = document.getElementById('rideTime')?.value || '';
  const pickupTimeIso = (rideDateVal && rideTimeVal) ? `${rideDateVal}T${rideTimeVal}:00` : '';

  const payload = isCorporateMode
    ? {
        corp_id: currentCorpId,
        driver_id: selectedDriverId,
        distance_km: oaCurrentDistance,
        duration_min: oaCurrentDuration,
        fare_eur: oaCurrentFare,
        service_type: serviceType,
        pickup_addr: pickupLocationVal,
        dest_addr: dropoffLocationVal,
        pickup_time: pickupTimeIso,
        pickup_lat: currentPickupLat,
        pickup_lng: currentPickupLng,
        dest_lat: currentDropLat,
        dest_lng: currentDropLng,
      }
    : {
        ride_id: currentRideId,
        driver_id: selectedDriverId,
        distance_km: oaCurrentDistance,
        duration_min: oaCurrentDuration,
        fare_eur: oaCurrentFare,
        service_type: serviceType,
      };

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (response.status === 401) {
      window.location.href = '/';
      return;
    }

    const result = await response.json();

    if (result.success) {
      // Start / refresh live tracking for the newly assigned driver
      assignedDriverId = selectedDriverId;
      stopDriverTracking();
      startDriverTracking();

      const modal = new bootstrap.Modal(
        document.getElementById('driverAssignedModal')
      );
      modal.show();

      const goToPreorderBtn = document.getElementById('goToPreorderBtn');
      const newBtn = goToPreorderBtn.cloneNode(true);
      goToPreorderBtn.parentNode.replaceChild(newBtn, goToPreorderBtn);

      // corporate_rides.php was removed — all corporate ride management now
      // lives in Live Orders (Pre-Order + Meet & Greet tabs), so every
      // assignment (corporate or not) goes back there.
      newBtn.addEventListener('click', () => {
        window.location.href = 'liveorder.php';
      });

    } else {
      oaShowToast('Error assigning driver: ' + (result.error || 'Unknown error'));
    }
  } catch (error) {
    console.error('Error assigning driver:', error);
    oaShowToast('Failed to assign driver. Please try again.');
  } finally {
    // Reset button state after API call completes (success or error)
    oaSetButtonLoading(false);
  }
}

function openCancelRideModal() {
  if (!currentRideId && !currentCorpId) {
    oaShowToast('No ride selected to cancel.');
    return;
  }
  const modalEl = document.getElementById('cancelRideModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

async function confirmCancelRide() {
  if (!currentRideId && !currentCorpId) {
    oaShowToast('No ride selected to cancel.');
    return;
  }

  const confirmBtn = document.getElementById('confirmCancelRideBtn');
  const confirmText = document.getElementById('confirmCancelText');
  const confirmSpinner = document.getElementById('confirmCancelSpinner');

  if (confirmBtn) confirmBtn.disabled = true;
  if (confirmText) confirmText.textContent = 'Cancelling...';
  if (confirmSpinner) confirmSpinner.style.display = 'inline-block';

  const endpoint = isCorporateMode ? 'api/update_corporate_ride_status.php' : 'api/update_ride_status.php';
  const payload = isCorporateMode
    ? { corp_id: currentCorpId, status: 'Cancelled' }
    : { ride_id: currentRideId, status: 'cancelled' };
  // corporate_rides.php was removed — corporate ride management now lives in
  // Live Orders, so cancelling (corporate or not) redirects back there.
  const redirectTo = 'liveorder.php';

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (response.status === 401) { window.location.href = '/'; return; }
    const result = await response.json();

    if (result.success) {
      const modalEl = document.getElementById('cancelRideModal');
      if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
      oaShowToast('Ride cancelled successfully', true);
      setTimeout(() => { window.location.href = redirectTo; }, 900);
    } else {
      oaShowToast('Error cancelling ride: ' + (result.error || 'Unknown error'));
      if (confirmBtn) confirmBtn.disabled = false;
      if (confirmText) confirmText.textContent = 'Yes, Cancel Ride';
      if (confirmSpinner) confirmSpinner.style.display = 'none';
    }
  } catch (error) {
    console.error('Error cancelling ride:', error);
    oaShowToast('Failed to cancel ride. Please try again.');
    if (confirmBtn) confirmBtn.disabled = false;
    if (confirmText) confirmText.textContent = 'Yes, Cancel Ride';
    if (confirmSpinner) confirmSpinner.style.display = 'none';
  }
}

// ── View mode helpers ─────────────────────────────────────────────────────

function applyViewModeLayout() {
  const mainEl = document.querySelector('main.main-content');
  if (mainEl) mainEl.classList.add('view-mode-active');

  const overlay = document.getElementById('dispatcherOverlay');
  if (overlay) overlay.style.display = 'block';

  // Prevent page-level scrolling in tracking view
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';

  const setMapHeight = () => {
    const mEl = document.querySelector('main.main-content');
    const mainTop = mEl ? Math.round(mEl.getBoundingClientRect().top) : 0;
    // 20px top padding + 20px bottom padding + 4px safety margin
    const availH = window.innerHeight - mainTop - 44;
    const container = document.getElementById('mapContainer');
    const mapEl = document.getElementById('map');
    if (container) { container.style.height = availH + 'px'; container.style.minHeight = availH + 'px'; }
    if (mapEl)      { mapEl.style.height = availH + 'px'; mapEl.style.minHeight = availH + 'px'; }
    if (oaMap && typeof google !== 'undefined') google.maps.event.trigger(oaMap, 'resize');
  };
  setMapHeight();
  window.addEventListener('resize', setMapHeight);
}


function createPinIcon(color) {
  const svg = encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">' +
    '<path d="M14 0C6.27 0 0 6.27 0 14c0 9.63 14 22 14 22S28 23.63 28 14C28 6.27 21.73 0 14 0z" fill="' + color + '"/>' +
    '<circle cx="14" cy="14" r="6" fill="white"/>' +
    '</svg>'
  );
  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + svg,
    scaledSize: new google.maps.Size(28, 36),
    anchor: new google.maps.Point(14, 36),
  };
}

function geoDistanceMeters(lat1, lng1, lat2, lng2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateDriverToPickupRoute(driverLat, driverLng) {
  if (!oaDirectionsService || !driverRouteRenderer) return;
  if (!currentPickupLat || !currentPickupLng) return;
  oaDirectionsService.route({
    origin: { lat: driverLat, lng: driverLng },
    destination: { lat: currentPickupLat, lng: currentPickupLng },
    travelMode: google.maps.TravelMode.DRIVING,
  }, (result, status) => {
    if (status === google.maps.DirectionsStatus.OK) {
      driverRouteRenderer.setDirections(result);
      lastDriverRouteLat = driverLat;
      lastDriverRouteLng = driverLng;
    }
  });
}

// Where is the driver headed? Same logic as Live Map.
function oaTripDestinationFor(driver) {
  const s = (driver.status || '').toLowerCase();
  const onTrip    = ['on_trip', 'started', 'in_progress', 'trip_started'].includes(s);
  const prePickup = ['assigned', 'accepted', 'driver_accepted', 'arrived_at_pickup', 'driver_arrived', 'arrived'].includes(s);
  const num = (v) => (v != null && v !== '' && !isNaN(parseFloat(v))) ? parseFloat(v) : null;

  let lat = null, lng = null;
  if (onTrip) {
    lat = num(driver.dest_lat);   lng = num(driver.dest_lng);
    if (lat === null || lng === null) { lat = num(driver.pickup_lat); lng = num(driver.pickup_lng); }
  } else if (prePickup) {
    lat = num(driver.pickup_lat); lng = num(driver.pickup_lng);
    if (lat === null || lng === null) { lat = num(currentPickupLat); lng = num(currentPickupLng); }
    if (lat === null || lng === null) { lat = num(driver.dest_lat); lng = num(driver.dest_lng); }
  } else {
    return null;
  }
  if (lat === null || lng === null) return null;
  return { lat, lng };
}

function clearDriverTrack() {
  if (driverTrackPolyline) {
    driverTrackPolyline.setMap(null);
    driverTrackPolyline = null;
  }
  driverTrackMeta = null;
}

// Throttled road route from driver → pickup/destination (Live Map style polyline)
function maybeUpdateDriverTrack(fromLat, fromLng, driver) {
  const target = oaTripDestinationFor(driver);
  if (!target) { clearDriverTrack(); return; }
  if (!oaDirectionsService) return;

  const targetKey = target.lat.toFixed(5) + ',' + target.lng.toFixed(5);
  const now = Date.now();
  let needs = false;
  if (!driverTrackMeta || driverTrackMeta.targetKey !== targetKey) {
    needs = true;
  } else if (now - driverTrackMeta.ts > OA_ROUTE_REFRESH_MS) {
    needs = true;
  } else if (google.maps.geometry && google.maps.geometry.spherical) {
    const moved = google.maps.geometry.spherical.computeDistanceBetween(
      new google.maps.LatLng(driverTrackMeta.fromLat, driverTrackMeta.fromLng),
      new google.maps.LatLng(fromLat, fromLng)
    );
    if (moved > OA_ROUTE_REDRAW_METERS) needs = true;
  }
  if (!needs) return;

  driverTrackMeta = { targetKey, ts: now, fromLat, fromLng };

  oaDirectionsService.route({
    origin: { lat: fromLat, lng: fromLng },
    destination: { lat: target.lat, lng: target.lng },
    travelMode: google.maps.TravelMode.DRIVING,
  }, (result, status) => {
    if (status !== 'OK' || !result.routes || !result.routes[0]) return;
    const path = result.routes[0].overview_path;
    if (driverTrackPolyline) {
      driverTrackPolyline.setPath(path);
    } else {
      driverTrackPolyline = new google.maps.Polyline({
        path,
        map: oaMap,
        strokeColor: '#f37a20',
        strokeOpacity: 0.85,
        strokeWeight: 4,
        zIndex: 1,
      });
    }
  });
}

// Formats a Supabase timestamp for the dispatcher overlay, e.g. "11 Jul, 14:32"
function formatOverlayDateTime(value) {
  if (!value) return null;
  const d = new Date(value);
  if (isNaN(d.getTime())) return null;
  const datePart = d.toLocaleDateString('en-IE', { day: '2-digit', month: 'short' });
  const timePart = d.toLocaleTimeString('en-IE', { hour: '2-digit', minute: '2-digit' });
  return `${datePart}, ${timePart}`;
}

function populateDispatcherOverlay(ride) {
  const pickupEl  = document.getElementById('overlayPickup');
  const dropoffEl = document.getElementById('overlayDropoff');
  if (pickupEl)  pickupEl.textContent  = ride.pickup_addr || ride.actual_start_addr || '—';
  if (dropoffEl) dropoffEl.textContent = ride.dest_addr || ride.actual_end_addr || '—';

  const orderTimeEl = document.getElementById('overlayOrderTime');
  if (orderTimeEl) {
    orderTimeEl.textContent = formatOverlayDateTime(ride.scheduled_at || ride.created_at) || '—';
  }
  const enrouteTimeEl = document.getElementById('overlayEnrouteTime');
  if (enrouteTimeEl) {
    enrouteTimeEl.textContent = formatOverlayDateTime(ride.enroute_at) || 'Not yet';
  }

  if (ride.pickup_lat != null && ride.pickup_lng != null) {
    currentPickupLat = parseFloat(ride.pickup_lat);
    currentPickupLng = parseFloat(ride.pickup_lng);
  }
  if (ride.dest_lat != null && ride.dest_lng != null) {
    currentDropLat = parseFloat(ride.dest_lat);
    currentDropLng = parseFloat(ride.dest_lng);
  }
}

async function loadDriverOverlayInfo(driverId) {
  try {
    const resp = await fetch(`api/get_live_drivers.php?driver_id=${encodeURIComponent(driverId)}`, { cache: 'no-store' });
    if (!resp.ok) return;
    const result = await resp.json();
    if (!result.success || !result.data || !result.data.length) return;
    const d = result.data[0];
    const nameEl = document.getElementById('overlayDriverName');
    const vehEl  = document.getElementById('overlayVehicle');
    if (nameEl) nameEl.textContent = d.full_name || d.name || 'Driver';
    if (vehEl) {
      const parts = [d.vehicle_make, d.vehicle_number].filter(Boolean);
      vehEl.textContent = parts.length ? parts.join(' · ') : '—';
    }
  } catch (e) {
    console.warn('Could not load driver overlay info:', e);
  }
}

function updateDispatcherOverlayFromDriver(loc) {
  const nameEl = document.getElementById('overlayDriverName');
  const vehEl  = document.getElementById('overlayVehicle');
  if (nameEl && loc.full_name) nameEl.textContent = loc.full_name;
  if (vehEl) {
    const parts = [loc.vehicle_make, loc.vehicle_number].filter(Boolean);
    if (parts.length) vehEl.textContent = parts.join(' · ');
  }
  if (loc.enroute_at) {
    const enrouteTimeEl = document.getElementById('overlayEnrouteTime');
    if (enrouteTimeEl) enrouteTimeEl.textContent = formatOverlayDateTime(loc.enroute_at) || 'Not yet';
  }
}

function placeViewModeMarkers() {
  if (!oaMap || !currentPickupLat || !currentPickupLng || !currentDropLat || !currentDropLng) return;
  hideMapLoadingSkeleton();

  if (!viewModePickupMarker) {
    viewModePickupMarker = new google.maps.Marker({
      position: { lat: currentPickupLat, lng: currentPickupLng },
      map: oaMap,
      icon: createPinIcon('#22C55E'),
      title: 'Pickup',
      zIndex: 10,
    });
    const iw = new google.maps.InfoWindow({
      content: '<div style="font-size:13px;font-weight:700;color:#18181B;">Pickup</div>' +
               '<div style="font-size:12px;color:#52525B;margin-top:2px;">' +
               (document.getElementById('pickupLocation')?.value || '') + '</div>',
    });
    viewModePickupMarker.addListener('click', () => iw.open(oaMap, viewModePickupMarker));
  }

  if (!viewModeDropoffMarker) {
    viewModeDropoffMarker = new google.maps.Marker({
      position: { lat: currentDropLat, lng: currentDropLng },
      map: oaMap,
      icon: createPinIcon('#E11D48'),
      title: 'Dropoff',
      zIndex: 10,
    });
    const iw = new google.maps.InfoWindow({
      content: '<div style="font-size:13px;font-weight:700;color:#18181B;">Dropoff</div>' +
               '<div style="font-size:12px;color:#52525B;margin-top:2px;">' +
               (document.getElementById('dropoffLocation')?.value || '') + '</div>',
    });
    viewModeDropoffMarker.addListener('click', () => iw.open(oaMap, viewModeDropoffMarker));
  }
}


// ── Bearing & icon helpers ─────────────────────────────────────────────────

function oaComputeBearing(fromLat, fromLng, toLat, toLng) {
  if (fromLat === toLat && fromLng === toLng) return null;
  const φ1 = fromLat * Math.PI / 180;
  const φ2 = toLat   * Math.PI / 180;
  const Δλ = (toLng - fromLng) * Math.PI / 180;
  const y  = Math.sin(Δλ) * Math.cos(φ2);
  const x  = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
  return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

function oaBuildDriverIcon(bearingDeg, rideStatus) {
  const s   = (rideStatus || '').toLowerCase();
  const col = ['on_trip','started','in_progress','trip_started'].includes(s)          ? '#3B82F6'
            : ['arrived_at_pickup','driver_arrived','arrived'].includes(s)             ? '#22C55E'
            : ['assigned','accepted','driver_accepted'].includes(s)                    ? '#f37a20'
            : '#f37a20';
  const b   = Math.round((bearingDeg || 0) % 360);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44"><g transform="translate(22,22) rotate(${b})"><ellipse cx="0" cy="1" rx="13" ry="11" fill="rgba(0,0,0,0.18)"/><rect x="-9" y="-12" width="18" height="24" rx="5" fill="${col}" stroke="white" stroke-width="1.5"/><rect x="-7" y="-10" width="14" height="8" rx="3" fill="rgba(255,255,255,0.45)"/><rect x="-6" y="6" width="12" height="5" rx="2" fill="rgba(255,255,255,0.25)"/><rect x="-14" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="-11" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="-14" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><rect x="9" y="2" width="5" height="9" rx="2.5" fill="#1E293B"/><polygon points="0,-19 -5,-12 5,-12" fill="white" opacity="0.9"/></g></svg>`;
  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
    scaledSize: new google.maps.Size(44, 44),
    anchor: new google.maps.Point(22, 22),
  };
}

// ── Route progress ─────────────────────────────────────────────────────────

function storeRoutePolyline(directionsResult) {
  try {
    const route = directionsResult && directionsResult.routes && directionsResult.routes[0];
    if (!route) return;
    currentRoutePath = [];
    for (const leg of route.legs) {
      for (const step of leg.steps) {
        if (Array.isArray(step.path)) {
          step.path.forEach(p => currentRoutePath.push({ lat: p.lat(), lng: p.lng() }));
        } else if (step.polyline && step.polyline.points && typeof google !== 'undefined' && google.maps.geometry) {
          google.maps.geometry.encoding.decodePath(step.polyline.points)
            .forEach(p => currentRoutePath.push({ lat: p.lat(), lng: p.lng() }));
        }
      }
    }
  } catch (e) {
    console.warn('storeRoutePolyline:', e);
  }
}

function findNearestRouteIndex(driverLat, driverLng) {
  let nearestIdx = 0, minDist = Infinity;
  for (let i = 0; i < currentRoutePath.length; i++) {
    const d = geoDistanceMeters(driverLat, driverLng, currentRoutePath[i].lat, currentRoutePath[i].lng);
    if (d < minDist) { minDist = d; nearestIdx = i; }
  }
  return nearestIdx;
}

function updateRouteProgress(driverLat, driverLng) {
  if (!currentRoutePath.length || !oaMap) return;
  const idx = findNearestRouteIndex(driverLat, driverLng);
  const completedPath = currentRoutePath.slice(0, Math.max(1, idx + 1));
  const remainingPath = currentRoutePath.slice(idx);

  if (completedPolyline) { completedPolyline.setMap(null); completedPolyline = null; }
  if (remainingPolyline) { remainingPolyline.setMap(null); remainingPolyline = null; }

  if (completedPath.length >= 2) {
    completedPolyline = new google.maps.Polyline({
      path: completedPath, strokeColor: '#A1A1AA',
      strokeOpacity: 0.45, strokeWeight: 5, zIndex: 1, map: oaMap,
    });
  }
  if (remainingPath.length >= 2) {
    remainingPolyline = new google.maps.Polyline({
      path: remainingPath, strokeColor: '#3B82F6',
      strokeOpacity: 0.9, strokeWeight: 5, zIndex: 2, map: oaMap,
    });
  }
}

function clearRouteProgress() {
  if (completedPolyline) { completedPolyline.setMap(null); completedPolyline = null; }
  if (remainingPolyline) { remainingPolyline.setMap(null); remainingPolyline = null; }
  routeProgressActive = false;
}

// ── Map loading skeleton ───────────────────────────────────────────────────
function showMapLoadingSkeleton() {
  const container = document.getElementById('mapContainer');
  if (!container || document.getElementById('mapLoadingSkeleton')) return;
  const skeleton = document.createElement('div');
  skeleton.id = 'mapLoadingSkeleton';
  skeleton.style.cssText = 'position:absolute;inset:0;z-index:25;background:#F4F4F5;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  skeleton.innerHTML = `
    <div style="width:44px;height:44px;border:3px solid #E4E4E7;border-top-color:#f37a20;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
    <div style="font-size:0.84rem;font-weight:600;color:#71717A;">Loading ride details...</div>
  `;
  container.appendChild(skeleton);
}
function hideMapLoadingSkeleton() {
  document.getElementById('mapLoadingSkeleton')?.remove();
}

// ── Ride progress status card ─────────────────────────────────────────────
function updateRideProgressCard(status) {
  const card = document.getElementById('rideProgressCard');
  const icon = document.getElementById('rideProgressIcon');
  const text = document.getElementById('rideProgressText');
  if (!card || !icon || !text) return;

  const s = (status || '').toLowerCase();
  const isEnroute     = ['enroute','en_route','en-route'].includes(s);
  const isAssigned    = ['assigned','driver_accepted','accepted'].includes(s);
  const isArrived     = ['arrived_at_pickup','driver_arrived','arrived'].includes(s);
  const isOnTrip      = ['on_trip','started','in_progress','trip_started'].includes(s);
  const isCompleted   = ['completed','finished','done'].includes(s);
  const isCancelled   = ['cancelled','canceled'].includes(s);

  let msg = '', iconCls = '', bg = '', border = '', color = '';

  if (isCompleted) {
    msg = 'Passenger dropped off. Trip completed.';
    iconCls = 'bi-check-circle-fill'; bg = '#F0FDF4'; border = '#DCFCE7'; color = '#16A34A';
  } else if (isOnTrip) {
    msg = 'Passenger picked up. Driver is heading to the destination.';
    iconCls = 'bi-car-front-fill'; bg = '#EFF6FF'; border = '#DBEAFE'; color = '#2563EB';
  } else if (isArrived) {
    msg = 'Driver arrived at pickup. Waiting for passenger.';
    iconCls = 'bi-geo-alt-fill'; bg = '#FFF7ED'; border = '#FED7AA'; color = '#EA580C';
  } else if (isEnroute) {
    msg = 'Driver is enroute to pick up the passenger.';
    iconCls = 'bi-geo-alt'; bg = '#EEF2FF'; border = '#C7D2FE'; color = '#4F46E5';
  } else if (isAssigned) {
    msg = 'Driver assigned. Waiting for driver to start.';
    iconCls = 'bi-arrow-right-circle-fill'; bg = '#F5F3FF'; border = '#DDD6FE'; color = '#7C3AED';
  } else if (isCancelled) {
    msg = 'Ride has been cancelled.';
    iconCls = 'bi-x-circle-fill'; bg = '#FFF1F2'; border = '#FFE4E6'; color = '#E11D48';
  } else if (s === 'searching') {
    msg = 'Searching for a nearby driver...';
    iconCls = 'bi-broadcast'; bg = '#FEF3C7'; border = '#FDE68A'; color = '#D97706';
  } else {
    card.style.display = 'none';
    return;
  }

  card.style.display = 'block';
  card.style.background = bg;
  card.style.borderColor = border;
  icon.className = 'bi ' + iconCls;
  icon.style.color = color;
  text.style.color = color;
  text.textContent = msg;
}

// ── Ride status state machine ──────────────────────────────────────────────

function handleRideStatusChange(oldStatus, newStatus) {
  if (!newStatus) return;
  const s = newStatus.toLowerCase();
  const isEnroute   = ['enroute','en_route','en-route'].includes(s);
  const isArrived   = ['arrived_at_pickup','driver_arrived','arrived'].includes(s);
  const isOnTrip    = ['on_trip','started','in_progress','trip_started'].includes(s);
  const isCompleted = ['completed','finished','done'].includes(s);
  const isCancelled = ['cancelled','canceled'].includes(s);

  updateRideProgressCard(s);

  if (isEnroute) {
    showRideStatusNotification('Driver enroute to pickup', 'enroute');
    updateDispatcherOverlayStatus('Enroute', '#4F46E5');
  }

  if (isArrived) {
    if (driverRouteRenderer) driverRouteRenderer.setMap(null);
    clearDriverTrack();
    showRideStatusNotification('Driver arrived at pickup', 'arrived');
    updateDispatcherOverlayStatus('Arrived at Pickup', '#22C55E');
  }

  if (isOnTrip && !routeProgressActive) {
    if (driverRouteRenderer) driverRouteRenderer.setMap(null);
    clearDriverTrack();
    routeProgressActive = true;
    if (oaDirectionsRenderer) oaDirectionsRenderer.setOptions({ suppressPolylines: true });
    showRideStatusNotification('Trip in progress', 'on_trip');
    updateDispatcherOverlayStatus('On Trip', '#3B82F6');
  }

  if (isCompleted) {
    showRideStatusNotification('Trip completed', 'completed');
    stopDriverTracking();
    if (oaDirectionsRenderer) oaDirectionsRenderer.setMap(null);
    showCompletionOverlay();
    return;
  }

  if (isCancelled) {
    showRideStatusNotification('Ride cancelled', 'cancelled');
    stopDriverTracking();
  }
}

function showRideStatusNotification(message, type) {
  const cfg = {
    enroute:   { bg: '#EEF2FF', text: '#4F46E5', border: '#C7D2FE', icon: 'bi-geo-alt' },
    arrived:   { bg: '#F0FDF4', text: '#16A34A', border: '#DCFCE7', icon: 'bi-geo-alt-fill' },
    on_trip:   { bg: '#EFF6FF', text: '#2563EB', border: '#DBEAFE', icon: 'bi-car-front-fill' },
    completed: { bg: '#F0FDF4', text: '#16A34A', border: '#DCFCE7', icon: 'bi-check-circle-fill' },
    cancelled: { bg: '#FFF1F2', text: '#E11D48', border: '#FFE4E6', icon: 'bi-x-circle-fill' },
  };
  const c = cfg[type] || { bg: '#FAFAFA', text: '#18181B', border: '#EBEBEB', icon: 'bi-info-circle' };

  document.getElementById('rideStatusBanner')?.remove();
  const banner = document.createElement('div');
  banner.id = 'rideStatusBanner';
  banner.style.cssText = `position:absolute;top:14px;right:14px;z-index:15;background:${c.bg};border:1px solid ${c.border};border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.12);padding:10px 14px;display:flex;align-items:center;gap:8px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;pointer-events:none;animation:slideInRight 0.3s ease-out;`;
  banner.innerHTML = `<i class="bi ${c.icon}" style="color:${c.text};font-size:15px;"></i><span style="font-size:0.8125rem;font-weight:600;color:${c.text};">${message}</span>`;
  const container = document.getElementById('mapContainer');
  if (container) container.appendChild(banner);
  if (type !== 'completed' && type !== 'cancelled') setTimeout(() => banner?.remove(), 5000);
}

function updateDispatcherOverlayStatus(label, color) {
  const dot = document.getElementById('liveTrackingDot');
  const lbl = document.getElementById('liveTrackingLabel');
  if (dot) dot.style.background = color;
  if (lbl) { lbl.textContent = label; lbl.style.color = color; }
}

function showCompletionOverlay() {
  const container = document.getElementById('mapContainer');
  if (!container) return;
  document.getElementById('tripCompletionOverlay')?.remove();
  const overlay = document.createElement('div');
  overlay.id = 'tripCompletionOverlay';
  overlay.style.cssText = 'position:absolute;inset:0;z-index:20;background:rgba(255,255,255,0.92);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;backdrop-filter:blur(4px);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  overlay.innerHTML = `
    <div style="width:72px;height:72px;background:#F0FDF4;border-radius:50%;display:flex;align-items:center;justify-content:center;">
      <i class="bi bi-check-circle-fill" style="color:#22C55E;font-size:36px;"></i>
    </div>
    <div style="text-align:center;">
      <div style="font-size:1.25rem;font-weight:700;color:#18181B;letter-spacing:-0.02em;">Trip Completed</div>
      <div style="font-size:0.8125rem;color:#71717A;margin-top:4px;">The driver has reached the destination.</div>
    </div>
    <a href="liveorder.php" style="height:40px;background:#22C55E;color:#fff;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;padding:0 24px;display:inline-flex;align-items:center;text-decoration:none;">Back to Live Orders</a>
  `;
  container.appendChild(overlay);
}

// Hard-refresh/tab-close safety net — SPA navigation cleanup goes through
// window.SPA_PAGES['orderassigned.php'].cleanup instead, see below.
window.addEventListener('beforeunload', () => stopDriverTracking());

// Initialize event listener when document is ready
function initOrderAssignedButtons() {
  const assignBtn = document.getElementById('assignDriverBtn');
  if (assignBtn) {
    // Remove any existing listeners and add new one
    assignBtn.replaceWith(assignBtn.cloneNode(true));
    document.getElementById('assignDriverBtn').addEventListener('click', assignDriver);
  }

  const cancelBtn = document.getElementById('cancelRideBtn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', openCancelRideModal);
  }

  const confirmCancelBtn = document.getElementById('confirmCancelRideBtn');
  if (confirmCancelBtn) {
    confirmCancelBtn.addEventListener('click', confirmCancelRide);
  }
}

// Runs all three of this page's former DOMContentLoaded blocks in sequence,
// plus an explicit Google Maps init (the shell now loads the Maps script
// once, without a callback= param, so each page that needs it calls its own
// initGoogleMaps() directly — see js/spa-navigation.js).
async function initOrderAssignedPage() {
  await initOrderAssignedCore();
  initOrderAssignedDriverFilter();
  initOrderAssignedButtons();
  oaInitGoogleMaps();
}
document.addEventListener('DOMContentLoaded', initOrderAssignedPage);

window.SPA_PAGES = window.SPA_PAGES || {};
window.SPA_PAGES['orderassigned.php'] = { init: initOrderAssignedPage, cleanup: stopDriverTracking };

