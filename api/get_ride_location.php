<?php
// Returns live driver position for an assigned ride — same GPS priority as the Live Map:
// rides.driver_lat/lng first (written by the driver app during a trip), then
// drivers.current_lat/lng as a fallback before the first ride GPS fix arrives.
ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized.', 'data' => null]);
    exit;
}

$rideId = isset($_GET['ride_id']) ? trim($_GET['ride_id']) : '';
if (!$rideId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ride_id required.', 'data' => null]);
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
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);

    if ($body === false || $curlErr) throw new Exception('cURL error: ' . $curlErr);
    if ($code >= 400) throw new Exception('Supabase HTTP ' . $code . ': ' . substr($body, 0, 200));
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON parse error');
    return is_array($data) ? $data : [];
}

try {
    $rows = pgGet(
        $supabaseUrl . '/rest/v1/rides'
        . '?select=id,driver_id,driver_lat,driver_lng,driver_heading,status,pickup_addr,dest_addr,pickup_lat,pickup_lng,dest_lat,dest_lng,updated_at'
        . '&id=eq.' . rawurlencode($rideId),
        $supabaseKey
    );

    if (empty($rows)) {
        echo json_encode(['success' => false, 'error' => 'Ride not found.', 'data' => null]);
        exit;
    }

    $ride = $rows[0];
    $driverId = $ride['driver_id'] ?? null;
    $profile = [];

    if ($driverId) {
        $profiles = pgGet(
            $supabaseUrl . '/rest/v1/drivers'
            . '?select=id,full_name,phone,vehicle_number,vehicle_make,vehicle_model,current_lat,current_lng'
            . '&id=eq.' . rawurlencode($driverId),
            $supabaseKey
        );
        $profile = $profiles[0] ?? [];
    }

    $rideLat = (isset($ride['driver_lat']) && $ride['driver_lat'] !== null && $ride['driver_lat'] !== '')
        ? floatval($ride['driver_lat']) : null;
    $rideLng = (isset($ride['driver_lng']) && $ride['driver_lng'] !== null && $ride['driver_lng'] !== '')
        ? floatval($ride['driver_lng']) : null;
    $drvLat  = (!empty($profile['current_lat'])) ? floatval($profile['current_lat']) : null;
    $drvLng  = (!empty($profile['current_lng'])) ? floatval($profile['current_lng']) : null;

    $lat = $rideLat ?? $drvLat;
    $lng = $rideLng ?? $drvLng;

    if ($lat !== null && (abs($lat) > 90 || is_nan($lat))) $lat = null;
    if ($lng !== null && (abs($lng) > 180 || is_nan($lng))) $lng = null;

    $heading = (isset($ride['driver_heading']) && $ride['driver_heading'] !== null && $ride['driver_heading'] !== '')
        ? floatval($ride['driver_heading']) : 0;

    $name = $profile['full_name'] ?? 'Driver';

    echo json_encode([
        'success' => true,
        'data'    => [
            'lat'            => $lat,
            'lng'            => $lng,
            'heading'        => $heading,
            'status'         => $ride['status'] ?? null,
            'updated_at'     => $ride['updated_at'] ?? null,
            'driver_id'      => $driverId,
            'full_name'      => $name,
            'name'           => $name,
            'phone'          => $profile['phone']          ?? '',
            'vehicle_number' => $profile['vehicle_number'] ?? '',
            'vehicle_make'   => $profile['vehicle_make']   ?? '',
            'vehicle_model'  => $profile['vehicle_model']  ?? '',
            'pickup_addr'    => $ride['pickup_addr']       ?? '',
            'dest_addr'      => $ride['dest_addr']         ?? '',
            'pickup_lat'     => isset($ride['pickup_lat']) ? floatval($ride['pickup_lat']) : null,
            'pickup_lng'     => isset($ride['pickup_lng']) ? floatval($ride['pickup_lng']) : null,
            'dest_lat'       => isset($ride['dest_lat'])   ? floatval($ride['dest_lat'])   : null,
            'dest_lng'       => isset($ride['dest_lng'])   ? floatval($ride['dest_lng'])   : null,
        ],
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => null]);
}
