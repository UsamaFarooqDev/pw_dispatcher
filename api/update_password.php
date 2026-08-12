<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password']     ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current password and new password are required.']);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long.']);
    exit;
}

try {
    $db   = new SupabaseDB(null, true);
    $rows = $db->findData('admin_users', ['id' => $_SESSION['admin_id']]);
    $user = $rows[0] ?? null;

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not found. Please log in again.']);
        exit;
    }

    if (!password_verify($currentPassword, $user['password_hash'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    $db->updateData('admin_users', $user['id'], [
        'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
    ]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);

} catch (Throwable $e) {
    error_log('update_password error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}
