<?php
include_once 'admin_header.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid_post = [];
    foreach ($_POST as $article_id => $updated_stock) {
        // Skip if updated_stock is not provided or is not a positive integer
        if (isset($updated_stock) && $updated_stock >= 0 && is_numeric($article_id)) {
            $valid_post[(int)$article_id] = (int)$updated_stock;
        }
    }

    if (!empty($valid_post)) {
        // Get all original stocks in one query
        $article_ids = array_keys($valid_post);
        $placeholders = implode(',', array_fill(0, count($article_ids), '?'));
        $types = str_repeat('i', count($article_ids));

        $stmt_select = $con->prepare("SELECT article_id, stock FROM article_info WHERE article_id IN ($placeholders)");
        $stmt_select->bind_param($types, ...$article_ids);
        $stmt_select->execute();
        $result = $stmt_select->get_result();

        $original_stocks = [];
        while ($row = $result->fetch_assoc()) {
            $original_stocks[$row['article_id']] = $row['stock'];
        }
        $stmt_select->close();

        $stmt_update = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
        $stmt_update->bind_param("ii", $bind_updated_stock, $bind_article_id);

        $stmt_insert = $con->prepare("INSERT INTO stock_log (article_id, original_stock, updated_stock, date) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY `updated_stock` = VALUES(`updated_stock`);");
        $stmt_insert->bind_param("iii", $bind_article_id, $bind_original_stock, $bind_updated_stock);

        foreach ($valid_post as $article_id => $updated_stock) {
            if (!isset($original_stocks[$article_id])) {
                continue;
            }

            $bind_article_id = $article_id;
            $bind_updated_stock = $updated_stock;
            $bind_original_stock = $original_stocks[$article_id];

            // Update stock in article_info table
            $stmt_update->execute();

            // Insert a log entry in stock_log table
            $stmt_insert->execute();
        }

        $stmt_update->close();
        $stmt_insert->close();
    }

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
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".copyValue").forEach(function(button) {
        button.addEventListener("click", function() {
            var tr = this.closest('tr');
            var currentVal = tr.querySelector('.currentValue').textContent;
            tr.querySelector('.inputValue').value = currentVal;
        });
    });

    const copyAllBtn = document.getElementById("copyAllValues");
    if (copyAllBtn) {
        copyAllBtn.addEventListener("click", function() {
            document.querySelectorAll("tbody tr").forEach(function(tr) {
                var currentVal = tr.querySelector('.currentValue').textContent;
                tr.querySelector('.inputValue').value = currentVal;
            });
        });
    }

    const clearAllBtn = document.getElementById("clearAllValues");
    if (clearAllBtn) {
        clearAllBtn.addEventListener("click", function() {
            document.querySelectorAll("tbody tr").forEach(function(tr) {
                tr.querySelector('.inputValue').value = '';
            });
        });
    }
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