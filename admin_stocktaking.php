<?php
include_once 'admin_header.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare statements outside the loop
    $stmtSelect = $con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
    $stmtUpdate = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
    $stmtLog = $con->prepare("INSERT INTO stock_log (article_id, original_stock, updated_stock, date) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY `updated_stock` = VALUES(`updated_stock`);");

    $bind_article_id = 0;
    $bind_updated_stock = 0;
    $bind_original_stock = 0;

    $stmtSelect->bind_param("i", $bind_article_id);
    $stmtUpdate->bind_param("ii", $bind_updated_stock, $bind_article_id);
    $stmtLog->bind_param("iii", $bind_article_id, $bind_original_stock, $bind_updated_stock);

    foreach ($_POST as $article_id => $updated_stock) {
        // Skip if updated_stock is not provided or is not a positive integer
        if (!isset($updated_stock) || $updated_stock < 0) {
            continue;
        }

        $bind_article_id = (int)$article_id;
        $bind_updated_stock = (int)$updated_stock;

        // Get current stock from article_info table
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();
        $row = $result->fetch_row();
        $original_stock = $row[0];
        $result->free();

        $bind_original_stock = (int)$original_stock;

        // Update stock in article_info table
        $stmtUpdate->execute();

        // Insert a log entry in stock_log table
        $stmtLog->execute();
    }

    $stmtSelect->close();
    $stmtUpdate->close();
    $stmtLog->close();

    // Set a success message
    $_SESSION['success_msg'] = '在庫数が更新されました。';
}

// Rest of your HTML code goes here...
?>

<!-- Alert for the success message -->
<?php if (isset($_SESSION['success_msg'])) : ?>
    <div class="alert alert-success">
        <?php
        echo $_SESSION['success_msg'];
        unset($_SESSION['success_msg']);  // unset the success message after displaying it
        ?>
    </div>
<?php endif; ?>


<script>
$(document).ready(function() {
    $(".copyValue").click(function() {
        var currentVal = $(this).closest('tr').find('.currentValue').text();
        $(this).closest('tr').find('.inputValue').val(currentVal);
    });

    $("#copyAllValues").click(function() {
        $('tbody tr').each(function() {
            var currentVal = $(this).find('.currentValue').text();
            $(this).find('.inputValue').val(currentVal);
        });
    });

    $("#clearAllValues").click(function() {
        $('tbody tr').each(function() {
            $(this).find('.inputValue').val('');
        });
    });
});
</script>

<form id="stocktaking" method="POST" action="admin_stocktaking.php">
    <table class="table table-striped table-hover" id="inventorylist">
        <thead>
            <tr>
                <th>物品名</th>
                <th>現在の在庫数</th>
                <th>棚卸後の在庫数</th>
                <th><button type="button" id="copyAllValues">すべて現在の個数</button></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $con->prepare("SELECT article_name, stock, article_id FROM article_info ORDER BY article_id");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_row()) {
            echo "<tr>
                <td>{$row[0]}</td>
                <td class='currentValue'>{$row[1]}</td>
                <td><input type='number' class='inputValue' form=stocktaking min='0' name={$row[2]} size=2></td>
                <td><button type='button' class='copyValue'>現在の個数</button></td>
            </tr>";
        }
        $result->free();
        ?>
        </tbody>
    </table>
    <br>
    <button type="submit" class="btn btn-primary">在庫を更新</button>
    <button type="button" id="clearAllValues" class="btn btn-secondary">Clear All</button>
</form>

<?php include_once 'admin_footer.php'; ?>