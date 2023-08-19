<?php
require_once('functions.php');
if (isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['type'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $type = $_GET['type'];
    $group_by_article = isset($_GET['group_by_article']);
    $type_query = "";

    if ($type == "入庫") {
        $type_query = "AND type = '入庫'";
    } else if ($type == "出庫") {
        $type_query = "AND type = '出庫'";
    }
$start_date_obj = DateTime::createFromFormat('m/d/Y', $start_date);
$end_date_obj = DateTime::createFromFormat('m/d/Y', $end_date);

// Convert to strings in 'Y-m-d' format
$start_date_mysql = $start_date_obj->format('Y-m-d');
$end_date_mysql = $end_date_obj->format('Y-m-d');
    $query = "";
    if ($group_by_article) {
        $query = "SELECT article_id, (SELECT article_name from article_info WHERE article_info.article_id=history.article_id) AS article_name, SUM(changed_value) AS total_changed_value, type 
                               FROM history 
                               WHERE `time` BETWEEN ? AND ? $type_query
                               GROUP BY article_id, type
                               ORDER by `time` DESC";
    } else {
        $query = "SELECT time, (SELECT article_name from article_info WHERE article_info.article_id=history.article_id) AS article_name, changed_value, type 
                               FROM history 
                               WHERE `time` BETWEEN ? AND ? $type_query
                               ORDER by `time` DESC";
    }


    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date_mysql, $end_date_mysql);
    if (!$stmt->execute()) {
        logError(mysqli_error($con), $query);
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {

        echo "{$row['time']} {$row['article_name']} {$row['changed_value']}個{$row['type']}されました。<br>";
    }
}
?>
