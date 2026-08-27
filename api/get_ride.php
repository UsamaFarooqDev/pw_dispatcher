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

    // Cascade through every available source instead of stopping at the
    // first record that happens to be present but blank (e.g. a real
    // passenger row whose name was never filled in) — that used to quietly
    // drop straight to "N/A" instead of falling back further to a name the
    // ride itself already has on hand (meta.customer_name, or the employee
    // column for a corporate ride).
    $existingEmployee = isset($ride['employee']) ? trim((string)$ride['employee']) : '';
    $existingCompany = isset($ride['company']) ? trim((string)$ride['company']) : '';
    $meta = isset($ride['meta']) ? (is_string($ride['meta']) ? json_decode($ride['meta'], true) : $ride['meta']) : [];
    $metaCustomerName = is_array($meta) && !empty($meta['customer_name']) ? trim((string)$meta['customer_name']) : '';
    $metaCustomerPhone = is_array($meta) && !empty($meta['customer_phone']) ? trim((string)$meta['customer_phone']) : '';

    $passenger = null;
    $corpEmployee = null;
    if (isset($ride['user_id'])) {
        try {
            $passengers = $db->findData('passengers', ['id' => $ride['user_id']]);
            if (!empty($passengers)) {
                $passenger = $passengers[0];
            } else {
                $corpMatches = $db->findData('corporate_employees', ['id' => $ride['user_id']]);
                if (!empty($corpMatches)) $corpEmployee = $corpMatches[0];
            }
        } catch (Exception $e) {
            error_log("Warning: Could not fetch passenger data: " . $e->getMessage());
        }
    }

    $passengerName = ($passenger && !empty($passenger['name'])) ? trim((string)$passenger['name']) : '';
    $corpEmployeeName = ($corpEmployee && !empty($corpEmployee['name'])) ? trim((string)$corpEmployee['name']) : '';

    if ($passengerName !== '') {
        $ride['passenger_name'] = $passengerName;
    } elseif ($corpEmployeeName !== '') {
        $ride['passenger_name'] = $corpEmployeeName;
    } elseif ($existingEmployee !== '') {
        $ride['passenger_name'] = $existingEmployee;
    } elseif ($metaCustomerName !== '') {
        $ride['passenger_name'] = $metaCustomerName;
    } else {
        $ride['passenger_name'] = 'N/A';
    }

    $ride['passenger_email'] = $passenger['email'] ?? ($corpEmployee['email'] ?? 'N/A');
    $ride['passenger_phone'] = $passenger['phone'] ?? ($corpEmployee['phone'] ?? ($metaCustomerPhone !== '' ? $metaCustomerPhone : 'N/A'));
    $ride['company'] = $passenger['business_name'] ?? ($corpEmployee['company'] ?? ($existingCompany !== '' ? $existingCompany : 'N/A'));
    
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

