<?php
require_once('connect.php');

function logError($errorMessage, $query)
{
    global $con;

    $stmt = $con->prepare("INSERT INTO error_log (error_message, query) VALUES (?, ?)");
    $stmt->bind_param("ss", $errorMessage, $query);
    if (!$stmt->execute()) {
        error_log("Failed to log error to database: " . mysqli_error($con));
    }
}

/**
 * Handle stock operations (restock/destock) for one or many articles.
 *
 * @param string     $operation    'restock' or 'destock'
 * @param array|null $stockChange  Associative array of article_id => change value
 * @param int|null   $article_id   Single article ID (used when $stockChange is null)
 * @param int|null   $value        Change amount (used when $stockChange is null)
 * @param int        $from_barcode 1 if triggered by barcode scan, 0 otherwise
 * @param bool       $play_audio   Whether to output audio feedback HTML
 * @return int 1 on success, 0 if nothing was processed
 */
function handleStock($operation, $stockChange = null, $article_id = null, $value = null, $from_barcode = 0, $play_audio = true)
{
    $flag = 0;
    if ($stockChange != null) {
        foreach ($stockChange as $key => $value) {
            if ($value and !($value == 0)) {
                $article_id = intval($key);
                $value = intval($value);
                $flag = updateStock($operation, $article_id, $value, $from_barcode);
            }
        }
    } elseif ($article_id != null && $value != null) {
        $flag = updateStock($operation, $article_id, $value, $from_barcode);
    }

    if ($flag == 1 && $play_audio) {
        $audioSrc = $operation === 'restock' ? '入庫しました.wav' : '出庫しました.wav';
        echo "<audio controls autoplay hidden><source src={$audioSrc}></audio>";
    }

    return $flag;
}

function updateStock($operation, $article_id, $value, $fromBarcode = 0)
{
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

    $type = $operation === 'restock' ? '入庫' : '出庫';

    $stmt = $con->prepare("INSERT INTO history (article_id, type, original_value, updated_value, from_barcode) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiii", $article_id, $type, $original_stock, $updated, $fromBarcode);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), "INSERT INTO history (article_id, type, original_value, updated_value, from_barcode) VALUES ($article_id, $type, $original_stock, $updated, $fromBarcode)");
    }

    $stmt = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
    $stmt->bind_param("ii", $updated, $article_id);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), "UPDATE article_info SET stock = $updated WHERE article_id = $article_id");
    }

    return 1;
}

/**
 * Get all categories
 *
 * @return array
 */
function getCategories()
{
    global $con;
    $categories = [];
    $stmt = $con->prepare("SELECT category_name FROM category");
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category_name'];
        }
        $result->free();
    }
    return $categories;
}

/**
 * Get full inventory
 *
 * @return array
 */
function getInventory()
{
    global $con;
    $inventory = [];
    $sql = "SELECT (SELECT category_name from category"
        . " WHERE article_info.category_id=category.category_id),"
        . "article_name,stock,article_id"
        . " from article_info ORDER BY category_id,article_order";
    $stmt = $con->prepare($sql);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_row()) {
            $inventory[] = $row;
        }
        $result->free();
    }
    return $inventory;
}

/**
 * Get recent history
 *
 * @param int $limit
 * @return array
 */
function getRecentHistory($limit = 30)
{
    global $con;
    $history = [];
    $sql = "SELECT time,"
        . "(SELECT article_name from article_info"
        . " WHERE article_info.article_id=history.article_id),"
        . "changed_value,type from history ORDER by `time` desc LIMIT ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $limit);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_row()) {
            $history[] = $row;
        }
        $result->free();
    }
    return $history;
}
