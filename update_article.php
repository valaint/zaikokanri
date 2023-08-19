<?php
require_once('functions.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the posted data
$data = $_POST['data'];

// Loop through each article_id
foreach ($data as $article_id => $values) {
    $contact_id = $values['contact_id'];
    $stock = $values['stock'];
    $threshold = $values['threshold'];

    // Prepare the update statement
    $stmt = $con->prepare("UPDATE article_info SET contact_id1 = ?, stock = ?, threshold = ? WHERE article_id = ?");

    // Bind the parameters
    $stmt->bind_param("iiii", $contact_id, $stock, $threshold, $article_id);

    // Execute the statement
    if (!$stmt->execute()) {
        die("Error executing update: (" . $stmt->errno . ") " . $stmt->error);
    }
}

// Redirect back to the admin page
header("Location: admin.php");
?>
