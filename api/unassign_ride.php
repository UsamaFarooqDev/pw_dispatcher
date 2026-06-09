<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['ride_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ride ID is required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true); // Service role to bypass RLS

    // A scheduled (future) ride that had a driver pre-assigned should NOT become
    // an active 'searching' broadcast when unassigned — it should stay scheduled
    // and simply lose its driver. Other rides reopen for searching as normal.
    $scheduledStatuses = ['scheduled', 'upcoming', 'pending', 'awaiting_assignment'];
    $existing = $db->findData('rides', ['id' => $input['ride_id']]);
    $currentStatus = !empty($existing) ? strtolower((string)($existing[0]['status'] ?? '')) : '';
    $newStatus = in_array($currentStatus, $scheduledStatuses, true) ? 'scheduled' : 'searching';

    // Unassign: detach the driver + their live GPS so the previous driver is
    // fully removed; keep the ride in the appropriate queue per its status.
    $updateData = [
        'status'     => $newStatus,
        'driver_id'  => null,
        'driver_lat' => null,
        'driver_lng' => null,
        'updated_at' => date('Y-m-d H:i:s') . '+00'
    ];

    $updatedRide = $db->updateData('rides', $input['ride_id'], $updateData);

    echo json_encode([
        'success' => true,
        'message' => 'Ride unassigned successfully',
        'data' => $updatedRide
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    error_log('unassign_ride error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
?>
