<?php
// auth/login.php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter email and password.']);
    exit;
}

// Supabase token endpoint (email + password login)
$authUrl = SUPABASE_URL . '/auth/v1/token?grant_type=password';
$payload = json_encode(['email' => $email, 'password' => $password]);

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

// For local dev only: you can set CURLOPT_SSL_VERIFYPEER to true in production.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
 

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curlErr]);
    exit;
}

$data = json_decode($response, true);

// If Supabase returns success, it includes an access_token and user object
if ($httpCode === 200 && isset($data['access_token']) && isset($data['user']['email'])) {
    $returnedEmail = $data['user']['email'];

    // Extra safety: ensure returned email exactly matches submitted email
    if (strtolower($returnedEmail) !== strtolower($email)) {
        echo json_encode(['success' => false, 'message' => 'Authentication failed (email mismatch).']);
        exit;
    }

    // Regenerate session id for security
    session_regenerate_id(true);

    // Save minimal session info
    $_SESSION['access_token'] = $data['access_token'];
    $_SESSION['refresh_token'] = $data['refresh_token'] ?? null;
    $_SESSION['user'] = [
        'id' => $data['user']['id'] ?? null,
        'email' => $returnedEmail,
        'aud' => $data['user']['aud'] ?? null
    ];

    echo json_encode(['success' => true, 'message' => 'Login successful.']);
    exit;
}

// Otherwise treat as invalid credentials
// Supabase often returns HTTP 400 with an error message on wrong credentials
$errMsg = 'Invalid email or password.';
if (isset($data['error_description'])) $errMsg = $data['error_description'];
elseif (isset($data['error'])) $errMsg = is_string($data['error']) ? $data['error'] : json_encode($data['error']);

echo json_encode(['success' => false, 'message' => $errMsg]);
exit;
