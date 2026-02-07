<?php
/**
 * Health check endpoint.
 * Returns JSON with application and database status.
 */
header('Content-Type: application/json');

$status = ['status' => 'ok', 'checks' => []];
$httpCode = 200;

// Check database connectivity
try {
    require_once __DIR__ . '/connect.php';
    if ($con->connect_error) {
        throw new Exception($con->connect_error);
    }
    $con->query("SELECT 1");
    $status['checks']['database'] = 'ok';
} catch (Exception $e) {
    $status['status'] = 'error';
    $status['checks']['database'] = 'failed';
    $httpCode = 503;
}

// Check PHP version
$status['checks']['php_version'] = PHP_VERSION;

// Check required extensions
$requiredExtensions = ['mysqli', 'gd'];
foreach ($requiredExtensions as $ext) {
    $status['checks']['ext_' . $ext] = extension_loaded($ext) ? 'ok' : 'missing';
    if (!extension_loaded($ext)) {
        $status['status'] = 'degraded';
    }
}

http_response_code($httpCode);
echo json_encode($status, JSON_PRETTY_PRINT);
