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

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['corp_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Corporate ride ID and status are required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

// corporate_rides uses title-case status values (Pending, Assigned, Completed, Cancelled)
$allowed = ['Pending', 'Assigned', 'Completed', 'Cancelled'];
$normalized = ucfirst(strtolower(trim((string)$input['status'])));
if (!in_array($normalized, $allowed, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid status. Allowed values: ' . implode(', ', $allowed),
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true);
    $updated = $db->updateData('corporate_rides', $input['corp_id'], [
        'status' => $normalized,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Corporate ride status updated',
        'data' => $updated,
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
