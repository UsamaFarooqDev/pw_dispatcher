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

try {
    $userId = $_SESSION['user']['id'];
    $accessToken = $_SESSION['access_token'];
    
    // Get user info from Supabase Auth
    $authUrl = SUPABASE_URL . '/auth/v1/user';
    
    $ch = curl_init($authUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_ANON_KEY,
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if ($error) {
        throw new Exception("Failed to fetch user data: " . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("Failed to fetch user data. HTTP code: $httpCode");
    }
    
    $userData = json_decode($response, true);
    
    if (!$userData || !isset($userData['id'])) {
        throw new Exception("Invalid user data received");
    }
    
    // Extract user info
    $email = $userData['email'] ?? '';
    $userMetadata = $userData['user_metadata'] ?? [];
    $name = $userMetadata['name'] ?? $userMetadata['full_name'] ?? '';
    
    // If no name in metadata, try to derive from email
    if (empty($name) && !empty($email)) {
        $emailParts = explode('@', $email);
        $name = ucfirst($emailParts[0]);
    }
    
    // Get profile image from session or metadata
    $profileImage = $_SESSION['profile_image'] ?? $userMetadata['avatar_url'] ?? null;
    
    // Update session variables for consistency
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    if ($profileImage) {
        $_SESSION['profile_image'] = $profileImage;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $userData['id'],
            'email' => $email,
            'name' => $name,
            'profile_image' => $profileImage
        ]
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
