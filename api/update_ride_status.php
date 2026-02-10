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

if (!$input || !isset($input['ride_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ride ID and Status are required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

// Validate status - only allow: searching, assigned, upcoming
$allowedStatuses = ['searching', 'assigned', 'upcoming'];
$status = strtolower(trim($input['status']));

if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid status. Allowed values: ' . implode(', ', $allowedStatuses),
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true); // Use service role to bypass RLS
    
    // Prepare update data
    $updateData = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s') . '+00'
    ];
    
    // Update the ride
    $updatedRide = $db->updateData('rides', $input['ride_id'], $updateData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ride status updated successfully',
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