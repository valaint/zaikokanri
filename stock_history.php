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
    } elseif ($type == "出庫") {
        $type_query = "AND type = '出庫'";
    }
    $start_date_obj = DateTime::createFromFormat('m/d/Y', $start_date);
    $end_date_obj = DateTime::createFromFormat('m/d/Y', $end_date);

// Convert to strings in 'Y-m-d' format
    $start_date_mysql = $start_date_obj->format('Y-m-d');
    $end_date_mysql = $end_date_obj->format('Y-m-d');
    $query = "";
    if ($group_by_article) {
        $query = "SELECT h.article_id, a.article_name, SUM(h.changed_value) AS total_changed_value, h.type
                               FROM history h
                               LEFT JOIN article_info a ON a.article_id = h.article_id
                               WHERE h.`time` BETWEEN ? AND ? $type_query
                               GROUP BY h.article_id, h.type
                               ORDER by h.`time` DESC";
    } else {
        $query = "SELECT h.time, a.article_name, h.changed_value, h.type
                               FROM history h
                               LEFT JOIN article_info a ON a.article_id = h.article_id
                               WHERE h.`time` BETWEEN ? AND ? $type_query
                               ORDER by h.`time` DESC";
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
