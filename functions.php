<?php
require_once('connect.php');
$dbname = 'eeismzak';
function logError($errorMessage, $query) {
    global $con;

    $stmt = $con->prepare("INSERT INTO error_log (error_message, query) VALUES (?, ?)");
    $stmt->bind_param("ss", $errorMessage, $query);
    if (!$stmt->execute()) {
        // Log the error to PHP error log if the database logging fails.
        error_log("Failed to log error to database: " . mysqli_error($con));
    }
}

function handleStock($operation, $stockChange = null, $article_id = null, $value = null) {
    $flag = 0;
    if ($stockChange != null) {
        foreach($stockChange as $key => $value){
            if($value and !($value==0)){
                $article_id = intval($key);
                $value = intval($value);
                $flag = updateStock($operation, $article_id, $value);
            }
        }
    } else if ($article_id != null && $value != null) {
        $flag = updateStock($operation, $article_id, $value);
    }

    if ($flag == 1) {
        $audioSrc = $operation === 'restock' ? '入庫しました.wav' : '出庫しました.wav';
        echo "<audio controls autoplay hidden><source src={$audioSrc}></audio>";
    }
}

function handleStock2($operation, $stockChange = null, $article_id = null, $value = null, $from_barcode = 0) {
    $flag = 0;
    if ($stockChange != null) {
        foreach($stockChange as $key => $value){
            if($value and !($value==0)){
                $article_id = intval($key);
                $value = intval($value);
                $flag = updateStock($operation, $article_id, $value, $from_barcode);
            }
        }
    } else if ($article_id != null && $value != null) {
        $flag = updateStock($operation, $article_id, $value, $from_barcode);
    }

    return $flag;
}



function updateStock($operation, $article_id, $value, $fromBarcode = 0) {
    global $con;
    $stmt = $con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
    $stmt->bind_param("i", $article_id);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), "SELECT stock FROM article_info WHERE article_id = ?");
    }
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $original_stock = $row[0];

    $updated = $operation === 'restock' ? $original_stock + $value : $original_stock - $value;

    // Set the type based on the operation
    $type = $operation === 'restock' ? '入庫' : '出庫';

    // Insert the operation into history with from_barcode information
    $stmt = $con->prepare("INSERT INTO history (article_id, type, original_value, updated_value, from_barcode) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiii", $article_id, $type, $original_stock, $updated, $fromBarcode);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), "INSERT INTO history (article_id, type, original_value, updated_value, from_barcode) VALUES ($article_id, $type, $original_stock, $updated, $fromBarcode)");
    }


    // Update the article's stock
    $stmt = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
    $stmt->bind_param("ii", $updated, $article_id);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), "UPDATE article_info SET stock = $updated WHERE article_id = $article_id");
    }

    return 1;
}