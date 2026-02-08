<?php

/**
 * PHPUnit bootstrap file.
 *
 * Uses an in-memory SQLite database via a MySQLi-compatible wrapper,
 * so tests run without a MySQL server.
 */

require_once __DIR__ . '/SqliteConnection.php';

// Create in-memory SQLite connection as global $con
// Use $GLOBALS explicitly because PHPUnit loads bootstrap in a method scope
$GLOBALS['con'] = new SqliteConnection(':memory:');

if ($GLOBALS['con']->connect_error) {
    die("SQLite connection failed: " . $GLOBALS['con']->connect_error . "\n");
}

// Prevent connect.php from overwriting $con with a MySQL connection
define('TESTING_WITH_SQLITE', true);

// Load application functions (functions.php -> connect.php, which returns early)
require_once __DIR__ . '/../functions.php';
