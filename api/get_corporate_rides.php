<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => []
    ], JSON_PRETTY_PRINT);
    exit;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 15;
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $limit;

$supabaseUrl = SUPABASE_URL;
$supabaseKey = (defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '')
    ? SUPABASE_SERVICE_ROLE_KEY
    : SUPABASE_ANON_KEY;

function execSupabaseRequest($url, $apiKey, $rangeStart, $rangeEnd) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Prefer: count=exact',
            'Range: ' . $rangeStart . '-' . $rangeEnd
        ],
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $curlError = curl_error($ch);

    if ($response === false || $curlError) {
        throw new Exception('Failed to connect to Supabase: ' . $curlError);
    }

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    if ($httpCode !== 200 && $httpCode !== 206) {
        throw new Exception('Supabase API returned HTTP ' . $httpCode . '. Response: ' . substr($body, 0, 300));
    }

    $records = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to parse Supabase response: ' . json_last_error_msg());
    }

    $total = 0;
    if (preg_match('/Content-Range:\s*\d+-\d+\/(\d+|\*)/i', $headers, $matches)) {
        $total = ($matches[1] === '*') ? 0 : intval($matches[1]);
    }

    return [
        'records' => is_array($records) ? $records : [],
        'total' => $total
    ];
}

try {
    $query = [
        'select' => '*',
        // corporate_rides does not have created_at in current schema
        'order' => 'id.desc'
    ];

    if ($search !== '') {
        $safe = str_replace(['%', ','], ['\\%', '\\,'], $search);
        $query['or'] = '(company.ilike.*' . $safe . '*,employee.ilike.*' . $safe . '*,employee_id.ilike.*' . $safe . '*,pickup.ilike.*' . $safe . '*,destination.ilike.*' . $safe . '*,payment_source.ilike.*' . $safe . '*,status.ilike.*' . $safe . '*,cid.ilike.*' . $safe . '*)';
    }

    $url = $supabaseUrl . '/rest/v1/corporate_rides?' . http_build_query($query);
    $result = execSupabaseRequest($url, $supabaseKey, $offset, $offset + $limit - 1);

    echo json_encode([
        'success' => true,
        'data' => $result['records'],
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'totalPages' => $limit > 0 ? (int)ceil($result['total'] / $limit) : 1
        ]
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ], JSON_PRETTY_PRINT);
}
?>
