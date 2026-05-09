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

// UI sends title-case statuses; rides table stores lowercase status values.
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
    $rows = $db->findData('rides', ['id' => $input['corp_id']]);
    if (empty($rows)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Corporate ride not found',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $source = strtolower(trim((string)($rows[0]['source'] ?? '')));
    if ($source !== 'corporate' && $source !== 'corporate meet_and_greet') {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Corporate ride not found',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }
    $updated = $db->updateData('rides', $input['corp_id'], [
        'status' => strtolower($normalized),
    ]);
    if (is_array($updated)) {
        $status = trim((string)($updated['status'] ?? ''));
        $updated['status'] = $status === '' ? 'Pending' : ucwords(str_replace('_', ' ', strtolower($status)));
    }

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
