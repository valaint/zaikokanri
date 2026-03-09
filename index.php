<?php
require_once('functions.php');
if (isset($_POST['restock'])) {
    handleStock('restock', $_POST);
}

if (isset($_POST['destock'])) {
    handleStock('destock', $_POST);
}

if (isset($_POST['barcodetext'])) {
    $barcode = $_POST['barcodetext'];

    $stmt = $con->prepare("SELECT article_id, destock_count FROM barcode_list WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_row()) {
        $article_id = $row[0];
        $destock_count = $row[1];
        handleStock('destock', null, $article_id, $destock_count);
    }
    $result->free();
}

unset($_POST);
include('header.php');
include('navbar.php');
?>
        <script src="filter.js"></script>
        <script src="hiddenclick.js"></script>
        <script src="barcode.js"></script>
        <script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>


            <div class="col-10 bg-light p-3">
                <div class="row mb-3 align-items-center">
                    <div class="col-auto">
                        <select class="form-control" id="categorylist" oninput="filterTable()">
                            <option>All</option>
                            <?php
                            $categories = getCategories();
                            foreach ($categories as $category_name) {
                                echo "<option>" . htmlspecialchars($category_name) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="text" id="searchArticleList" class="form-control" placeholder="Search by name..." oninput="filterTable()">
                    </div>
                    <div class="col-auto ml-auto">
                        <a href="download_csv.php" class="btn btn-outline-success">CSV出力</a>
                    </div>
                </div>


                <form id="stockupdate" method="POST" action="index.php" onsubmit="return validate();">
                    <div class="tableFixHead">
                        <table class="table table-striped table-hover" id="inventorylist">
                            <thead>
                            <tr>
                                <th>種目</th>
                                <th>物品名</th>
                                <th>在庫数</th>
                                <th>入力</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $inventory = getInventory();
                            foreach ($inventory as $row) {
                                $category_name = htmlspecialchars($row[0] ?? '');
                                $article_name = htmlspecialchars($row[1] ?? '');
                                $stock = htmlspecialchars($row[2] ?? '');
                                $article_id = htmlspecialchars($row[3] ?? '');
                                echo "<tr> 
        <td>{$category_name}</td>
        <td>{$article_name}</td>
        <td>{$stock}</td>
        <td><input type='number' form=stockupdate min='0' name='{$article_id}' size='2'></td>
        </tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <input type="hidden" id="hiddensubmit" name="">
                    <input type="button" class="btn btn-primary btn-pill"
                        onclick="Submitfrm(this)" form=stockupdate name="restock" value="入庫">
                    <input type="button" class="btn btn-primary btn-pill"
                        onclick="Submitfrm(this)" form=stockupdate name="destock" value="出庫">
                </form>
                <div class="stockhistory col-10 bd-callout-warning">
                    <?php
                    $history = getRecentHistory();
                    foreach ($history as $row) {
                        $time = htmlspecialchars($row[0] ?? '');
                        $article_name = htmlspecialchars($row[1] ?? '');
                        $changed_value = htmlspecialchars($row[2] ?? '');
                        $type = htmlspecialchars($row[3] ?? '');
                        echo "{$time} {$article_name} {$changed_value}個{$type}されました。<br>";
                    }
                    echo "<br>";
                    ?>
                </div>
            </div>
<?php include('footer.php'); ?>