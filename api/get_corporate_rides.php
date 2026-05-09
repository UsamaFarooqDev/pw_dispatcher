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
        'source' => 'ilike.corporate*',
        'order' => 'created_at.desc'
    ];

    if ($search !== '') {
        $safe = str_replace(['%', ','], ['\\%', '\\,'], $search);
        $query['or'] = '(company.ilike.*' . $safe . '*,employee.ilike.*' . $safe . '*,employee_id.ilike.*' . $safe . '*,pickup_addr.ilike.*' . $safe . '*,dest_addr.ilike.*' . $safe . '*,payment_method.ilike.*' . $safe . '*,status.ilike.*' . $safe . '*,cid.ilike.*' . $safe . '*,source.ilike.*' . $safe . '*)';
    }

    $url = $supabaseUrl . '/rest/v1/rides?' . http_build_query($query);
    $result = execSupabaseRequest($url, $supabaseKey, $offset, $offset + $limit - 1);
    $records = array_map(function ($r) {
        $status = trim((string)($r['status'] ?? ''));
        $normalized = $status === '' ? 'Pending' : ucwords(str_replace('_', ' ', strtolower($status)));
        return [
            'id' => (string)($r['id'] ?? ''),
            'company' => $r['company'] ?? '',
            'employee' => $r['employee'] ?? '',
            'employee_id' => $r['employee_id'] ?? '',
            'pickup_addr' => $r['pickup_addr'] ?? '',
            'dest_addr' => $r['dest_addr'] ?? '',
            'payment_method' => $r['payment_method'] ?? '',
            'fare_eur' => $r['fare_eur'] ?? null,
            'ride_type' => $r['ride_type'] ?? null,
            'distance_km' => $r['distance_km'] ?? null,
            'duration_min' => $r['duration_min'] ?? null,
            'enroute_at' => $r['enroute_at'] ?? null,
            'created_at' => $r['created_at'] ?? null,
            'status' => $normalized,
            'vehicle_number' => $r['vehicle_number'] ?? null,
            'cid' => $r['cid'] ?? null,
            'source' => $r['source'] ?? null,
        ];
    }, $result['records']);

    echo json_encode([
        'success' => true,
        'data' => $records,
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
