<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

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
    $db = new SupabaseDB(null, true);

    // Fetch every active ride type, ordered by the column operators expose.
    // fetchData sorts via the 'order' param (col.asc/col.desc).
    $rows = $db->fetchData('ride_types', [
        'order' => 'sort_order.asc',
        'limit' => 200,
    ]);

    // Only expose the fields the dispatcher UI needs + skip inactive types.
    $types = [];
    foreach (is_array($rows) ? $rows : [] as $r) {
        if (isset($r['is_active']) && $r['is_active'] === false) continue;
        $types[] = [
            'id'          => $r['id']          ?? null,
            'name'        => $r['name']        ?? '',
            'description' => $r['description'] ?? null,
            'image_url'   => $r['image_url']   ?? null,
            'icon_emoji'  => $r['icon_emoji']  ?? null,
            'seats'       => isset($r['seats'])      ? intval($r['seats'])        : null,
            'multiplier'  => isset($r['multiplier']) ? floatval($r['multiplier']) : 1.0,
            'sort_order'  => isset($r['sort_order']) ? intval($r['sort_order'])   : 0,
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $types,
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
