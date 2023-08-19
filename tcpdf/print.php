<?php
require_once('functions.php');
require_once('phpqrcode/qrlib.php'); // assuming you have PHP QR Code installed
require_once('tcpdf/tcpdf.php'); // assuming you have TCPDF installed

// Fetch all barcodes
$stmt = $con->prepare("SELECT bl.barcode, bl.destock_count, ai.article_name 
                       FROM barcode_list bl
                       JOIN article_info ai ON bl.article_id = ai.article_id");
$stmt->execute();
$result = $stmt->get_result();

$barcodes = array();

while ($row = $result->fetch_row()) {
    $barcodes[] = array(
        'barcode' => $row[0],
        'destock_count' => $row[1],
        'article_name' => $row[2]
    );
}

// Generate QR codes for each barcode
foreach ($barcodes as &$barcode) {
    $qrData = $barcode['barcode'];
    $qrFile = 'qrcodes/' . $qrData . '.png';
    QRcode::png($qrData, $qrFile, 'L', 4, 2);
    $barcode['qr_file'] = $qrFile;
}

// Handle PDF generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new TCPDF object
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('dejavusans', '', 10);

    // Create table headers
    $table = '<table><tr><th>Barcode</th><th>QR Code</th><th>Article Name</th><th>Destock Count</th></tr>';

    // Create table rows
    foreach ($barcodes as $barcode) {
        $table .= '<tr>';
        $table .= '<td>' . $barcode['barcode'] . '</td>';
        $table .= '<td><img src="' . $barcode['qr_file'] . '"></td>';
        $table .= '<td>' . $barcode['article_name'] . '</td>';
        $table .= '<td>' . $barcode['destock_count'] . '</td>';
        $table .= '</tr>';
    }

    $table .= '</table>';

    // Write the table to the PDF
    $pdf->writeHTML($table, true, false, true, false, '');

    // Output the PDF
    $pdf->Output('barcodes.pdf', 'D');

    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Barcodes</title>
    <link href="src/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Barcodes</h1>
    <form method="post">
        <table>
            <tr>
                <th>Barcode</th>
                <th>QR Code</th>
                <th>Article Name</th>
                <th>Destock Count</th>
            </tr>
            <?php foreach ($barcodes as $barcode): ?>
                <tr>
                    <td><?= htmlspecialchars($barcode['barcode']) ?></td>
                    <td><img src="<?= htmlspecialchars($barcode['qr_file']) ?>"></td>
                    <td><?= htmlspecialchars($barcode['article_name']) ?></td>
                    <td><?= htmlspecialchars($barcode['destock_count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit" class="btn btn-primary">Generate PDF</button>
    </form>
</div>
</body>
</html>
