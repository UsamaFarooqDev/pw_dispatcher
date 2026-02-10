<?php
// auth/session.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!empty($_SESSION['user']) && !empty($_SESSION['access_token'])) {
    echo json_encode(['loggedIn' => true, 'user' => $_SESSION['user']]);
} else {
    echo json_encode(['loggedIn' => false]);
}
