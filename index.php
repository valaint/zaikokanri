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
                <div class="row">
                    <div class="col-2">
                        <select id="categorylist" oninput="filterTable()">
                            <option>All</option>
                            <?php
                            $stmt = $con->prepare("SELECT category_name FROM category");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo "<option>{$row['category_name']}</option>";
                            }
                            $result->free();
                            ?>
                        </select>
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
                            $stmt = $con->prepare("SELECT (SELECT category_name from category WHERE article_info.category_id=category.category_id),article_name,stock,article_id from article_info ORDER BY category_id,article_order");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_row()) {
                                echo "<tr> 
        <td>{$row[0]}</td>
        <td>{$row[1]}</td>
        <td>{$row[2]}</td>
        <td><input type='number' form=stockupdate min='0' name={$row[3]} size=2></td>
        </tr>";
                            }
                            $result->free();
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <input type="hidden" id="hiddensubmit" name="">
                    <input type="button" class="btn btn-primary btn-pill" onclick="Submitfrm(this)"; form=stockupdate name="restock" value="入庫"><input type="button" class="btn btn-primary btn-pill" onclick="Submitfrm(this)"; form=stockupdate name="destock" value="出庫">
                </form>
                <div class="stockhistory col-10 bd-callout-warning">
                    <?php
                    $stmt = $con->prepare("SELECT time,(SELECT article_name from article_info WHERE article_info.article_id=history.article_id),changed_value,type from history ORDER by `time` desc LIMIT 30");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_row()) {
                        echo "{$row[0]} {$row[1]} {$row[2]}個{$row[3]}されました。<br>";
                    }
                    $result->free();
                    echo "<br>";
                    ?>
                </div>
            </div>
<?php include('footer.php'); ?>