<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

Permission::requireCan('live_orders', 'edit');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['ride_id']) || trim((string)$input['ride_id']) === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ride ID is required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true); // Use service role to bypass RLS

    $existing = $db->findData('rides', ['id' => $input['ride_id']]);
    if (empty($existing)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Ride not found',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Update the ride's payment method to cash
    $updateData = [
        'payment_method' => 'cash',
        'updated_at' => date('Y-m-d H:i:s') . '+00'
    ];

    $updatedRide = $db->updateData('rides', $input['ride_id'], $updateData);

    echo json_encode([
        'success' => true,
        'message' => 'Ride payment converted to cash successfully',
        'data' => $updatedRide
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
?>
