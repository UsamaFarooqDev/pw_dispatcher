<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

/**
 * @return string 
 */
function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return sprintf(
        '%08s-%04s-%04s-%04s-%12s',
        bin2hex(substr($data, 0, 4)),
        bin2hex(substr($data, 4, 2)),
        bin2hex(substr($data, 6, 2)),
        bin2hex(substr($data, 8, 2)),
        bin2hex(substr($data, 10, 6))
    );
}

/**
 * @param string 
 * @param string
 * @param string 
 * @return array 
 */
function createSupabaseUser($email, $phone, $password = null) {
    if ($password === null) {
        $password = bin2hex(random_bytes(16)) . 'A1!'; 
    }

    $userEmail = $email ?: $phone . '@temp.passenger';

    $authUrl = SUPABASE_URL . '/auth/v1/admin/users';
    $payload = json_encode([
        'email' => $userEmail,
        'password' => $password,
        'phone' => $phone,
        'email_confirm' => true,
        'phone_confirm' => true 
    ]);

    $headers = [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY
    ];

    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);

    if ($response === false) {
        throw new Exception('Failed to create user: ' . $curlErr);
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 || $httpCode === 201) {
        return $data;
    } else {
        if (isset($data['message']) && (strpos($data['message'], 'already') !== false || strpos($data['message'], 'exists') !== false)) {
            return getUserByEmail($userEmail);
        }
        throw new Exception('Failed to create user: ' . (isset($data['message']) ? $data['message'] : 'Unknown error'));
    }
}

/**
 * @param string $email User email
 * @return array User data
 */
