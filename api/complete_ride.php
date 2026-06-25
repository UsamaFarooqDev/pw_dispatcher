<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized.', 'data' => null]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['ride_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ride_id is required.', 'data' => null]);
    exit;
}

try {
    $db = new SupabaseDB(null, true);

    $fareEur = (isset($input['fare_eur']) && $input['fare_eur'] !== null)
        ? number_format(floatval($input['fare_eur']), 2, '.', '')
        : '0.00';

    $updated = $db->updateData('rides', $input['ride_id'], [
        'status'        => 'completed',
        'is_scheduled'  => false,
        'total_charged' => $fareEur,
        'toll_amount'   => 0,
        'updated_at'    => date('Y-m-d H:i:s') . '+00',
    ]);

    echo json_encode([
        'success' => true,
        'data'    => $updated,
        'message' => 'Ride marked as completed.',
    ]);
} catch (Exception $e) {
    error_log('complete_ride.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => null]);
}
?>
