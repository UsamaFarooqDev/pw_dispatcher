<?php
header('Content-Type: application/json');
session_start();
require_once '../auth/config.php';

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['corp_id']) || !isset($input['driver_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Corporate ride ID and driver ID are required',
        'data' => null
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Look up the rides row already mirrored for a corporate ride by filtering on
 * rides.meta->>corp_id. The helper methods in SupabaseDB don't expose a way to
 * filter on a jsonb path, so this is done with a direct REST call.
 */
function find_mirror_ride_id($corpId) {
    $url = SUPABASE_URL . '/rest/v1/rides?select=id&meta->>corp_id=eq.' . urlencode((string)$corpId) . '&limit=1';
    $ch = curl_init($url);
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
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code !== 200 && $code !== 206) return null;
    $rows = json_decode($body, true);
    return (is_array($rows) && !empty($rows) && !empty($rows[0]['id'])) ? $rows[0]['id'] : null;
}

/**
 * Create an auth.users row via the Supabase admin API and return the new UUID.
 * passengers.id is a FK to auth.users.id, so we must mint an auth user before
 * we can insert a passenger. Uses the service-role key.
 */
function create_auth_user($email, $name) {
    $payload = [
        'email' => $email,
        // Random password; the corporate employee never signs in as a passenger.
        'password' => bin2hex(random_bytes(16)),
        'email_confirm' => true,
        'user_metadata' => [
            'full_name' => $name,
            'source' => 'corporate',
        ],
    ];
    $ch = curl_init(SUPABASE_URL . '/auth/v1/admin/users');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
            'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $decoded = json_decode($body, true);

    if ($code === 200 || $code === 201) {
        return $decoded['id'] ?? null;
    }
    // If the email is already registered, fetch the existing user id.
    $msg = strtolower((string)($decoded['msg'] ?? $decoded['error_description'] ?? $decoded['message'] ?? ''));
    if (strpos($msg, 'already') !== false || $code === 422) {
        return find_auth_user_id_by_email($email);
    }
    error_log('create_auth_user failed (HTTP ' . $code . '): ' . $body);
    return null;
}

function find_auth_user_id_by_email($email) {
    $url = SUPABASE_URL . '/auth/v1/admin/users?email=' . urlencode($email);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
            'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code !== 200) return null;
    $decoded = json_decode($body, true);
    $users = $decoded['users'] ?? (is_array($decoded) ? $decoded : []);
    foreach ($users as $u) {
        if (!empty($u['email']) && strcasecmp($u['email'], $email) === 0 && !empty($u['id'])) {
            return $u['id'];
        }
    }
    return null;
}

try {
    $db = new SupabaseDB(null, true);

    // ── Step 1: Validate every piece of input up front ───────────────────
    // The driver app reads from `rides`, which has strict NOT NULL columns
    // (lat/lng, ride_type, payment_method, etc). We fail early here so the
    // corporate ride row is never marked as assigned without a valid payload.
    $pickupLat = isset($input['pickup_lat']) && is_numeric($input['pickup_lat']) ? floatval($input['pickup_lat']) : null;
    $pickupLng = isset($input['pickup_lng']) && is_numeric($input['pickup_lng']) ? floatval($input['pickup_lng']) : null;
    $destLat   = isset($input['dest_lat'])   && is_numeric($input['dest_lat'])   ? floatval($input['dest_lat'])   : null;
    $destLng   = isset($input['dest_lng'])   && is_numeric($input['dest_lng'])   ? floatval($input['dest_lng'])   : null;
    if ($pickupLat === null || $pickupLng === null || $destLat === null || $destLng === null) {
        throw new Exception('Pickup/drop-off coordinates missing. Please wait for the map to calculate the route and try again.');
    }

    // ── Step 2: Load and validate driver ─────────────────────────────────
    $drivers = $db->findData('drivers', ['id' => $input['driver_id']]);
    if (empty($drivers)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Driver not found', 'data' => null], JSON_PRETTY_PRINT);
        exit;
    }
    $driver = $drivers[0];
    $vehicleNumber = $driver['vehicle_number'] ?? $driver['plate_no'] ?? null;
    if (empty($vehicleNumber)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Selected driver has no vehicle number on file',
            'data' => null
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // ── Step 3: Load the corporate ride (source of truth for the booking) ─
    $corpRows = $db->findData('rides', ['id' => $input['corp_id']]);
    if (empty($corpRows)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Corporate ride not found', 'data' => null], JSON_PRETTY_PRINT);
        exit;
    }
    $corp = $corpRows[0];
    $corpSource = strtolower(trim((string)($corp['source'] ?? '')));
    if ($corpSource !== 'corporate' && $corpSource !== 'corporate meet_and_greet') {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Corporate ride not found', 'data' => null], JSON_PRETTY_PRINT);
        exit;
    }

    // ── Step 4: Resolve the passenger for the employee ───────────────────
    //   - rides.user_id is NOT NULL and is a FK to auth.users.id
    //   - Try to reuse an existing passenger matched by email
    //   - Otherwise mint an auth user via the admin API, then insert passenger
    $employeeId = $corp['employee_id'] ?? null;
    $employeeName = trim((string)($corp['employee'] ?? 'Corporate Employee'));
    $employeeEmail = null;
    $employeePhone = null;
    if ($employeeId) {
        $emps = $db->findData('corporate_employees', ['id' => $employeeId]);
        if (!empty($emps)) {
            $emp = $emps[0];
            $employeeEmail = $emp['email'] ?? null;
            $employeePhone = $emp['phone'] ?? null;
            if (empty($employeeName) || $employeeName === 'Corporate Employee') {
                $employeeName = trim((string)($emp['name'] ?? $employeeName));
            }
        }
    }

    $passengerId = null;
    if (!empty($employeeEmail)) {
        $existing = $db->findData('passengers', ['email' => $employeeEmail]);
        if (!empty($existing)) {
            $passengerId = $existing[0]['id'] ?? null;
        }
    }
    if (!$passengerId) {
        $emailForAuth = $employeeEmail
            ?: ('corp-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$employeeId)) . '@corporate.powercabs.local');
        $authUserId = create_auth_user($emailForAuth, $employeeName ?: 'Corporate Employee');
        if (!$authUserId) {
            throw new Exception('Could not create auth user for corporate employee');
        }
        $passengerRow = $db->insertData('passengers', [
            'id' => $authUserId,
            'name' => $employeeName ?: 'Corporate Employee',
            'email' => $emailForAuth,
            'phone' => $employeePhone,
            'is_email_verified' => true,
            'status' => 'active',
            'meta' => [
                'source' => 'corporate',
                'corp_employee_id' => $employeeId,
                'cid' => $corp['cid'] ?? null,
                'company' => $corp['company'] ?? null,
            ],
        ]);
        $passengerId = $passengerRow['id'] ?? $authUserId;
    }
    if (!$passengerId) {
        throw new Exception('Could not resolve a passenger record for the corporate employee');
    }

    // ── Step 5: Update the corporate ride row in `rides` so driver app sees it ─
    $pickupAddr = isset($input['pickup']) && trim((string)$input['pickup']) !== ''
        ? trim((string)$input['pickup']) : ($corp['pickup_addr'] ?? ($corp['pickup'] ?? ''));
    $destAddr = isset($input['destination']) && trim((string)$input['destination']) !== ''
        ? trim((string)$input['destination']) : ($corp['dest_addr'] ?? ($corp['destination'] ?? ''));
    $rideType = isset($input['service_type']) && trim((string)$input['service_type']) !== ''
        ? trim((string)$input['service_type']) : ($corp['ride_type'] ?? ($corp['carType'] ?? 'Economy'));
    $paymentMethod = $corp['payment_method'] ?? ($corp['payment_source'] ?? 'cash');
    $fareEur = isset($input['fare_eur']) ? floatval($input['fare_eur']) : (isset($corp['fare_eur']) ? floatval($corp['fare_eur']) : (isset($corp['fare']) ? floatval($corp['fare']) : 0));
    $distanceKm = isset($input['distance_km']) ? floatval($input['distance_km']) : (isset($corp['distance_km']) ? floatval($corp['distance_km']) : (isset($corp['distance']) ? floatval($corp['distance']) : 0));
    $durationMin = isset($input['duration_min']) ? intval($input['duration_min']) : (isset($corp['duration_min']) ? intval($corp['duration_min']) : (isset($corp['eta']) ? intval($corp['eta']) : 0));

    $rideUpdatePayload = [
        'user_id' => $passengerId,
        'driver_id' => $input['driver_id'],
        'pickup_addr' => $pickupAddr,
        'pickup_lat' => $pickupLat,
        'pickup_lng' => $pickupLng,
        'dest_addr' => $destAddr,
        'dest_lat' => $destLat,
        'dest_lng' => $destLng,
        'ride_type' => $rideType,
        'payment_method' => $paymentMethod,
        'fare_eur' => $fareEur,
        'distance_km' => $distanceKm,
        'duration_min' => $durationMin,
        'status' => 'assigned',
        'source' => $corpSource,
        'vehicle_number' => $vehicleNumber,
        // Keep legacy/display fields in sync for dispatcher and corporate UIs.
        'pickup' => $pickupAddr,
        'destination' => $destAddr,
        'payment_source' => $corp['payment_source'] ?? $paymentMethod,
        'carType' => $rideType,
        'fare' => number_format($fareEur, 2, '.', ''),
        'distance' => $distanceKm,
        'eta' => $durationMin,
        'meta' => [
            'corp_id' => (string)($corp['id'] ?? $input['corp_id']),
            'cid' => $corp['cid'] ?? null,
            'company' => $corp['company'] ?? null,
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
        ],
    ];
    if (isset($input['pickup_time']) && trim((string)$input['pickup_time']) !== '') {
        $rideUpdatePayload['pickupTime'] = trim((string)$input['pickup_time']);
    }

    $updatedCorp = $db->updateData('rides', $input['corp_id'], $rideUpdatePayload);

    echo json_encode([
        'success' => true,
        'message' => 'Driver assigned successfully',
        'data' => [
            'corporate_ride' => $updatedCorp,
            'ride' => $updatedCorp,
        ],
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
