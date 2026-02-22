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

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radiusKm = isset($_GET['radius_km']) ? max(0.1, min(50, floatval($_GET['radius_km']))) : 5;

if ($lat === null || $lng === null || (abs($lat) > 90 || abs($lng) > 180)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid lat and lng are required.',
        'data' => []
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Haversine distance in km between two points.
 */
function haversineKm($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

// Only show drivers active within this many minutes (online + recently updated location)
$activeWithinMinutes = isset($_GET['active_within_min']) ? max(1, min(60, intval($_GET['active_within_min']))) : 10;

try {
    $db = new SupabaseDB(null, true);

    // Fetch drivers (with location); get a large set so we can filter by distance and activity
    $drivers = $db->fetchData('drivers', [
        'order' => 'created_at.desc',
        'limit' => 500
    ]);

    $cutoffTime = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify("-{$activeWithinMinutes} minutes");
    $cutoffIso = $cutoffTime->format('Y-m-d\TH:i:s.u\Z');

    $nearby = [];
    foreach ($drivers as $d) {
        // Must have a valid current location (not just saved home address from the past)
        $dlat = isset($d['current_lat']) ? floatval($d['current_lat']) : null;
        $dlng = isset($d['current_lng']) ? floatval($d['current_lng']) : null;
        if ($dlat === null || $dlng === null || is_nan($dlat) || is_nan($dlng)) {
            continue;
        }

        // Only include drivers who are currently online (recent last_active)
        $lastActive = isset($d['last_active']) ? trim((string) $d['last_active']) : null;
        if ($lastActive === null || $lastActive === '') {
            continue;
        }
        if (strtotime($lastActive) < $cutoffTime->getTimestamp()) {
            continue;
        }

        // If drivers table has ride_searching column, only include drivers actively accepting rides
        if (array_key_exists('ride_searching', $d)) {
            $searching = $d['ride_searching'];
            if ($searching === false || $searching === 'f' || $searching === 'false' || $searching === 0 || $searching === '0') {
                continue;
            }
        }

        $dist = haversineKm($lat, $lng, $dlat, $dlng);
        if ($dist <= $radiusKm) {
            $d['distance_km'] = round($dist, 2);
            $nearby[] = $d;
        }
    }

    // Sort by distance ascending
    usort($nearby, function ($a, $b) {
        return ($a['distance_km'] <=> $b['distance_km']);
    });

    echo json_encode([
        'success' => true,
        'data' => $nearby,
        'count' => count($nearby)
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ], JSON_PRETTY_PRINT);
}
