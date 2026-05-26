<?php
// Prevent any PHP notices/warnings from leaking into the JSON body
ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized.', 'data' => []]);
    exit;
}

$driverIdFilter = isset($_GET['driver_id']) ? trim($_GET['driver_id']) : '';

$supabaseUrl = SUPABASE_URL;
$supabaseKey = (defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '')
    ? SUPABASE_SERVICE_ROLE_KEY
    : SUPABASE_ANON_KEY;

function supabaseGet($url, $apiKey) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $body    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    // curl_close() is deprecated in PHP 8.5+ and handled automatically

    if ($body === false || $curlErr) throw new Exception('cURL error: ' . $curlErr);
    if ($code >= 400) throw new Exception('Supabase HTTP ' . $code . ': ' . substr($body, 0, 300));
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON parse error');
    return is_array($data) ? $data : [];
}

try {
    // Query the drivers table directly — current_lat / current_lng are the GPS source of truth
    $url = $supabaseUrl . '/rest/v1/drivers?select=*';

    if ($driverIdFilter !== '') {
        // Tracking a specific assigned driver: filter by id only (ignore online status)
        $url .= '&id=eq.' . rawurlencode($driverIdFilter);
    } else {
        // Live Map: only show drivers who are actively online
        $url .= '&is_online=eq.true';
    }

    $rows = supabaseGet($url, $supabaseKey);

    $result = [];
    foreach ($rows as $dr) {
        $lat = (isset($dr['current_lat']) && $dr['current_lat'] !== null && $dr['current_lat'] !== '')
            ? floatval($dr['current_lat']) : null;
        $lng = (isset($dr['current_lng']) && $dr['current_lng'] !== null && $dr['current_lng'] !== '')
            ? floatval($dr['current_lng']) : null;

        // Skip drivers without valid GPS coordinates
        if ($lat === null || $lng === null || is_nan($lat) || is_nan($lng)) continue;
        if (abs($lat) > 90 || abs($lng) > 180) continue;

        $driverId = (string)($dr['id'] ?? '');
        if (!$driverId) continue;

        $name = $dr['full_name'] ?? $dr['name'] ?? 'Driver';

        $result[] = [
            'id'             => $driverId,
            'driver_id'      => $driverId,
            'lat'            => $lat,
            'lng'            => $lng,
            'current_lat'    => $lat,
            'current_lng'    => $lng,
            'heading'        => floatval($dr['driver_heading'] ?? $dr['heading'] ?? 0),
            'updated_at'     => $dr['location_last_updated_at'] ?? $dr['last_active'] ?? $dr['updated_at'] ?? null,
            'full_name'      => $name,
            'name'           => $name,
            'phone'          => $dr['phone']          ?? '',
            'vehicle_number' => $dr['vehicle_number'] ?? '',
            'vehicle_make'   => $dr['vehicle_make']   ?? '',
            'vehicle_model'  => $dr['vehicle_model']  ?? '',
            'status'         => $dr['status']         ?? 'online',
            'is_online'      => true,
        ];
    }

    echo json_encode([
        'success' => true,
        'data'    => $result,
        'count'   => count($result),
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'data'    => [],
    ], JSON_PRETTY_PRINT);
}
