<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.', 'data' => null]);
    exit;
}

try {
    $db   = new SupabaseDB(null, true);
    $rows = $db->findData('admin_users', ['id' => $_SESSION['admin_id']]);
    $user = $rows[0] ?? null;

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not found. Please log in again.', 'data' => null]);
        exit;
    }

    // Refresh session with latest values from DB
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['admin_role'] = $user['role'];

    $profileImage = $_SESSION['profile_image'] ?? null;

    echo json_encode([
        'success' => true,
        'data' => [
            'id'            => $user['id'],
            'email'         => $user['email'],
            'name'          => $user['name'],
            'role'          => $user['role'],
            'profile_image' => $profileImage,
        ],
    ]);

} catch (Throwable $e) {
    error_log('get_profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error.', 'data' => null]);
}
