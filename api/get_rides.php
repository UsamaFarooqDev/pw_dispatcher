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
        'data' => []
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true);

    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(5000, intval($_GET['limit']))) : 10;

    // Lite mode: for background status pollers (js/rides-poller.js, which
    // feeds searching-ride-beep.js + preorder-voice-reminder.js on every
    // page) that only need status/driver_id, not the full enriched row,
    // passenger/driver joins, or total count.
    $lite = isset($_GET['mode']) && $_GET['mode'] === 'lite';

    if ($lite) {
        $rides = $db->fetchData('rides', [
            'select' => 'id,status,driver_id,updated_at',
            'order' => 'created_at.desc',
            'page' => $page,
            'limit' => $limit
        ]);

        echo json_encode([
            'success' => true,
            'data' => $rides,
            'count' => count($rides),
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Fetch paginated rides
    $rides = $db->fetchData('rides', [
        'order' => 'created_at.desc',
        'page' => $page,
        'limit' => $limit
    ]);

    // Get total count
    $totalCount = $db->getCount('rides');

    // Only fetch the passengers/drivers actually referenced by this page of
    // rides (instead of dumping the whole table on every call) — the lookup
    // maps below only ever need these ids.
    $passengerIds = array_values(array_unique(array_filter(array_map(
        function ($r) { return $r['user_id'] ?? null; },
        $rides
    ))));
    $driverIds = array_values(array_unique(array_filter(array_map(
        function ($r) { return $r['driver_id'] ?? null; },
        $rides
    ))));

    $passengerMap = [];
    if (!empty($passengerIds)) {
        try {
            $passengers = $db->fetchData('passengers', [
                'select' => 'id,name,email,business_name',
                'filter' => ['id' => 'in.(' . implode(',', $passengerIds) . ')'],
            ]);
            foreach ($passengers as $passenger) {
                if (isset($passenger['id'])) {
                    $passengerMap[$passenger['id']] = $passenger;
                }
            }
        } catch (Exception $e) {
            error_log("Warning: Could not fetch passengers data: " . $e->getMessage());
        }
    }

    foreach ($rides as &$ride) {
        if (isset($ride['user_id']) && isset($passengerMap[$ride['user_id']])) {
            $passenger = $passengerMap[$ride['user_id']];
            $ride['passenger_name'] = $passenger['name'] ?? 'N/A';
            $ride['passenger_email'] = $passenger['email'] ?? 'N/A';
            $ride['company'] = $passenger['business_name'] ?? 'N/A';
        } else {
            $meta = isset($ride['meta']) ? (is_string($ride['meta']) ? json_decode($ride['meta'], true) : $ride['meta']) : [];
            $ride['passenger_name'] = $meta['customer_name'] ?? 'N/A';
            $ride['passenger_email'] = 'N/A';
            $ride['company'] = 'N/A';
        }
    }
    unset($ride);

    // Attach driver name (for tabs that show the assigned driver, e.g. On-Trip)
    $driverMap = [];
    if (!empty($driverIds)) {
        try {
            $drivers = $db->fetchData('drivers', [
                'select' => 'id,full_name,phone,vehicle_number',
                'filter' => ['id' => 'in.(' . implode(',', $driverIds) . ')'],
            ]);
            foreach ($drivers as $driver) {
                if (isset($driver['id'])) {
                    $driverMap[$driver['id']] = $driver;
                }
            }
        } catch (Exception $e) {
            error_log("Warning: Could not fetch drivers data: " . $e->getMessage());
        }
    }

    foreach ($rides as &$ride) {
        if (!empty($ride['driver_id']) && isset($driverMap[$ride['driver_id']])) {
            $driver = $driverMap[$ride['driver_id']];
            $ride['driver_name'] = $driver['full_name'] ?? 'N/A';
            $ride['driver_phone'] = $driver['phone'] ?? null;
            $ride['driver_vehicle_number'] = $driver['vehicle_number'] ?? null;
        } else {
            $ride['driver_name'] = null;
        }
    }
    unset($ride);

    echo json_encode([
        'success' => true,
        'data' => $rides,
        'count' => count($rides),
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'totalPages' => ceil($totalCount / $limit)
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

