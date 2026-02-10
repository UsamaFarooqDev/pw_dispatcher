<?php
session_start();

if (empty($_SESSION['user']) || empty($_SESSION['access_token'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please log in.',
        'data' => []
    ], JSON_PRETTY_PRINT);
    exit;
}
?>

