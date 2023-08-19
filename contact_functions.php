<?php
include('connect.php');  // Include your database connection script

function addContact($name, $email) {
    global $con;

    // Sanitize and validate data before inserting into database

    $stmt = $con->prepare("INSERT INTO contact (contact_id, `name`, email) VALUES (NULL, ?, ?)");
    $stmt->bind_param("ss", $name, $email);

    if ($stmt->execute()) {
        return $con->insert_id;  // Return the ID of the inserted row
    } else {
        return false;
    }
}

function updateContact($contact_id, $name, $email) {
    global $con;

    // Sanitize and validate data before updating in the database

    $stmt = $con->prepare("UPDATE contact SET `name` = ?, email = ? WHERE contact_id = ?");
    $stmt->bind_param("ssi", $name, $email, $contact_id);
    

    if ($stmt->execute()) {
        return true;
    } else {
        echo "Error updating contact: " . $stmt->error;
        return false;
    }
}

function deleteContact($contact_id) {
    global $con;

    // Sanitize and validate data before deleting from the database

    $stmt = $con->prepare("DELETE FROM contact WHERE contact_id = ?");
    $stmt->bind_param("i", $contact_id);

    return $stmt->execute();
}

// Check action parameter sent from AJAX
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $name = $_POST['name'];
            $email = $_POST['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);  // Bad Request
                exit;
            }
            $contact_id = addContact($name, $email);

            if ($contact_id !== false) {
                // Send back the inserted ID
                echo json_encode(['contact_id' => $contact_id]);
            } else {
                http_response_code(500);  // Internal Server Error
            }
            break;

        case 'update':
            $contact_id = $_POST['contact_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);  // Bad Request
                exit;
            }
            if (!updateContact($contact_id, $name, $email)) {
                http_response_code(500);  // Internal Server Error
            }
            break;

        case 'delete':
            $contact_id = $_POST['contact_id'];

            if (!deleteContact($contact_id)) {
                http_response_code(500);  // Internal Server Error
            }
            break;
    }
}
?>
