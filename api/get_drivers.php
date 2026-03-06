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
    // Use service role key to bypass RLS policies
    // Security is handled at application level (authentication check above)
    $db = new SupabaseDB(null, true);
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10; // Default 10, max 100
    
    // Fetch paginated data
    $drivers = $db->fetchData('drivers', [
        'order' => 'created_at.desc',
        'page' => $page,
        'limit' => $limit
    ]);

    // Enrich each driver with total completed rides (all-time)
    if (is_array($drivers)) {
        foreach ($drivers as &$driver) {
            if (!isset($driver['id'])) {
                $driver['total_completed_rides'] = 0;
                continue;
            }

            try {
                $completedCount = $db->getCount('rides', [
                    'filter' => [
                        'driver_id' => $driver['id'],
                        'status' => 'completed'
                    ]
                ]);
            } catch (Exception $innerException) {
                // If count query fails for any reason, default to 0 for this driver
                $completedCount = 0;
            }

            $driver['total_completed_rides'] = $completedCount;
        }
        unset($driver);
    }
    
    // Get total count
    $totalCount = $db->getCount('drivers');
    
    echo json_encode([
        'success' => true,
        'data' => $drivers,
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