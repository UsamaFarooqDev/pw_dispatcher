<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../auth/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

// ── REQUIRED FIELDS ───────────────────────────────────
$required = [
    'company_name', 'tax_number', 'office_address',
    'appointed_person', 'designation', 'email',
    'phone', 'billing_iban', 'payment_cycle',
    'invoice_email', 'password'
];

foreach ($required as $field) {
    if (empty(trim($body[$field] ?? ''))) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Missing required field: ' . $field]);
        exit;
    }
}

if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

if (!filter_var($body['invoice_email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid invoice email']);
    exit;
}

if (strlen($body['password']) < 8) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
    exit;
}

// ── PULL CREDENTIALS FROM YOUR CONFIG ────────────────
// Adjust these variable names to match what config.php exposes
$supabaseUrl    = defined('SUPABASE_URL')          ? SUPABASE_URL          : $_ENV['SUPABASE_URL'];
$supabaseKey    = defined('SUPABASE_SERVICE_KEY')  ? SUPABASE_SERVICE_KEY  : $_ENV['SUPABASE_SERVICE_KEY'];
// If your config uses a different constant name, replace above — e.g. SUPABASE_SECRET_KEY

$email = strtolower(trim($body['email']));

// ── DUPLICATE CHECK via direct REST call ──────────────
$checkUrl = $supabaseUrl . '/rest/v1/corporate?email=eq.' . urlencode($email) . '&select=id&limit=1';

$ch = curl_init($checkUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
    ],
]);
$checkResponse = curl_exec($ch);
$checkStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($checkStatus === 200) {
    $existing = json_decode($checkResponse, true);
    if (!empty($existing)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
        exit;
    }
}

// ── INSERT via direct REST call ───────────────────────
$payload = [
    'company_name'     => trim($body['company_name']),
    'tax_number'       => trim($body['tax_number']),
    'office_address'   => trim($body['office_address']),
    'appointed_person' => trim($body['appointed_person']),
    'designation'      => trim($body['designation']),
    'email'            => $email,
    'phone'            => trim($body['phone']),
    'billing_iban'             => strtoupper(str_replace(' ', '', trim($body['billing_iban']))),
    'payment_cycle'    => trim($body['payment_cycle']),
    'invoice_email'    => strtolower(trim($body['invoice_email'])),
    'special_notes_company'    => trim($body['special_notes_company']   ?? ''),
    'special_notes_powercabs'  => trim($body['special_notes_powercabs'] ?? ''),
    'password'         => password_hash(trim($body['password']), PASSWORD_BCRYPT),
    'created_at'       => date('c'),
];

$insertUrl = $supabaseUrl . '/rest/v1/corporate';

$ch = curl_init($insertUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
        'Prefer: return=representation',
    ],
]);
$insertResponse = curl_exec($ch);
$insertStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError      = curl_error($ch);
curl_close($ch);

// 201 = created successfully
if ($insertStatus === 201) {
    echo json_encode([
        'success' => true,
        'message' => 'Corporate account created successfully',
        'data'    => ['company_name' => $payload['company_name'], 'email' => $payload['email']]
    ]);
    exit;
}

// ── Handle Supabase-specific errors ───────────────────
$responseBody = json_decode($insertResponse, true);
$supabaseMsg  = $responseBody['message'] ?? $responseBody['error'] ?? null;

// Duplicate key from DB level (e.g. unique constraint on email)
if ($insertStatus === 409 || str_contains($supabaseMsg ?? '', 'duplicate')) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
    exit;
}

// Column does not exist in your table
if (str_contains($supabaseMsg ?? '', 'column')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database column mismatch: ' . $supabaseMsg . '. Check your corporate table columns match the payload.',
        'debug'   => $payload  // remove this line in production
    ]);
    exit;
}

http_response_code(500);
echo json_encode([
    'success'       => false,
    'error'         => $supabaseMsg ?? 'Insert failed — unknown error',
    'http_status'   => $insertStatus,
    'curl_error'    => $curlError ?: null,
    'debug_response'=> $insertResponse  // remove in production
]);
?>