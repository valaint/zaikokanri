<?php
// api_handler.php
require_once('log_functions.php');

// Get the SQL query from the request
$sql_query = $_POST['sql_query'];

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

try {
    // Run the SQL query
    $result = mysqli_query($con, $sql_query);

    if ($result) {
        // Fetch all results
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        // Prepare the data to log the response
        $log_response_data = array(
            'uuid' => $request_id,
            'status_code' => http_response_code(),
            'headers' => json_encode(headers_list()),
            'body' => json_encode($response)
        );
        
        // Log the successful response
        log_api_response($log_response_data);
    } else {
        // Prepare the data to log the exception
        $log_exception_data = array(
            'uuid' => $request_id,
            'exception_message' => mysqli_error($con),
            'exception_trace' => ''
        );

        // Log any SQL errors
        log_api_exception($log_exception_data);

        http_response_code(500);
        $response = array("error" => "Error executing query: " . mysqli_error($con));
    }
} catch (Exception $e) {
    // Prepare the data to log the exception
    $log_exception_data = array(
        'uuid' => $request_id,
        'exception_message' => $e->getMessage(),
        'exception_trace' => $e->getTraceAsString()
    );

    // Log any exceptions
    log_api_exception($log_exception_data);

    http_response_code(500);
    $response = array("error" => "Exception: " . $e->getMessage());
}

// Send the response
echo json_encode($response);
?>
