<?php
// auth/session.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!empty($_SESSION['admin_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'user' => [
            'id'    => $_SESSION['admin_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'name'  => $_SESSION['user_name']  ?? '',
        ],
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
