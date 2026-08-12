<?php
/**
 * Use after session_start() on protected pages.
 * Redirects to / when session is invalid (no user or access_token).
 */
if (empty($_SESSION['admin_id'])) {
    header('Location: /');
    exit;
}
