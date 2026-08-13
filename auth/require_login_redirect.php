<?php
/**
 * Use after session_start() on protected pages.
 * Redirects to / when session is invalid.
 */
if (empty($_SESSION['admin_id'])) {
    header('Location: /');
    exit;
}
require_once __DIR__ . '/config.php';
