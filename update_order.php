<?php
require_once('functions.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $data = json_decode(file_get_contents('php://input'), true);

    // Prepare the SQL statement
    $stmt = $con->prepare("UPDATE article_info SET article_order = ? WHERE article_id = ?");

    // Begin a transaction
    $con->begin_transaction();

    try {
        // Loop through each item in the data
        foreach ($data as $article_id => $article_order) {
            // Bind the parameters
            $stmt->bind_param("ii", $article_order, $article_id);

            // Execute the statement
            $stmt->execute();
        }

        // If we made it this far, there were no errors, so commit the transaction
        $con->commit();
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $con->rollback();

        // Re-throw the exception
        throw $e;
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>