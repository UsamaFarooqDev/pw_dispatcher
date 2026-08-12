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

    $note = isset($input['internal_note']) ? trim((string)$input['internal_note']) : '';

    $updateData = [
        'internal_note' => $note !== '' ? $note : null,
        'updated_at' => date('Y-m-d H:i:s') . '+00'
    ];

    $updatedRide = $db->updateData('rides', $input['ride_id'], $updateData);

    echo json_encode([
        'success' => true,
        'message' => 'Internal note saved',
        'data' => $updatedRide
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    error_log('update_internal_note error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
?>
