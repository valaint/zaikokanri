<?php
// api_handler.php

require_once('../connect.php');
require_once('log_functions.php');

// This part can be extended to validate user's credentials
// or apply any other necessary security measures

// Get the SQL query from the request
$sql_query = $_POST['sql_query'];

// Initialize a response array
$response = array();

// Log the request
$request_id = log_api_request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], json_encode(getallheaders()), json_encode($_POST));

try {
    // Run the SQL query
    $result = mysqli_query($con, $sql_query);

    if ($result) {
        // Fetch all results
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        // Log the successful response
        log_api_response($request_id, http_response_code(), json_encode(headers_list()), json_encode($response));
    } else {
        // Log any SQL errors
        log_api_exception($request_id, mysqli_error($con), '');

        http_response_code(500);
        $response = array("error" => "Error executing query: " . mysqli_error($con));
    }
} catch (Exception $e) {
    // Log any exceptions
    log_api_exception($request_id, $e->getMessage(), $e->getTraceAsString());

    http_response_code(500);
    $response = array("error" => "Exception: " . $e->getMessage());
}

// Send the response
echo json_encode($response);
?>
