<?php
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $article_id = $_GET['id'];

    // Prepared statement to protect against SQL injection
    $stmt = $con->prepare("DELETE FROM article_info WHERE article_id = ?");
    $stmt->bind_param("i", $article_id);  // 'i' denotes integer

    if ($stmt->execute()) {
        // Redirect to the previous page or a specific location after successful deletion
        header("Location: admin_stock.php");
        exit();
    } else {
        echo "削除失敗しました: " . $stmt->error;
    }

    $stmt->close();
}

$con->close();
?>