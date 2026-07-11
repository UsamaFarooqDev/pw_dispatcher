<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';
require_once '../auth/role_guard.php';

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

    // Fetch paginated rides
    $rides = $db->fetchData('rides', [
        'order' => 'created_at.desc',
        'page' => $page,
        'limit' => $limit
    ]);

    // Get total count
    $totalCount = $db->getCount('rides');

    // Dispatcher role only ever sees Powercabs Dispatch-created orders — filter
    // out app/corporate rides (and their embedded passenger PII) server-side,
    // not just in the UI.
    if (isDispatcherRole()) {
        $rides = array_values(array_filter($rides, function ($r) {
            return stripos((string)($r['source'] ?? ''), 'dispatch') !== false;
        }));
        $totalCount = count($rides);
    }

    // Fetch all passengers for mapping (or optimize to fetch only needed ones)
    $passengers = [];
    try {
        $passengers = $db->fetchData('passengers');
        $passengerMap = [];
        foreach ($passengers as $passenger) {
            if (isset($passenger['id'])) {
                $passengerMap[$passenger['id']] = $passenger;
            }
        }
        
        foreach ($rides as &$ride) {
            if (isset($ride['user_id']) && isset($passengerMap[$ride['user_id']])) {
                $passenger = $passengerMap[$ride['user_id']];
                $ride['passenger_name'] = $passenger['name'] ?? $passenger['full_name'] ?? 'N/A';
                $ride['passenger_email'] = $passenger['email'] ?? 'N/A';
                $ride['company'] = $passenger['company'] ?? 'N/A';
            } else {
                $meta = isset($ride['meta']) ? (is_string($ride['meta']) ? json_decode($ride['meta'], true) : $ride['meta']) : [];
                $ride['passenger_name'] = $meta['customer_name'] ?? 'N/A';
                $ride['passenger_email'] = 'N/A';
                $ride['company'] = 'N/A';
            }
        }
        unset($ride);
    } catch (Exception $e) {
        error_log("Warning: Could not fetch passengers data: " . $e->getMessage());
    }

    // Attach driver name (for tabs that show the assigned driver, e.g. On-Trip)
    try {
        $drivers = $db->fetchData('drivers');
        $driverMap = [];
        foreach ($drivers as $driver) {
            if (isset($driver['id'])) {
                $driverMap[$driver['id']] = $driver;
            }
        }

        foreach ($rides as &$ride) {
            if (!empty($ride['driver_id']) && isset($driverMap[$ride['driver_id']])) {
                $driver = $driverMap[$ride['driver_id']];
                $ride['driver_name'] = $driver['full_name'] ?? $driver['name'] ?? 'N/A';
                $ride['driver_phone'] = $driver['phone'] ?? null;
                $ride['driver_vehicle_number'] = $driver['vehicle_number'] ?? null;
            } else {
                $ride['driver_name'] = null;
            }
        }
        unset($ride);
    } catch (Exception $e) {
        error_log("Warning: Could not fetch drivers data: " . $e->getMessage());
    }

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

