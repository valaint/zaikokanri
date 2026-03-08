<?php
// Load environment variables from .env if available (via Composer autoload)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'eeismzak';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'zaikokanrimysql';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'eeismzak';

try {
    $con = new mysqli($host, $user, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
