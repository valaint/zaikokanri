<?php
// api_handler.php
require_once('log_functions.php');

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get the SQL query from the request
$sql_query = $_POST['sql_query'] ?? '';

// Initialize a response array
$response = array();

// Prepare the data to log the request
$log_request_data = array(
    'method' => $_SERVER['REQUEST_METHOD'],
    'url' => $_SERVER['REQUEST_URI'],
    'headers' => json_encode(getallheaders()),
    'body' => json_encode($_POST)
);

// Log the request
$request_id = log_api_request($log_request_data);

// Security: Only allow read-only SELECT queries
$trimmed = trim($sql_query);
if (!preg_match('/^\s*SELECT\s/i', $trimmed)) {
    http_response_code(403);
    $response = array("error" => "Only SELECT queries are allowed");

    $log_exception_data = array(
        'uuid' => $request_id,
        'exception_message' => "Blocked non-SELECT query: " . substr($trimmed, 0, 100),
        'exception_trace' => ''
    );
    log_api_exception($log_exception_data);

    echo json_encode($response);
    exit;
}

// Block dangerous keywords even within SELECT queries
$blocked_patterns = [
    '/\bINTO\s+OUTFILE\b/i',
    '/\bINTO\s+DUMPFILE\b/i',
    '/\bLOAD_FILE\b/i',
    '/\bBENCHMARK\b/i',
    '/\bSLEEP\b/i',
    '/;\s*(DROP|DELETE|UPDATE|INSERT|ALTER|CREATE|TRUNCATE|EXEC)/i',
];
foreach ($blocked_patterns as $pattern) {
    if (preg_match($pattern, $trimmed)) {
        http_response_code(403);
        $response = array("error" => "Query contains blocked keywords");

        $log_exception_data = array(
            'uuid' => $request_id,
            'exception_message' => "Blocked dangerous query pattern: " . substr($trimmed, 0, 100),
            'exception_trace' => ''
        );
        log_api_exception($log_exception_data);

        echo json_encode($response);
        exit;
    }
}

try {
    $result = mysqli_query($con, $sql_query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        $log_response_data = array(
            'uuid' => $request_id,
            'status_code' => http_response_code(),
            'headers' => json_encode(headers_list()),
            'body' => json_encode($response)
        );

        log_api_response($log_response_data);
    } else {
        $log_exception_data = array(
            'uuid' => $request_id,
            'exception_message' => mysqli_error($con),
            'exception_trace' => ''
        );

        log_api_exception($log_exception_data);

        http_response_code(500);
        $response = array("error" => "Query execution failed");
    }
} catch (Exception $e) {
    $log_exception_data = array(
        'uuid' => $request_id,
        'exception_message' => $e->getMessage(),
        'exception_trace' => $e->getTraceAsString()
    );

    log_api_exception($log_exception_data);

    http_response_code(500);
    $response = array("error" => "Query execution failed");
}

echo json_encode($response);
