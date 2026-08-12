<?php
// auth/login.php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email    = strtolower(trim($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter email and password.']);
    exit;
}

try {
    $db   = new SupabaseDB(null, true);
    $rows = $db->findData('admin_users', ['email' => $email]);

    $user = $rows[0] ?? null;

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    if (!(bool)$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact an administrator.']);
        exit;
    }

    if (!password_verify($password, $user['password_hash'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    // Check dispatcher application access
    $accessRows = $db->findData('user_application_access', [
        'user_id'     => $user['id'],
        'application' => 'dispatcher',
    ]);
    // super_admin bypasses app-access check
    if ($user['role'] !== 'super_admin' && empty($accessRows)) {
        echo json_encode(['success' => false, 'message' => 'You do not have access to the Dispatcher application.']);
        exit;
    }

    // Regenerate session for security
    session_regenerate_id(true);

    // Set session — compatible with both Gateway and Dispatcher patterns
    $_SESSION['admin_id']   = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    // Update last_login timestamp
    try {
        $db->updateData('admin_users', $user['id'], ['last_login' => date('c')]);
    } catch (Throwable) {
        // Non-fatal — continue even if timestamp update fails
    }

    // Operator role — defaults to 'admin' (full access) when not set on the
    // Supabase Auth user's user_metadata. Only 'dispatcher' is restricted.
    $_SESSION['role'] = $userMetadata['role'] ?? 'admin';

    echo json_encode(['success' => true, 'message' => 'Login successful.']);
    exit;

} catch (Throwable $e) {
    error_log('Dispatcher login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
    exit;
}
