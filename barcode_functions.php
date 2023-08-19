<?php
include('connect.php');

function logError($errorMessage, $query) {
    global $con;

    $stmt = $con->prepare("INSERT INTO error_log (error_message, query) VALUES (?, ?)");
    $stmt->bind_param("ss", $errorMessage, $query);
    if (!$stmt->execute()) {
        // Log the error to PHP error log if the database logging fails.
        error_log("Failed to log error to database: " . mysqli_error($con));
    }
}

$action = $_POST['action'];
$id = $_POST['id'] ?? null;
$barcode = $_POST['barcode'];
$article_id = $_POST['article_id'];
$destock_count = $_POST['destock_count'];
$is_prompt = intval($_POST['is_prompt']);

switch ($action) {
    case 'update':
        $sql = "UPDATE barcode_list SET barcode = ?, article_id = ?, destock_count = ?, is_prompt = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("siiii", $barcode, $article_id, $destock_count, $is_prompt, $id);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        }
        break;
    case 'delete':
        $sql = "DELETE FROM barcode_list WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        }
        break;
    case 'add':
        $sql = "INSERT INTO barcode_list (barcode, article_id, destock_count, is_prompt) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("siii", $barcode, $article_id, $destock_count, $is_prompt);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        } else {
            // After adding the new barcode, you may want to return its ID to the client
            echo json_encode(['barcode' => $barcode]);
        }
        break;
}

$con->close();
?>