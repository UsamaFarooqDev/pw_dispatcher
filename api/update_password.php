<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Current password and new password are required.'
    ]);
    exit;
}

// Validate new password length (Supabase minimum is 6 characters)
if (strlen($newPassword) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 6 characters long.'
    ]);
    exit;
}

try {
    $accessToken = $_SESSION['access_token'];
    $userId = $_SESSION['user']['id'];
    
    // First, verify current password by attempting to re-authenticate
    $email = $_SESSION['user']['email'];
    
    // Verify current password
    $authUrl = SUPABASE_URL . '/auth/v1/token?grant_type=password';
    $payload = json_encode(['email' => $email, 'password' => $currentPassword]);
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . SUPABASE_ANON_KEY
    ];
    
    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // If current password is wrong, return error
    if ($httpCode !== 200) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }
    
    // Update password using Supabase Auth API
    $updateUrl = SUPABASE_URL . '/auth/v1/user';
    $updatePayload = json_encode(['password' => $newPassword]);
    
    $updateHeaders = [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_ANON_KEY,
        'Authorization: Bearer ' . $accessToken
    ];
    
    $ch = curl_init($updateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $updatePayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $updateHeaders);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $updateResponse = curl_exec($ch);
    $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if ($error) {
        throw new Exception("Failed to update password: " . $error);
    }
    
    if ($updateHttpCode !== 200) {
        $errorData = json_decode($updateResponse, true);
        $errorMsg = $errorData['msg'] ?? $errorData['error_description'] ?? 'Failed to update password.';
        throw new Exception($errorMsg);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
