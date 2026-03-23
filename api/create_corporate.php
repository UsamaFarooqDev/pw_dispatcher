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
$supabaseKey    = defined('SUPABASE_SERVICE_ROLE_KEY') ? SUPABASE_SERVICE_ROLE_KEY : ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? SUPABASE_ANON_KEY);
// If your config uses a different constant name, replace above — e.g. SUPABASE_SECRET_KEY

$email = strtolower(trim($body['email']));

/**
 * Generate a unique corporate CID like CORPCI1234.
 */
function generateCorporateCid($supabaseUrl, $supabaseKey, $maxAttempts = 20) {
    for ($i = 0; $i < $maxAttempts; $i++) {
        $candidate = 'CORPCI' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $checkUrl = $supabaseUrl . '/rest/v1/corporate?cid=eq.' . urlencode($candidate) . '&select=id&limit=1';

        $ch = curl_init($checkUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $supabaseKey,
                'Authorization: Bearer ' . $supabaseKey,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status === 200) {
            $rows = json_decode($response, true);
            if (empty($rows)) {
                return $candidate;
            }
        }
    }
    throw new Exception('Failed to generate unique corporate CID');
}

// ── DUPLICATE CHECK via direct REST call ──────────────
$checkUrl = $supabaseUrl . '/rest/v1/corporate?email=ilike.' . urlencode($email) . '&select=id,email&limit=1';

$ch = curl_init($checkUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 30,
]);
$checkResponse = curl_exec($ch);
$checkStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($checkStatus === 200) {
    $existing = json_decode($checkResponse, true);
    if (is_array($existing) && !empty($existing)) {
        $existingEmail = strtolower(trim($existing[0]['email'] ?? ''));
        if ($existingEmail === $email) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
            exit;
        }
    }
}

if ($checkStatus >= 400) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to verify existing account before insert.',
        'http_status' => $checkStatus,
        'debug_response' => $checkResponse
    ]);
    exit;
}

if ($checkStatus !== 200) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Unexpected response while checking duplicate email.',
        'http_status' => $checkStatus
    ]);
    exit;
}

// ── INSERT via direct REST call ───────────────────────
$payload = [
    'cid'              => generateCorporateCid($supabaseUrl, $supabaseKey),
    'company_name'     => trim($body['company_name']),
    'name'             => trim($body['company_name']),
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
    'pass'             => password_hash(trim($body['password']), PASSWORD_BCRYPT),
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
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 30,
]);
$insertResponse = curl_exec($ch);
$insertStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError      = curl_error($ch);


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
$supabaseMsg  = strtolower((string)($responseBody['message'] ?? $responseBody['error'] ?? ''));

// Duplicate/unique conflicts: report correct field instead of always "email"
if ($insertStatus === 409 || str_contains($supabaseMsg, 'duplicate')) {
    http_response_code(409);
    if (str_contains($supabaseMsg, 'email')) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'An account with this email already exists']);
        exit;
    }
    if (str_contains($supabaseMsg, 'cid')) {
        echo json_encode(['success' => false, 'error' => 'Generated corporate ID conflicted. Please try again.']);
        exit;
    }
    echo json_encode([
        'success' => false,
        'error' => 'Duplicate record conflict in corporate table.',
        'details' => $responseBody['message'] ?? $responseBody['error'] ?? null
    ]);
    exit;
}

// Column does not exist in your table
if (str_contains($supabaseMsg, 'column')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database column mismatch: ' . $supabaseMsg . '. Check your corporate table columns match the payload.',
        'debug'   => array_merge($payload, ['pass' => '[hashed-password]'])  // remove this line in production
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