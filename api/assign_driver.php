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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['ride_id']) || !isset($input['driver_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ride ID and Driver ID are required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = new SupabaseDB(null, true);

    // force_assign is sent by the 40-min auto-transition to activate a scheduled
    // ride (move it to 'assigned'); a normal manual assignment does not set it.
    $forceAssign = !empty($input['force_assign']) && $input['force_assign'] !== 'false';

    // Look up the current status. Pre-assigning a driver to a *scheduled* ride must
    // keep it scheduled (just attach the driver) — it should not activate early.
    // Only force_assign (the timed activation) flips a scheduled ride to assigned.
    $existing = $db->findData('rides', ['id' => $input['ride_id']]);
    $currentStatus = !empty($existing) ? strtolower((string)($existing[0]['status'] ?? '')) : '';

    if ($currentStatus === 'scheduled' && !$forceAssign) {
        $newStatus = 'scheduled';
    } else {
        $newStatus = 'assigned';
    }

    // Prepare update data
    $updateData = [
        'driver_id' => $input['driver_id'],
        'status' => $newStatus,
        'updated_at' => date('Y-m-d H:i:s') . '+00'
    ];

    // When a scheduled ride is force-activated (40-min window), clear the
    // is_scheduled flag so the driver app treats it as an active ride.
    if ($forceAssign && $currentStatus === 'scheduled') {
        $updateData['is_scheduled'] = false;
    }
    
    // Update distance, duration, and fare if provided
    if (isset($input['distance_km'])) {
        $updateData['distance_km'] = floatval($input['distance_km']);
    }
    if (isset($input['duration_min'])) {
        $updateData['duration_min'] = intval($input['duration_min']);
    }
    if (isset($input['fare_eur'])) {
        $updateData['fare_eur'] = number_format(floatval($input['fare_eur']), 2, '.', '');
    }
    if (isset($input['service_type']) && trim((string)$input['service_type']) !== '') {
        $updateData['ride_type'] = trim((string)$input['service_type']);
    }

    // Update the ride
    $updatedRide = $db->updateData('rides', $input['ride_id'], $updateData);

    // Notify passenger by email when ride is assigned (e.g. from Searching → Assigned)
    $passengerEmail = null;
    $passengerName = 'Passenger';
    if (!empty($updatedRide['user_id'])) {
        $passengers = $db->findData('passengers', ['id' => $updatedRide['user_id']]);
        if (!empty($passengers)) {
            $p = $passengers[0];
            $passengerEmail = $p['email'] ?? null;
            $passengerName = $p['name'] ?? $p['full_name'] ?? 'Passenger';
        }
    }
    // Look up driver name
    $driverName = null;
    if (!empty($updatedRide['driver_id'])) {
        $drivers = $db->findData('drivers', ['id' => $updatedRide['driver_id']]);
        if (!empty($drivers)) {
            $d = $drivers[0];
            $driverName = $d['name'] ?? $d['full_name'] ?? null;
        }
    }
    // Only notify when the ride actually becomes assigned/active — not when a
    // driver is merely pre-attached to a still-scheduled future booking.
    if ($passengerEmail && $newStatus === 'assigned') {
        require_once __DIR__ . '/../lib/mail_helper.php';
        $pickupAddr = $updatedRide['pickup_addr'] ?? '';
        $destAddr = $updatedRide['dest_addr'] ?? '';
        $rideType = $updatedRide['ride_type'] ?? '';
        $fareEur = $updatedRide['fare_eur'] ?? '';
        $orderDate = '';
        $pickupTime = '';
        if (!empty($updatedRide['created_at'])) {
            $ts = strtotime($updatedRide['created_at']);
            if ($ts !== false) $orderDate = date('l, d M Y', $ts);
        }
        if (!empty($updatedRide['scheduled_at'])) {
            $ts = strtotime($updatedRide['scheduled_at']);
            if ($ts !== false) $pickupTime = date('g:i A', $ts);
        } else {
            $pickupTime = 'As Soon As Possible';
        }
        $emailResult = sendRideAssignedEmail(
            $passengerEmail,
            $passengerName,
            $pickupAddr,
            $destAddr,
            $rideType,
            $fareEur,
            $orderDate,
            $pickupTime
        );
        if ($emailResult !== true) {
            error_log('Ride-assigned email failed: ' . (is_string($emailResult) ? $emailResult : 'unknown'));
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Driver assigned successfully',
        'data' => $updatedRide
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

