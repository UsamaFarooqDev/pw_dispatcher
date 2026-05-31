<?php
// Returns driver_lat / driver_lng from the rides table for an active assigned ride.
// This is the correct GPS source during an active ride — the driver app writes here,
// NOT to drivers.current_lat/lng (which is only updated when the driver is idle).
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

try {
    $url = $supabaseUrl . '/rest/v1/rides?select=driver_lat,driver_lng,status&id=eq.' . rawurlencode($rideId);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
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

    $rows = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON parse error');
    if (!is_array($rows) || count($rows) === 0) {
        echo json_encode(['success' => false, 'error' => 'Ride not found.', 'data' => null]);
        exit;
    }

    $row = $rows[0];
    $lat = (isset($row['driver_lat']) && $row['driver_lat'] !== null && $row['driver_lat'] !== '')
        ? floatval($row['driver_lat']) : null;
    $lng = (isset($row['driver_lng']) && $row['driver_lng'] !== null && $row['driver_lng'] !== '')
        ? floatval($row['driver_lng']) : null;

    echo json_encode([
        'success' => true,
        'data'    => [
            'lat'    => $lat,
            'lng'    => $lng,
            'status' => $row['status'] ?? null,
        ],
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => null]);
}
