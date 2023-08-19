<?php
require_once('functions.php');
require_once('phpqrcode/qrlib.php'); // assuming you have PHP QR Code installed
require_once('tcpdf/tcpdf.php'); // assuming you have TCPDF installed
include('header.php');
include('navbar.php');
// Fetch all barcodes
$stmt = $con->prepare("SELECT * FROM `ArticleContactView`");
$stmt->execute();
$result = $stmt->get_result();

$barcodes = array();

while ($row = $result->fetch_assoc()) {
    $barcodes[] = array(
        'barcode' => $row['barcode'],
        'article_name' => $row['article_name'],
        'destock_count' => $row['destock_count'],
        'category_name' => $row['category_name'],
        'name' => $row['name'] // assuming 'name' is the contact's name
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
    $table = '<table cellspacing="0" cellpadding="1" border="2" style="border-color:black;"><tr><th style="text-align:center;">種目</th><th style="text-align:center;">バーコード</th><th style="text-align:center;">品名</th><th style="text-align:center;">出庫単位</th><th style="text-align:center;">担当者</th></tr>';
    
    foreach ($barcodes as $barcode) {
        $table .= '<tr>';
        $table .= '<td style="border: 1px solid black; text-align:center; vertical-align:middle;">' . htmlspecialchars($barcode['category_name']) . '</td>';
        $table .= '<td style="border: 1px solid black; text-align:center; vertical-align:middle;"><img src="' . htmlspecialchars($barcode['qr_file']) . '"></td>'; // Displays barcode as image
        $table .= '<td style="border: 1px solid black; text-align:center; vertical-align:middle;">' . htmlspecialchars($barcode['article_name']) . '</td>';
        $table .= '<td style="border: 1px solid black; text-align:center; vertical-align:middle;">' . htmlspecialchars($barcode['destock_count']) . '</td>';
        $table .= '<td style="border: 1px solid black; text-align:center; vertical-align:middle;">' . htmlspecialchars($barcode['name']) . '</td>';
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
<div class="col-10 bg-light content">
    <h1>バーコードリスト</h1>
    <form method="post">
    <table cellspacing="0" cellpadding="1" border="2" style="border-color:black;">
    <tr>
    <th style="text-align:center;">種目</th>
        <th style="text-align:center;">バーコード</th>
        <th style="text-align:center;">品名</th>
        <th style="text-align:center;">出庫単位</th>
        <th style="text-align:center;">担当者</th>
        
    </tr>
    <?php foreach ($barcodes as $barcode): ?>
        <tr>
        <td style="border: 1px solid black; text-align:center; vertical-align:middle;"><?= htmlspecialchars($barcode['category_name']) ?></td>
            <td style="border: 1px solid black; text-align:center; vertical-align:middle;"><img src="<?= htmlspecialchars($barcode['qr_file']) ?>"></td>
            <td style="border: 1px solid black; text-align:center; vertical-align:middle;"><?= htmlspecialchars($barcode['article_name']) ?></td>
            <td style="border: 1px solid black; text-align:center; vertical-align:middle;"><?= htmlspecialchars($barcode['destock_count']) ?></td>
            <td style="border: 1px solid black; text-align:center; vertical-align:middle;"><?= htmlspecialchars($barcode['name']) ?></td>
            
        </tr>
    <?php endforeach; ?>
</table>

        <button type="submit" class="btn btn-primary">Generate PDF</button>
    </form>
    </div>
<?php include('footer.php'); ?>