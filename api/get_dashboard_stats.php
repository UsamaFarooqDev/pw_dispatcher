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
    $corporate = $db->getCount('rides', [
        'filter' => ['source' => 'ilike.corporate*'],
    ]);

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

    // ---- Analytics: rides last 7 days (grouped per day, timezone Europe/Dublin) ----
    $tz = new DateTimeZone('Europe/Dublin');
    $today = new DateTime('now', $tz);
    $startOfRange = (clone $today)->modify('-6 days')->setTime(0, 0, 0);
    $sinceIso = $startOfRange->format('Y-m-d\TH:i:sP');

    $ch = curl_init(SUPABASE_URL . '/rest/v1/rides?select=created_at,status&created_at=gte.' . urlencode($sinceIso) . '&limit=5000');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
            'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    $recentRaw = curl_exec($ch);
    $recentRides = json_decode($recentRaw, true);
    if (!is_array($recentRides)) $recentRides = [];

    $daily = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = (clone $today)->modify("-$i days")->format('Y-m-d');
        $daily[$day] = ['total' => 0, 'completed' => 0, 'cancelled' => 0];
    }
    foreach ($recentRides as $r) {
        if (empty($r['created_at'])) continue;
        try {
            $d = (new DateTime($r['created_at']))->setTimezone($tz)->format('Y-m-d');
        } catch (Exception $e) {
            continue;
        }
        if (!isset($daily[$d])) continue;
        $daily[$d]['total']++;
        $status = strtolower($r['status'] ?? '');
        if ($status === 'completed' || $status === 'finished') $daily[$d]['completed']++;
        if ($status === 'cancelled' || $status === 'canceled') $daily[$d]['cancelled']++;
    }
    $ridesLast7 = [];
    foreach ($daily as $date => $counts) {
        $ridesLast7[] = array_merge(['date' => $date], $counts);
    }

    // ---- Analytics: driver verification breakdown ----
    $driversStatus = [
        'approved' => $db->getCount('drivers', ['filter' => ['status' => 'ilike.approved']]),
        'pending'  => $db->getCount('drivers', ['filter' => ['status' => 'ilike.pending']]),
        'rejected' => $db->getCount('drivers', ['filter' => ['status' => 'ilike.rejected']]),
    ];

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
            'rides_last_7_days' => $ridesLast7,
            'drivers_status' => $driversStatus,
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

