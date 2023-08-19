<?php
require_once('../connect.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function log_to_file($filename, $data) {
    $fp = fopen($filename, 'a');
    fwrite($fp, json_encode($data)."\n");
    fclose($fp);
}

function log_from_file_to_db($filename) {
    global $con;
    if (!file_exists($filename) || !is_readable($filename)) {
        return;
    }
    $contents = file($filename);
    foreach($contents as $line) {
        $log_data = json_decode($line, true);
        $log_type = $log_data['log_type'];
        $log_function = 'log_'.$log_type;
        if ($log_function($log_data, true) === false) {
            break;
        }
    }
    // If all lines have been processed and logged, delete the file
    if (!isset($log_data)) {
        unlink($filename);
    } else {
        // If there was a break, re-write the remaining lines back to the file
        $remaining_lines = array_slice($contents, array_search($line, $contents));
        file_put_contents($filename, $remaining_lines);
    }
}

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function log_api_request($log_data, $from_file = false) {
    global $con;

    // Try to log the data from the file first
    if (!$from_file) {
        log_from_file_to_db('request_log.txt');
    }

    // Check if a UUID was provided (when logging from a file)
    if (isset($log_data['uuid'])) {
        $uuid = $log_data['uuid'];
    } else {
        $uuid = generate_uuid();
    }

    // Prepare and execute the SQL statement
    $stmt = $con->prepare("INSERT INTO api_requests (uuid, method, url, headers, body) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $uuid, $log_data['method'], $log_data['url'], $log_data['headers'], $log_data['body']);

    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        if (!$from_file) {
            $log_data['uuid'] = $uuid;
            $log_data['log_type'] = 'api_request';
            log_to_file('request_log.txt', $log_data);
        }
        return $uuid;
    }
}

function log_api_response($log_data, $from_file = false) {
    global $con;

    // Try to log the data from the file first
    if (!$from_file) {
        log_from_file_to_db('response_log.txt');
    }

    // Prepare and execute the SQL statement
    $stmt = $con->prepare("INSERT INTO api_responses (request_uuid, status_code, headers, body) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $log_data['uuid'], $log_data['status_code'], $log_data['headers'], $log_data['body']);

    if (!$stmt->execute() && !$from_file) {
        $log_data['log_type'] = 'api_response';
        log_to_file('response_log.txt', $log_data);
    }
}

function log_api_exception($log_data, $from_file = false) {
    global $con;

    // Try to log the data from the file first
    if (!$from_file) {
        log_from_file_to_db('exception_log.txt');
    }

    // Prepare and execute the SQL statement
    $stmt = $con->prepare("INSERT INTO api_exceptions (request_uuid, exception_message, exception_trace) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $log_data['uuid'], $log_data['exception_message'], $log_data['exception_trace']);

    if (!$stmt->execute() && !$from_file) {
        $log_data['log_type'] = 'api_exception';
        log_to_file('exception_log.txt', $log_data);
    }
}
?>



