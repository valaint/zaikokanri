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

    if (isset($_GET['api'])) {
        header('Content-Type: application/json');
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="report' . $start_date . '_' . $end_date . '.csv"');

        // Open the output stream
        $fh = fopen('php://output', 'w');

        // Output BOM for UTF-8
        fputs($fh, "\xEF\xBB\xBF");

        // Start output buffering (to capture stream contents)
        ob_start();

        if ($group_by_article) {
            fputcsv($fh, ['Article ID', 'Article Name', 'Total Changed Value', 'Type']);
        } else {
            fputcsv($fh, ['Time', 'Article Name', 'Changed Value', 'Type']);
        }

        while ($row = $result->fetch_assoc()) {
            if ($group_by_article) {
                fputcsv($fh, [$row['article_id'], $row['article_name'], $row['total_changed_value'], $row['type']]);
            } else {
                fputcsv($fh, [$row['time'], $row['article_name'], $row['changed_value'], $row['type']]);
            }
        }

        // Get the contents of the output buffer
        $string = ob_get_clean();

        echo $string;
    }
    exit();
}
