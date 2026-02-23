<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Helper to refresh access token
function refreshAccessToken($refreshToken) {
    $url = SUPABASE_URL . '/auth/v1/token?grant_type=refresh_token';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_ANON_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode(['refresh_token' => $refreshToken]),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Token refresh failed. HTTP: $httpCode");
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['access_token'])) {
        throw new Exception("Invalid refresh response");
    }

    return $data;
}

// Ensure user is logged in
if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.', 'data' => null], JSON_PRETTY_PRINT);
    exit;
}

$accessToken = $_SESSION['access_token'];
$refreshToken = $_SESSION['refresh_token'] ?? null;

try {
    // Try fetching user with current token
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
    curl_close($ch);

    // If 401, try refreshing the token
    if ($httpCode === 401 && $refreshToken) {
        $tokens = refreshAccessToken($refreshToken);
        
        // Update session with new tokens
        $_SESSION['access_token'] = $tokens['access_token'];
        if (!empty($tokens['refresh_token'])) {
            $_SESSION['refresh_token'] = $tokens['refresh_token'];
        }
        
        // Retry the user fetch with new token
        $ch = curl_init($authUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . $tokens['access_token'],
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    if ($httpCode !== 200) {
        throw new Exception("Failed to fetch user data. HTTP: $httpCode");
    }

    $userData = json_decode($response, true);
    if (!$userData || !isset($userData['id'])) {
        throw new Exception("Invalid user data");
    }

    $email = $userData['email'] ?? '';
    $userMetadata = $userData['user_metadata'] ?? [];
    $name = $userMetadata['name'] ?? $userMetadata['full_name'] ?? '';
    if (empty($name) && !empty($email)) {
        $emailParts = explode('@', $email);
        $name = ucfirst($emailParts[0]);
    }
    $profileImage = $_SESSION['profile_image'] ?? $userMetadata['avatar_url'] ?? null;

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
    // If refresh also fails, clear session and force re-login
    if (strpos($e->getMessage(), 'Token refresh failed') !== false) {
        session_destroy();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Session expired. Please log in again.', 'data' => null], JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => null], JSON_PRETTY_PRINT);
    }
}
?>