function getUserByEmail($email) {
    $authUrl = SUPABASE_URL . '/auth/v1/admin/users?email=' . urlencode($email);

    $headers = [
        'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY
    ];

    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (!empty($data['users']) && count($data['users']) > 0) {
            return $data['users'][0];
        }
    }

    throw new Exception('User not found');
}

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST;
    }

    // driver_id is optional; if missing, ride will be set to "searching"
    $requiredFields = [
        'phone_number',
        'service_type',
        'seats',
        'customer_name',
        'date',
        'time',
        'pickup_lat',
        'pickup_lng',
        'pickup_addr',
        'dest_lat',
        'dest_lng',
        'dest_addr'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields: ' . implode(', ', $missingFields),
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $db = new SupabaseDB(null, true);

    $phoneNumber = trim($input['phone_number']);
    if (!preg_match('/^\+/', $phoneNumber)) {

    }

    $passengers = $db->findData('passengers', ['phone' => $phoneNumber]);
    $userId = null;

    if (!empty($passengers)) {
        $userId = $passengers[0]['id'];
    } else {
        $userEmail = isset($input['email']) ? $input['email'] : null;
        try {
            $authUser = createSupabaseUser($userEmail, $phoneNumber);
            $userId = $authUser['id'];
        } catch (Exception $e) {
            try {
                $tempEmail = $userEmail ?: $phoneNumber . '@temp.passenger';
                $authUser = getUserByEmail($tempEmail);
                $userId = $authUser['id'];
            } catch (Exception $e2) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create or find user: ' . $e->getMessage(),
                    'data' => null
                ], JSON_PRETTY_PRINT);
                exit;
            }
        }


        $passengerData = [
            'id' => $userId, // Use the user ID from Auth
            'phone' => $phoneNumber,
            'name' => $input['customer_name'],
            'email' => $userEmail,
            'is_email_verified' => false
        ];

        try {
            $newPassenger = $db->insertData('passengers', $passengerData);
        } catch (Exception $e) {

            $existingPassengers = $db->findData('passengers', ['id' => $userId]);
            if (!empty($existingPassengers)) {
                $userId = $existingPassengers[0]['id'];
            } else {
                throw $e;
            }
        }
    }


    $drivers = $db->findData('drivers', ['id' => $input['driver_id']]);
    if (empty($drivers)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid driver ID. Driver not found.',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }


    $date = $input['date'];
    $time = $input['time'];

    $scheduledDateTime = null;
    if (strtolower($date) === 'today') {
        $scheduledDate = date('Y-m-d');
    } elseif (strtolower($date) === 'tomorrow') {
        $scheduledDate = date('Y-m-d', strtotime('+1 day'));
    } else {

        $parsedDate = strtotime($date);
        if ($parsedDate !== false) {
            $scheduledDate = date('Y-m-d', $parsedDate);
        } else {
            $scheduledDate = date('Y-m-d'); // Default to today
        }
    }


    if (strtolower($time) === 'now') {
        $scheduledTime = date('H:i:s');
    } elseif (strtolower($time) === 'later') {

        $scheduledTime = date('H:i:s', strtotime('+1 hour'));
    } else {

        $parsedTime = strtotime($time);
        if ($parsedTime !== false) {
            $scheduledTime = date('H:i:s', $parsedTime);
        } else {

            if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches)) {
                $scheduledTime = sprintf('%02d:%02d:%02d', $matches[1], $matches[2], isset($matches[3]) ? $matches[3] : 0);
            } else {
                $scheduledTime = date('H:i:s'); // Default to current time
            }
        }
    }

    $scheduledDateTime = $scheduledDate . ' ' . $scheduledTime;


    $baseFare = isset($input['base_fare']) ? floatval($input['base_fare']) : 0;
    $extraCost = isset($input['extra_cost']) ? floatval($input['extra_cost']) : 0;
    $specialCost = isset($input['special_cost']) ? floatval($input['special_cost']) : 0;
    $fareEur = $baseFare + $extraCost + $specialCost;


    if ($fareEur == 0) {
        $serviceTypeFares = [
            'Economy' => 20.00,
            'Business' => 35.00,
            'Premium' => 50.00
        ];
        $fareEur = $serviceTypeFares[$input['service_type']] ?? 20.00;
    }


    $metaData = [
        'seats' => intval($input['seats']),
        'customer_name' => $input['customer_name'],
        'scheduled_date' => $scheduledDate,
        'scheduled_time' => $scheduledTime,
        'scheduled_datetime' => $scheduledDateTime,
        'extra_cost' => $extraCost,
        'special_cost' => $specialCost,
        'base_fare' => $baseFare,
        'source' => 'dispatcher'
    ];


    if (isset($input['km_included'])) {
        $metaData['km_included'] = floatval($input['km_included']);
    }
    if (isset($input['minutes_included'])) {
        $metaData['minutes_included'] = intval($input['minutes_included']);
    }


    $extras = [];
    $extraFields = [
        'accept_credit_card',
        'person_with_disabilities',
        'child_seat',
        'non_smoking',
        'smoking_allowed',
        'extra_luggage',
        'pets_allowed',
        'air_conditioning',
        'bike_mount',
        'delivery'
    ];

    foreach ($extraFields as $extraField) {
        if (isset($input[$extraField]) && ($input[$extraField] === true || $input[$extraField] === 'true' || $input[$extraField] === '1')) {
            $extras[] = $extraField;
        }
    }

    if (!empty($extras)) {
        $metaData['extras'] = $extras;
    }


    $distanceKm = isset($input['distance_km']) ? floatval($input['distance_km']) : 0;
    $durationMin = isset($input['duration_min']) ? intval($input['duration_min']) : 0;



    if ($distanceKm == 0 && isset($input['pickup_lat']) && isset($input['pickup_lng']) && 
        isset($input['dest_lat']) && isset($input['dest_lng'])) {

        $lat1 = floatval($input['pickup_lat']);
        $lon1 = floatval($input['pickup_lng']);
        $lat2 = floatval($input['dest_lat']);
        $lon2 = floatval($input['dest_lng']);

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distanceKm = round($earthRadius * $c, 2);


        $durationMin = round(($distanceKm / 50) * 60);
    }


    // Determine status based on whether driver is provided
    $driverIdInput = isset($input['driver_id']) ? trim((string)$input['driver_id']) : null;
    $hasDriver = !empty($driverIdInput);
    $driverId = $hasDriver ? $driverIdInput : null;

    // Sanitize user_id in case it's present/empty
    $userIdInput = isset($input['user_id']) ? trim((string)$input['user_id']) : null;
    if ($userIdInput === '') {
        $input['user_id'] = null;
    }
    $status = $hasDriver ? 'assigned' : 'searching';

    $rideData = [
        'user_id' => $userId,
        'pickup_lat' => floatval($input['pickup_lat']),
        'pickup_lng' => floatval($input['pickup_lng']),
        'pickup_addr' => $input['pickup_addr'],
        'dest_lat' => floatval($input['dest_lat']),
        'dest_lng' => floatval($input['dest_lng']),
        'dest_addr' => $input['dest_addr'],
        'ride_type' => $input['service_type'],
        'payment_method' => isset($input['payment_method']) ? $input['payment_method'] : 'cash',
        'distance_km' => $distanceKm,
        'duration_min' => $durationMin,
        'fare_eur' => number_format($fareEur, 2, '.', ''),
        'status' => $status,
        'driver_id' => $driverId,
        'meta' => json_encode($metaData),
        'source' => 'dispatcher',
        'created_at' => date('Y-m-d H:i:s') . '+00',
        'updated_at' => date('Y-m-d H:i:s') . '+00',
        'driver_heading' => 0,
        'tip_amount' => '0'
    ];

    $newRide = $db->insertData('rides', $rideData);


    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'data' => $newRide
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