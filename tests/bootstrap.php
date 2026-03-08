<?php
/**
 * PHPUnit bootstrap file.
 *
 * Sets environment variables so that functions.php -> connect.php
 * uses the test database, then loads application functions.
 */

// Ensure env vars are set for the test database (phpunit.xml provides these)
// Set them before loading any application code so connect.php picks them up.
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'test';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'zaikokanri_test';

// Load application code (functions.php -> connect.php -> sets up $con)
require_once __DIR__ . '/../functions.php';

global $con;
if (!$con || $con->connect_error) {
    $error = $con ? $con->connect_error : 'Unknown error (connection is null)';
    die("Test database connection failed: " . $error . "\n"
        . "Make sure the test database is running and credentials in phpunit.xml are correct.\n");
}
