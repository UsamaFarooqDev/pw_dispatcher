<?php
/**
 * Mail helper for PowerCabs Dispatcher.
 * Uses same SMTP credentials as reminderemail (admin@powercabs.ie / mail.powercabs.ie).
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mailerBase = dirname(__DIR__) . '/vendor/phpmailer/phpmailer/src';
require_once $mailerBase . '/Exception.php';
require_once $mailerBase . '/PHPMailer.php';
require_once $mailerBase . '/SMTP.php';

/** SMTP config – same as reminderemail.php (PowerCabs) */
 define('MAIL_USERNAME', 'admin@powercabs.ie');
 define('MAIL_HOST', 'mail.powercabs.ie');
 define('MAIL_PASSWORD', 'Pwcabs@_1234');
 define('MAIL_FROM_ADDRESS', 'admin@powercabs.ie');
 define('MAIL_FROM_NAME', 'PowerCabs Admin');

//define('MAIL_HOST', 'smtp.gmail.com');
//define('MAIL_USERNAME', 'arsalar286@gmail.com');
//define('MAIL_PASSWORD', 'qrpa qtqt qwgr zvus');
//define('MAIL_FROM_ADDRESS', 'arsalar286@gmail.com');
//define('MAIL_FROM_NAME', 'PowerCabs Admin');

/**
 * Send ride-assigned notification to passenger.
 * Returns true on success, or error string on failure.
 *
 * @param string $passengerEmail
 * @param string $passengerName
 * @param string $pickupAddr
 * @param string $destAddr
 * @param string $rideType
 * @param string $fareEur
 * @param string|null $templateDir Optional directory containing email_ride_assigned.html
 * @return true|string
 */
function sendRideAssignedEmail($passengerEmail, $passengerName, $pickupAddr, $destAddr, $rideType, $fareEur, $templateDir = null) {
    if (empty($passengerEmail) || !filter_var($passengerEmail, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid or missing passenger email';
    }
    // Skip temp placeholder emails (e.g. phone@temp.passenger)
    if (stripos($passengerEmail, '@temp.passenger') !== false) {
        return 'Passenger has no real email (temp placeholder)';
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug   = SMTP::DEBUG_OFF;
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer debug (level {$level}): {$str}");
        };
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 20;

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($passengerEmail);
        $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->isHTML(true);
        $mail->CharSet  = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->Subject  = 'Your ride has been assigned - PowerCabs';

        $body = buildRideAssignedBody($passengerName, $pickupAddr, $destAddr, $rideType, $fareEur, $templateDir);
        $mail->Body    = $body;
        $mail->AltBody = buildRideAssignedAltBody($passengerName, $pickupAddr, $destAddr, $rideType, $fareEur);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Ride-assigned email error: ' . $mail->ErrorInfo);
        return $mail->ErrorInfo;
    }
}

/**
 * Plain-text alternative body. Required for good deliverability — mailers
 * without AltBody get higher spam scores and are frequently dropped.
 */
function buildRideAssignedAltBody($passengerName, $pickupAddr, $destAddr, $rideType, $fareEur) {
    $name   = $passengerName ?: 'Passenger';
    $pickup = $pickupAddr    ?: '-';
    $dest   = $destAddr      ?: '-';
    $type   = $rideType      ?: '-';
    $fare   = ($fareEur !== '' && $fareEur !== null) ? ('EUR ' . $fareEur) : '-';

    return "Hi {$name},\n\n"
        . "Your PowerCabs ride has been confirmed.\n\n"
        . "TRIP DETAILS\n"
        . "------------\n"
        . "Pick-up:  {$pickup}\n"
        . "Drop-off: {$dest}\n"
        . "Service:  {$type}\n"
        . "Fare:     {$fare}\n\n"
        . "Please be ready at your pickup location 5 minutes before the scheduled time.\n"
        . "The fare shown is an estimate; tolls, waiting time and route changes may affect the final amount.\n\n"
        . "Questions? Call +353 1 500 0000 or email support@powercabs.ie.\n\n"
        . "Thank you for choosing PowerCabs.\n"
        . "-- PowerCabs Ireland";
}

/**
 * Build HTML body for ride-assigned email.
 */
function buildRideAssignedBody($passengerName, $pickupAddr, $destAddr, $rideType, $fareEur, $templateDir = null) {
    $dir = $templateDir !== null ? $templateDir : (dirname(__DIR__) . '/templates');
    $path = rtrim($dir, '/\\') . '/email_ride_assigned.html';
    if (is_file($path)) {
        $html = file_get_contents($path);
        $html = str_replace(
            '[PASSENGER_NAME]',
            htmlspecialchars($passengerName ?: 'Passenger'),
            $html
        );
        $html = str_replace(
            '[PICKUP_ADDRESS]',
            htmlspecialchars($pickupAddr ?: '—'),
            $html
        );
        $html = str_replace(
            '[DEST_ADDRESS]',
            htmlspecialchars($destAddr ?: '—'),
            $html
        );
        $html = str_replace(
            '[RIDE_TYPE]',
            htmlspecialchars($rideType ?: '—'),
            $html
        );
        $html = str_replace(
            '[FARE_EUR]',
            htmlspecialchars($fareEur !== '' && $fareEur !== null ? '€' . $fareEur : '—'),
            $html
        );
        return $html;
    }
    // Fallback inline HTML
    $name = htmlspecialchars($passengerName ?: 'Passenger');
    $pickup = htmlspecialchars($pickupAddr ?: '—');
    $dest = htmlspecialchars($destAddr ?: '—');
    $type = htmlspecialchars($rideType ?: '—');
    $fare = ($fareEur !== '' && $fareEur !== null) ? '€' . htmlspecialchars($fareEur) : '—';
    return "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>"
        . "<p>Hello {$name},</p>"
        . "<p>Your PowerCabs ride has been assigned.</p>"
        . "<p><strong>Pick-up:</strong> {$pickup}<br><strong>Drop-off:</strong> {$dest}<br>"
        . "<strong>Service:</strong> {$type}<br><strong>Fare:</strong> {$fare}</p>"
        . "<p>Thank you for choosing PowerCabs.</p></body></html>";
}
