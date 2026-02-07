<?php
require_once('functions.php');

$action = $_POST['action'];
$category_id = $_POST['category_id'] ?? null;
$category_name = $_POST['category_name'];

switch ($action) {
    case 'update':
        $sql = "UPDATE category SET category_name = ? WHERE category_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $category_name, $category_id);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        }
        break;
    case 'delete':
        $sql = "DELETE FROM category WHERE category_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $category_id);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        }
        break;
    case 'add':
        $sql = "INSERT INTO category (category_name) VALUES (?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $category_name);
        if (!$stmt->execute()) {
            logError($stmt->error, $sql);
        } else {
            $last_id = $con->insert_id;
            echo json_encode(['category_id' => $last_id]);
        }
        break;
}

$con->close();
?>
