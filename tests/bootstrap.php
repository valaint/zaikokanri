<?php
/**
 * PHPUnit bootstrap file.
 *
 * Sets up the test database connection and loads application functions.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set up test database connection using phpunit.xml env vars
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? 'test';
$dbname = $_ENV['DB_NAME'] ?? 'zaikokanri_test';

$con = new mysqli($host, $user, $password, $dbname);

if ($con->connect_error) {
    die("Test database connection failed: " . $con->connect_error . "\n"
        . "Make sure the test database is running and credentials in phpunit.xml are correct.\n");
}

// Load application functions (skipping connect.php since we already have $con)
require_once __DIR__ . '/../functions.php';
