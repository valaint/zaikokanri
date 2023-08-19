<?php
require_once('connect.php');
require_once('functions.php');

// Get the input data from the JSON body of the request
$input = json_decode(file_get_contents('php://input'), true);

$response = [];

if (isset($input['barcodetext'])) {
    $barcode = $input['barcodetext'];

    $stmt = $con->prepare("SELECT bl.article_id, bl.destock_count, ai.article_name 
                           FROM barcode_list bl 
                           JOIN article_info ai ON bl.article_id = ai.article_id
                           WHERE bl.barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($stmt->errno) {
        // If there was a MySQL error, add it to the response
        $response[] = ['error' => "MySQL error ($stmt->errno): $stmt->error"];
    } else {
        if ($result->num_rows === 0) {
            // If the result is empty, add an error message to the response
            $response[] = ['error' => "物品リストにありません $barcode"];
        } else {
            while ($row = $result->fetch_row()) {
                if (handleStock2('destock', null, $row[0], $row[1],1)) {
                    // Add each destocked article, its count and name to the response
                    $response[] = ['article_name' => $row[2], 'count' => $row[1]];
                } else {
                    // handleStock returned false, add error message to response
                    $response[] = ['error' => "出庫失敗しました ".$row[2]];
                }
            }
        }
    }
    $result->free();

    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(["error" => "No barcode provided"]);
}
?>