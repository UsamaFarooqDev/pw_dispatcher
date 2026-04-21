<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

$corpId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$corpId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Corporate ride ID is required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true);

    $rides = $db->findData('corporate_rides', ['id' => $corpId]);
    if (empty($rides)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Corporate ride not found',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $ride = $rides[0];

    $employees = [];
    if (!empty($ride['cid'])) {
        $employees = $db->findData('corporate_employees', ['cid' => $ride['cid']]);
    }

    // corporate_rides links to a driver via vehicle_number only
    $driver = null;
    if (!empty($ride['vehicle_number'])) {
        $found = $db->findData('drivers', ['vehicle_number' => $ride['vehicle_number']]);
        if (!empty($found)) $driver = $found[0];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'ride' => $ride,
            'employees' => $employees,
            'driver' => $driver,
        ],
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
