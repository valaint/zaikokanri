<?php
require_once('functions.php');
require_once('phpqrcode/qrlib.php'); // assuming you have PHP QR Code installed
require_once('tcpdf/tcpdf.php'); // assuming you have TCPDF installed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all barcodes
$stmt = $con->prepare("SELECT bl.barcode, GROUP_CONCAT(ai.article_name) AS article_names, GROUP_CONCAT(bl.destock_count) AS destock_counts
                       FROM barcode_list bl
                       JOIN article_info ai ON bl.article_id = ai.article_id
                       GROUP BY bl.barcode");
$stmt->execute();
$result = $stmt->get_result();

$barcodes = array();

while ($row = $result->fetch_row()) {
    $barcodes[] = array(
        'barcode' => $row[0],
        'article_name' => $row[1],
        'destock_count' => $row[2]
    );
}

// Generate QR codes for each barcode
foreach ($barcodes as &$barcode) {
    $qrData = $barcode['barcode'];
    $fileName = str_replace('/', '_', $qrData);
    $qrFile = 'qrcodes/' . $fileName . '.png';
    QRcode::png($qrData, $qrFile, 'L', 4, 2);
    $barcode['qr_file'] = $qrFile;
}


// Handle PDF generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new TCPDF object
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->AddFont('kozminproregular', '', 'kozminproregular.php');
    $pdf->SetFont('kozminproregular', '', 12, '', false);
    // Create table headers
    $table = '<table style="text-align:center;vertical-align:middle;"><tr><th>Barcode</th><th>QR Code</th><th>Article Name</th><th>Destock Count</th></tr>';
    $rowNum = 0;
    foreach ($barcodes as $barcode) {
        $article_names = explode(',', $barcode['article_name']);
        $destock_counts = explode(',', $barcode['destock_count']);
        for ($i = 0; $i < count($article_names); $i++){
            $table .= '<tr>';
            if ($rowNum % 2 == 0) {
                if ($i == 0){
                    $table .= '<td style="vertical-align:middle;" rowspan="'.count($article_names).'"><div style="text-align:center;">'. htmlspecialchars($barcode['barcode']) . '</div></td>';
                    $table .= '<td style="vertical-align:middle;" rowspan="'.count($article_names).'"><div style="text-align:center;"><img src="' . $barcode['qr_file'] . '" width="60" height="60"></div></td>';
                }
                $table .= '<td style="vertical-align:middle;"><div style="text-align:center;">' . htmlspecialchars($article_names[$i]) . '</div></td>';
                $table .= '<td style="vertical-align:middle;"><div style="text-align:center;">' . htmlspecialchars($destock_counts[$i]) . '</div></td>';
            } else {
                // QR code in the first column
                if ($i == 0) {
                    $table .= '<td style="vertical-align:middle;" rowspan=' . count($article_names) . '><div style="text-align:center;"><img src="' . $barcode['qr_file'] . '" width="60" height="60"></div></td>';
                    $table .= '<td style="vertical-align:middle;" rowspan=' . count($article_names) . '><div style="text-align:center;">' . htmlspecialchars($barcode['barcode']) . '</div></td>';
                }
                $table .= '<td style="vertical-align:middle;"><div style="text-align:center;">' . htmlspecialchars($article_names[$i]) . '</div></td>';
                $table .= '<td style="vertical-align:middle;"><div style="text-align:center;">' . htmlspecialchars($destock_counts[$i]) . '</div></td>';
            }
            $table .= '</tr>';
            $rowNum++;
        }
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
        <table cellspacing="0" cellpadding="1" border="1" style="border-color:gray;">
            <tr>
                <th>Barcode</th>
                <th>QR Code</th>
                <th>Article Name</th>
                <th>Destock Count</th>
            </tr>
            <?php foreach ($barcodes as $barcode): ?>
                <?php
                $article_names = explode(',', $barcode['article_name']);
                $destock_counts = explode(',', $barcode['destock_count']);
                for ($i = 0; $i < count($article_names); $i++):
                    ?>
                    <tr>
                        <?php if ($i == 0): // Only print barcode and QR code for the first row ?>
                            <td rowspan="<?= count($article_names) ?>"><?= htmlspecialchars($barcode['barcode']) ?></td>
                            <td rowspan="<?= count($article_names) ?>"><img src="<?= htmlspecialchars($barcode['qr_file']) ?>"></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($article_names[$i]) ?></td>
                        <td><?= htmlspecialchars($destock_counts[$i]) ?></td>
                    </tr>
                <?php endfor; ?>
            <?php endforeach; ?>
        </table>
        <button type="submit" class="btn btn-primary">Generate PDF</button>
    </form>
</div>
</body>
</html>
