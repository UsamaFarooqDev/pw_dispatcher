<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

// Security: only allow authenticated dispatcher users
if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null,
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Use service role to bypass RLS for dashboard aggregates.
    // RLS is handled via application-level auth check above.
    $db = new SupabaseDB(null, true);

    $drivers = $db->getCount('drivers');
    $passengers = $db->getCount('passengers');
    $rides = $db->getCount('rides');
    $corporate = $db->getCount('corporate_rides');

    // Ride statuses used by the dashboard cards.
    // Case-insensitive status counts to match the live-orders client-side logic
    // (which lowercases status before comparing).
    $unassigned = $db->getCount('rides', [
        'filter' => ['status' => 'ilike.searching'],
    ]);
    $assigned = $db->getCount('rides', [
        'filter' => ['status' => 'ilike.assigned'],
    ]);

    // Scheduled: upcoming or scheduled
    $scheduled = $db->getCount('rides', [
        'filter' => ['status' => 'ilike.upcoming'],
    ]) + $db->getCount('rides', [
        'filter' => ['status' => 'ilike.scheduled'],
    ]);

    // Completed: completed or finished
    $completed = $db->getCount('rides', [
        'filter' => ['status' => 'ilike.completed'],
    ]) + $db->getCount('rides', [
        'filter' => ['status' => 'ilike.finished'],
    ]);

    // Cancelled: cancelled or canceled
    $cancelled = $db->getCount('rides', [
        'filter' => ['status' => 'ilike.cancelled'],
    ]) + $db->getCount('rides', [
        'filter' => ['status' => 'ilike.canceled'],
    ]);

    // Per your request, show On Trip as 0.
    $onTrip = 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'drivers' => $drivers,
            'passengers' => $passengers,
            'rides' => $rides,
            'corporate_rides' => $corporate,
            'unassigned' => $unassigned,
            'assigned' => $assigned,
            'on_trip' => $onTrip,
            'scheduled' => $scheduled,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ],
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null,
    ], JSON_PRETTY_PRINT);
}

?>

