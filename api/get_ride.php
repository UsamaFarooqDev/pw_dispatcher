<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: Check if user is authenticated
if (empty($_SESSION['admin_id'])) {
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
        $existingEmployee = isset($ride['employee']) ? trim((string)$ride['employee']) : '';
        $existingCompany = isset($ride['company']) ? trim((string)$ride['company']) : '';
        try {
            $passengers = $db->findData('passengers', ['id' => $ride['user_id']]);
            if (!empty($passengers)) {
                $passenger = $passengers[0];
                $ride['passenger_name'] = $passenger['name'] ?? 'N/A';
                $ride['passenger_email'] = $passenger['email'] ?? 'N/A';
                $ride['passenger_phone'] = $passenger['phone'] ?? 'N/A';
                $ride['company'] = $passenger['business_name'] ?? ($existingCompany !== '' ? $existingCompany : 'N/A');
            } else {
                $corpEmployee = null;
                try {
                    $corpMatches = $db->findData('corporate_employees', ['id' => $ride['user_id']]);
                    if (!empty($corpMatches)) $corpEmployee = $corpMatches[0];
                } catch (Exception $e) {
                    error_log("Warning: Could not fetch corporate_employees data: " . $e->getMessage());
                }
                if ($corpEmployee) {
                    $ride['passenger_name'] = $corpEmployee['name'] ?? ($existingEmployee !== '' ? $existingEmployee : 'N/A');
                    $ride['passenger_email'] = $corpEmployee['email'] ?? 'N/A';
                    $ride['passenger_phone'] = $corpEmployee['phone'] ?? 'N/A';
                    $ride['company'] = $corpEmployee['company'] ?? ($existingCompany !== '' ? $existingCompany : 'N/A');
                } else {
                    $ride['passenger_name'] = $existingEmployee !== '' ? $existingEmployee : 'N/A';
                    $ride['passenger_email'] = 'N/A';
                    $ride['passenger_phone'] = 'N/A';
                    $ride['company'] = $existingCompany !== '' ? $existingCompany : 'N/A';
                }
            }
        } catch (Exception $e) {
            error_log("Warning: Could not fetch passenger data: " . $e->getMessage());
            $ride['passenger_name'] = $existingEmployee !== '' ? $existingEmployee : 'N/A';
            $ride['passenger_email'] = 'N/A';
            $ride['passenger_phone'] = 'N/A';
            $ride['company'] = $existingCompany !== '' ? $existingCompany : 'N/A';
        }
    } else {
        $meta = isset($ride['meta']) ? (is_string($ride['meta']) ? json_decode($ride['meta'], true) : $ride['meta']) : [];
        $ride['passenger_name'] = $meta['customer_name'] ?? 'N/A';
        $ride['passenger_phone'] = $meta['customer_phone'] ?? 'N/A';
        $ride['passenger_email'] = 'N/A';
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

