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
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get ride ID from query parameter
$rideId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$rideId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ride ID is required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true);
    
    // Fetch the ride by ID
    $rides = $db->findData('rides', ['id' => $rideId]);
    
    if (empty($rides)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Ride not found',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $ride = $rides[0];
    
    // Fetch passenger information if user_id exists
    if (isset($ride['user_id'])) {
        try {
            $passengers = $db->findData('passengers', ['id' => $ride['user_id']]);
            if (!empty($passengers)) {
                $passenger = $passengers[0];
                $ride['passenger_name'] = $passenger['name'] ?? $passenger['full_name'] ?? 'N/A';
                $ride['passenger_email'] = $passenger['email'] ?? 'N/A';
                $ride['passenger_phone'] = $passenger['phone'] ?? $passenger['phone_number'] ?? 'N/A';
                $ride['company'] = $passenger['company'] ?? 'N/A';
            } else {
                $ride['passenger_name'] = 'N/A';
                $ride['passenger_email'] = 'N/A';
                $ride['passenger_phone'] = 'N/A';
                $ride['company'] = 'N/A';
            }
        } catch (Exception $e) {
            error_log("Warning: Could not fetch passenger data: " . $e->getMessage());
            $ride['passenger_name'] = 'N/A';
            $ride['passenger_email'] = 'N/A';
            $ride['passenger_phone'] = 'N/A';
            $ride['company'] = 'N/A';
        }
    } else {
        $ride['passenger_name'] = 'N/A';
        $ride['passenger_email'] = 'N/A';
        $ride['passenger_phone'] = 'N/A';
        $ride['company'] = 'N/A';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $ride
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
?>

