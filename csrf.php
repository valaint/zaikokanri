<?php
/**
 * CSRF token generation and validation.
 *
 * Usage:
 *   Generate a hidden input:  <?= csrfField() ?>
 *   Validate on POST:         validateCsrfToken()
 */

function generateCsrfToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField()
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function validateCsrfToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('CSRF token validation failed.');
    }
    return true;
}
