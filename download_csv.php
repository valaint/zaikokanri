<?php
session_start();

if (isset($_SESSION['csv_data'])) {
    $csv_data = $_SESSION['csv_data'];
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $encoding = isset($_GET['encoding']) ? $_GET['encoding'] : 'utf-8';  // Set default encoding to UTF-8

    // Open output stream
    $output = fopen('php://output', 'w');
    if ($output === FALSE) {
        die("Failed to open php://output");
    }

    // Set headers to force download
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="stock_'.$date.'.csv"');
    header('Content-Type: text/csv; charset='.$encoding);
    header('Content-Transfer-Encoding: binary');

    if ($encoding === 'utf-8') {
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
    }

    // Output all the data
    foreach ($csv_data as $row) {
        if ($encoding === 'shift-jis') {
            $row = array_map(function($field) {
                return mb_convert_encoding($field, 'Shift_JIS', 'UTF-8');
            }, $row);
        }

        // Open a temporary stream in memory
        $temp = fopen('php://temp', 'r+');

        // Write the row to the temporary stream
        fputcsv($temp, $row);

        // Go back to the start of the temporary stream
        rewind($temp);

        // Fetch the row data as a CSV string
        $csv = fgets($temp);

        // Replace LF with CRLF
        $csv = str_replace("\n", "\r\n", $csv);

        // Write the modified CSV string to the output
        fwrite($output, $csv);

        // Close the temporary stream
        fclose($temp);
    }
    
    // Close the stream
    fclose($output);
    exit();
} else {
    die("Error: Data not found.");
}
?>