<?php
ini_set('display_errors', '0');
error_reporting(0);
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized.', 'data' => []]);
    exit;
}

$supabaseUrl = SUPABASE_URL;
$supabaseKey = (defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '')
    ? SUPABASE_SERVICE_ROLE_KEY
    : SUPABASE_ANON_KEY;

function pgGet($url, $key) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err     = curl_error($ch);
    if ($body === false || $err) throw new Exception('cURL: ' . $err);
    if ($code >= 400) throw new Exception('Supabase HTTP ' . $code . ': ' . substr($body, 0, 200));
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON parse error');
    return is_array($data) ? $data : [];
}

try {
    $result      = [];
    $seenDrivers = [];

    // ── 1. Drivers with active rides ─────────────────────────────────────────
    // No GPS filter: include all active rides so we can show drivers even
    // before the driver app has written its first location update to the ride.
    $activeRides = pgGet(
        $supabaseUrl . '/rest/v1/rides'
        . '?select=id,driver_id,driver_lat,driver_lng,driver_heading,status,pickup_addr,dest_addr,pickup_lat,pickup_lng,dest_lat,dest_lng,updated_at'
        . '&status=in.(assigned,accepted,driver_accepted,arrived_at_pickup,driver_arrived,arrived,on_trip,started,in_progress,trip_started)'
        . '&driver_id=not.is.null',
        $supabaseKey
    );

    $rideByDriver = [];
    foreach ($activeRides as $r) {
        if (!empty($r['driver_id'])) $rideByDriver[$r['driver_id']] = $r;
    }

    if (!empty($rideByDriver)) {
        $ids = implode(',', array_keys($rideByDriver));
        // Fetch profiles + current_lat/lng so we can fall back to the driver
        // record when the ride table doesn't yet have a GPS fix.
        $profiles = pgGet(
            $supabaseUrl . '/rest/v1/drivers'
            . '?select=*'
            . '&id=in.(' . $ids . ')',
            $supabaseKey
        );
        $profileMap = [];
        foreach ($profiles as $p) $profileMap[$p['id']] = $p;

        foreach ($rideByDriver as $driverId => $ride) {
            $pr = $profileMap[$driverId] ?? [];

            // GPS priority: rides.driver_lat/lng (live during trip) first,
            // fall back to drivers.current_lat/lng if ride GPS not yet available.
            $rideLat = ($ride['driver_lat'] !== null && $ride['driver_lat'] !== '') ? floatval($ride['driver_lat']) : null;
            $rideLng = ($ride['driver_lng'] !== null && $ride['driver_lng'] !== '') ? floatval($ride['driver_lng']) : null;
            $drvLat  = (!empty($pr['current_lat']))  ? floatval($pr['current_lat'])  : null;
            $drvLng  = (!empty($pr['current_lng']))  ? floatval($pr['current_lng'])  : null;

            $lat = $rideLat ?? $drvLat;
            $lng = $rideLng ?? $drvLng;

            if ($lat === null || $lng === null) continue;
            if (abs($lat) > 90 || abs($lng) > 180) continue;

            // Driver-reported compass heading from the trip (0–359). The driver app
            // writes this to rides.driver_heading; it is the most accurate facing.
            $heading = (isset($ride['driver_heading']) && $ride['driver_heading'] !== null && $ride['driver_heading'] !== '')
                ? floatval($ride['driver_heading']) : 0;

            $name = $pr['full_name'] ?? 'Driver';
            $prMeta = isset($pr['meta']) ? (is_string($pr['meta']) ? json_decode($pr['meta'], true) : $pr['meta']) : [];
            if (!is_array($prMeta)) $prMeta = [];
            $result[] = [
                'id'             => $driverId,
                'driver_id'      => $driverId,
                'lat'            => $lat,
                'lng'            => $lng,
                'heading'        => $heading,
                'updated_at'     => $ride['updated_at'] ?? null,
                'full_name'      => $name,
                'name'           => $name,
                'email'           => $pr['email']           ?? '',
                'phone'           => $pr['phone']           ?? '',
                'current_address' => $pr['current_address'] ?? '',
                'vehicle_number'  => $pr['vehicle_number']  ?? '',
                'vehicle_make'    => $pr['vehicle_make']    ?? '',
                'vehicle_model'   => $pr['vehicle_model']   ?? '',
                'petsAllowed'              => !empty($prMeta['petsAllowed']),
                'acceptCardRides'          => !empty($prMeta['acceptCardRides']),
                'personWithDisabilities'   => !empty($prMeta['personWithDisabilities']),
                'acceptDeliveryRides'      => !empty($prMeta['acceptDeliveryRides']),
                'status'          => $ride['status']        ?? 'on_trip',
                'ride_id'         => $ride['id'],
                'pickup_addr'     => $ride['pickup_addr']   ?? '',
                'dest_addr'       => $ride['dest_addr']     ?? '',
                'pickup_lat'     => isset($ride['pickup_lat']) ? floatval($ride['pickup_lat']) : null,
                'pickup_lng'     => isset($ride['pickup_lng']) ? floatval($ride['pickup_lng']) : null,
                'dest_lat'       => isset($ride['dest_lat'])   ? floatval($ride['dest_lat'])   : null,
                'dest_lng'       => isset($ride['dest_lng'])   ? floatval($ride['dest_lng'])   : null,
                'is_online'      => true,
            ];
            $seenDrivers[$driverId] = true;
        }
    }

    // ── 2. Idle/online drivers not already captured above ─────────────────────
    $idleDrivers = pgGet(
        $supabaseUrl . '/rest/v1/drivers?select=*&is_online=eq.true',
        $supabaseKey
    );

    foreach ($idleDrivers as $dr) {
        $driverId = (string)($dr['id'] ?? '');
        if (!$driverId || isset($seenDrivers[$driverId])) continue;

        $lat = isset($dr['current_lat']) && $dr['current_lat'] !== null ? floatval($dr['current_lat']) : null;
        $lng = isset($dr['current_lng']) && $dr['current_lng'] !== null ? floatval($dr['current_lng']) : null;
        if ($lat === null || $lng === null || is_nan($lat) || is_nan($lng)) continue;
        if (abs($lat) > 90 || abs($lng) > 180) continue;

        // drivers has no heading column, so heading is derived client-side from
        // successive GPS fixes. Surface the GPS freshness so the dispatcher can
        // tell a live position from a stale last-known one.
        $name = $dr['full_name'] ?? $dr['name'] ?? 'Driver';
        $drMeta = isset($dr['meta']) ? (is_string($dr['meta']) ? json_decode($dr['meta'], true) : $dr['meta']) : [];
        if (!is_array($drMeta)) $drMeta = [];
        $result[] = [
            'id'             => $driverId,
            'driver_id'      => $driverId,
            'lat'            => $lat,
            'lng'            => $lng,
            'heading'        => 0,
            'updated_at'     => $dr['location_last_updated_at'] ?? $dr['last_active'] ?? $dr['updated_at'] ?? null,
            'full_name'       => $name,
            'name'            => $name,
            'email'           => $dr['email']           ?? '',
            'phone'           => $dr['phone']           ?? '',
            'current_address' => $dr['current_address'] ?? '',
            'vehicle_number'  => $dr['vehicle_number']  ?? '',
            'vehicle_make'    => $dr['vehicle_make']    ?? '',
            'vehicle_model'   => $dr['vehicle_model']   ?? '',
            'petsAllowed'              => !empty($drMeta['petsAllowed']),
            'acceptCardRides'          => !empty($drMeta['acceptCardRides']),
            'personWithDisabilities'   => !empty($drMeta['personWithDisabilities']),
            'acceptDeliveryRides'      => !empty($drMeta['acceptDeliveryRides']),
            'status'          => 'available',
            'is_online'       => true,
        ];
    }

    echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => []]);
}
