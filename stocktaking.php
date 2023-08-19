<?php
require_once('functions.php');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST as $article_id => $updated_stock) {
        // Skip if updated_stock is not provided or is not a positive integer
        if (!isset($updated_stock) || $updated_stock < 0) {
            continue;
        }

        // Get current stock from article_info table
        $stmt = $con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $original_stock = $row[0];

        // Update stock in article_info table
        $stmt = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
        $stmt->bind_param("ii", $updated_stock, $article_id);
        $stmt->execute();

        // Insert a log entry in stock_log table
        $stmt = $con->prepare("INSERT INTO stock_log (article_id, original_stock, updated_stock, date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $article_id, $original_stock, $updated_stock);
        $stmt->execute();
    }
}
// Rest of your HTML code goes here...
?>



<html>
    <head>
        <script src="filter.js"></script>
        <script src="hiddenclick.js"></script>
        <script src="barcode.js"></script>
        <link href="src/jquery.js" rel="stylesheet">
        <link href="src/bootstrap.css" rel="stylesheet">
        <link href="style.css" rel="stylesheet">
        <meta charset="utf-8">
        <title>
            在庫管理
        </title>
        <script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>
    </head>
    <body>

    <div class="container-fluid border">
        <div class="row">
            <div class="col-12 align-items-center">
                <h1 class="text-center">在庫管理</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-2 sidebar bg-info bg-opacity-50 text-white">
                <div class="nav">
                    <ul class="nav nav-sidebar">
                        <li class="active"><a class="nav-link active">在庫管理</a></li>
                        <li class="nav-item"><a class="nav-link">バーコード</a></li>
                        <li class="nav-item"><a class="nav-link">在庫管理委員用</a></li>
                    </ul>
                </div>

            </div>
            <div class="col-10 bg-light">
                <form id="stocktaking" method="POST" action="stocktaking.php">
                    <table class="table table-striped table-hover" id="inventorylist">
                        <thead>
                        <tr>
                            <th>物品名</th>
                            <th>現在の在庫数</th>
                            <th>更新された在庫数</th>
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
    <td>{$row[1]}</td>
    <td><input type='number' form=stocktaking min='0' name={$row[2]} size=2></td>
    </tr>";
                        }
                        $result->free();
                        ?>
                        </tbody>
                    </table>
                    <br>
                    <button type="submit" class="btn btn-primary">在庫を更新</button>
                </form>
            </div>
        </div>
    </div>
    </body>
</html>