<?php
// Live "Est. Fare" preview for order.php — calls the exact same
// resolveDispatcherFare() used by create_order.php (see
// lib/fare_calculator.php) so what the dispatcher sees while building an
// order always matches what actually gets billed on Confirm. Previously
// this number was computed client-side (js/order.js), reading the
// dispatcher's own browser clock for day/night and ride_types.multiplier
// instead of pricing_config.type_multiplier — both of which could disagree
// with the passenger app's real fare for the same trip.
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';
require_once '../lib/fare_calculator.php';

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.', 'data' => null]);
    exit;
}

$serviceType = isset($_GET['service_type']) ? trim((string) $_GET['service_type']) : 'Economy';
$distanceKm = isset($_GET['distance_km']) ? floatval($_GET['distance_km']) : 0;
$durationMin = isset($_GET['duration_min']) ? floatval($_GET['duration_min']) : 0;

if ($serviceType === '') {
    $serviceType = 'Economy';
}

try {
    $db = new SupabaseDB(null, true);
    $fareEur = ($distanceKm > 0 || $durationMin > 0)
        ? resolveDispatcherFare($db, $serviceType, $distanceKm, $durationMin)
        : 0;

    echo json_encode([
        'success' => true,
        'data' => ['fare_eur' => $fareEur],
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => null]);
}
