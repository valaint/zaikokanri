<?php 
include_once 'admin_header.php';

// Fetch all distinct dates from stock_log for the dropdown
$stmt = $con->prepare("SELECT DISTINCT date FROM stock_log ORDER BY date");
$stmt->execute();
$result = $stmt->get_result();
$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['date']) && $_POST['confirm'] == '棚卸前に復元') {
        $date = $_POST['date'];
        $stock_column = 'original_stock';
        
        restore_stock($con, $date, $stock_column);
    } else if ($_POST['date'] && $_POST['confirm'] == '棚卸後に復元') {
        $date = $_POST['date'];
        $stock_column = 'updated_stock';

        restore_stock($con, $date, $stock_column);
    }
}

function restore_stock($con, $date, $stock_column) {
    // Fetch all rows from stock_log for the selected date
    $stmt = $con->prepare("SELECT article_id, $stock_column FROM stock_log WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $article_id = $row['article_id'];
        $stock = $row[$stock_column];

        // Update the current stock in article_info
        $stmt = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
        $stmt->bind_param("ii", $stock, $article_id);
        $stmt->execute();

        echo "<div class='alert alert-success'>Stock restored successfully for Article ID: $article_id on Date: $date</div>";
    }
}

?>
<div class="container mt-4">
    <form method="POST" action="" class="form-inline">
        <div class="form-group mb-2">
            <label for="date" class="sr-only">Date:</label>
            <select id="date" name="date" class="form-control">
                <?php foreach ($dates as $date): ?>
                    <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mb-2 ml-2">Show Values</button>
    </form>

    <?php
    if (isset($_POST['date'])) {
        $date = $_POST['date'];
        echo "<h3 class='mt-4'>Current and Restored Values for Date: $date</h3>";
        echo "<table class='table table-striped mt-2'>
                <tr>
                    <th>物品名</th>
                    <th>棚卸前</th>
                    <th>棚卸後</th>
                    <th>現在の個数</th>
                </tr>";

        $stmt = $con->prepare("SELECT article_info.article_id, article_info.article_name,article_info.stock, stock_log.original_stock, stock_log.updated_stock
                FROM stock_log 
                INNER JOIN article_info ON stock_log.article_id = article_info.article_id
                WHERE stock_log.date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $csv_data = [];
        $csv_data[] = ["物品名", "棚卸前", "棚卸後", "現在の個数"];
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['article_name']}</td>
                    <td>{$row['original_stock']}</td>
                    <td>{$row['updated_stock']}</td>
                    <td>{$row['stock']}</td>
                  </tr>";
                  
            $csv_data[] = [$row['article_name'], $row['original_stock'], $row['updated_stock'], $row['stock']];
        }

        echo "</table>";
        $_SESSION['csv_data'] = $csv_data;

        echo "<form method='POST' action='' class='mb-4'>
                <input type='hidden' name='date' value='$date'>
                <button type='submit' name='confirm' value='棚卸前に復元' class='btn btn-primary'>棚卸前に復元</button>
                <button type='submit' name='confirm' value='棚卸後に復元' class='btn btn-secondary ml-2'>棚卸後に復元</button>
              </form>";

        echo "<a href='download_csv.php' class='btn btn-success mb-4'>Download CSV</a>";
    }
    ?>
</div>
<?php include_once 'admin_footer.php'; ?>
