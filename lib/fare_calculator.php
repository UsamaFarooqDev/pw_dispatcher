<?php
function currentDublinHour() {
    return (int) (new DateTime('now', new DateTimeZone('Europe/Dublin')))->format('G');
}

/**
 * Authoritative dispatcher-side fare calculation: resolve one
 * pricing_config row via the documented fallback chain — [ride type +
 * period] -> [ride type + 'both'] -> ['all' + period] -> ['all' + 'both']
 * — then meter -> type_multiplier (NOT ride_types.multiplier; the two can
 * drift apart without anything failing) -> surge -> config discount ->
 * minimum_fare clamp. Only drops to the hard-coded estimate when no
 * pricing_config row is reachable at all, matching the app's own
 * documented last-resort tier.
 *
 * @param SupabaseDB $db
 * @param string $rideType Service/ride type — exact ride_types.name string
 * @param float $distanceKm
 * @param float $durationMin
 * @return float Fare in EUR, rounded to 2 decimals
 */
function resolveDispatcherFare($db, $rideType, $distanceKm, $durationMin) {
    $hour = currentDublinHour();
    $period = ($hour >= 8 && $hour < 20) ? 'day' : 'night';

    $config = null;
    try {
        $rows = $db->findData('pricing_config', ['ride_type' => $rideType, 'is_active' => 'true']);
        if ($rideType !== 'all') {
            $allRows = $db->findData('pricing_config', ['ride_type' => 'all', 'is_active' => 'true']);
            $rows = array_merge(is_array($rows) ? $rows : [], is_array($allRows) ? $allRows : []);
        }
        $chain = [[$rideType, $period], [$rideType, 'both'], ['all', $period], ['all', 'both']];
        foreach ($chain as $want) {
            foreach ($rows as $row) {
                if ((string) ($row['ride_type'] ?? '') === (string) $want[0]
                    && (string) ($row['time_period'] ?? '') === (string) $want[1]) {
                    $config = $row;
                    break 2;
                }
            }
        }
    } catch (Exception $e) {
        error_log('resolveDispatcherFare: pricing_config lookup failed: ' . $e->getMessage());
    }

    if ($config === null) {
        return calcFareHardcodedFallback($distanceKm, $durationMin, $rideType, $period);
    }

    $meter = floatval($config['base_fare'])
        + floatval($config['booking_fee'])
        + ($distanceKm * floatval($config['per_km_rate']))
        + ($durationMin * floatval($config['per_min_rate']));

    $surge = (!empty($config['surge_enabled'])) ? floatval($config['surge_multiplier']) : 1.0;
    $raw = $meter * floatval($config['type_multiplier']) * $surge;

    if (!empty($config['discount_enabled'])) {
        $nowUtc = gmdate('Y-m-d\TH:i:s');
        $validFrom = $config['discount_valid_from'] ?? null;
        $validUntil = $config['discount_valid_until'] ?? null;
        $withinWindow = (empty($validFrom) || $nowUtc >= $validFrom) && (empty($validUntil) || $nowUtc <= $validUntil);
        $usesLeft = empty($config['discount_max_uses']) || intval($config['discount_uses_count']) < intval($config['discount_max_uses']);
        $meetsMin = $raw >= floatval($config['discount_min_fare'] ?? 0);
        if ($withinWindow && $usesLeft && $meetsMin) {
            if ($config['discount_type'] === 'percentage') {
                $raw -= $raw * floatval($config['discount_value']) / 100;
            } elseif ($config['discount_type'] === 'fixed') {
                $raw -= floatval($config['discount_value']);
            }
        }
    }

    $minimumFare = isset($config['minimum_fare']) ? floatval($config['minimum_fare']) : 0;
    return round(max($raw, $minimumFare), 2);
}

function calcFareHardcodedFallback($distanceKm, $durationMin, $rideType, $period) {
    $baseFare = 3.0;
    if ($period === 'day') {
        $bookingFee = 4.4;
        $ratePerKm = 1.32;
        $ratePerMinute = 0.20;
    } else {
        $bookingFee = 5.4;
        $ratePerKm = 1.81;
        $ratePerMinute = 0.30;
    }
    $meter = $baseFare + $bookingFee + ($distanceKm * $ratePerKm) + ($durationMin * $ratePerMinute);
    $multipliers = [
        'Economy' => 1.0,
        'Economy XL' => 1.2,
        'Business' => 1.0,
        'Business Plus' => 1.2,
        'Limousine' => 2.0,
        'Wheelchair accessible' => 1.1,
        'Wheelchair Taxi' => 1.1,
        'Pets Taxi' => 1.15,
        'Courier / Parcel' => 0.9,
        'Parcel Delivery' => 0.9,
    ];
    $multiplier = isset($multipliers[$rideType]) ? $multipliers[$rideType] : 1.0;
    return round((float) ($meter * $multiplier), 2);
}
