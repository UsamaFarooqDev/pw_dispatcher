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
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10; // Default 10, max 100
    
    // Fetch paginated rides
    $rides = $db->fetchData('rides', [
        'order' => 'created_at.desc',
        'page' => $page,
        'limit' => $limit
    ]);
    
    // Get total count
    $totalCount = $db->getCount('rides');
    
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
                $ride['passenger_name'] = 'N/A';
                $ride['passenger_email'] = 'N/A';
                $ride['company'] = 'N/A';
            }
        }
        unset($ride); 
    } catch (Exception $e) {
        error_log("Warning: Could not fetch passengers data: " . $e->getMessage());
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

