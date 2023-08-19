<div class="row">
    <div class="col-2 sidebar bg-primary h-100">
        <div class="nav">
            <ul class="nav flex-column">
                <?php
                $pages = array(
                    'index.php' => '在庫管理',
                    'barcode.html' => 'バーコード',
                    'barcodeprint.php' => 'バーコードリストの表',
                    'item.php' => '物品情報',
                    'count.php' => '集計',
                    'admin.php' => '在庫管理委員用'
                );
                $current_page = basename($_SERVER['SCRIPT_NAME']);
                foreach($pages as $file => $name) {
                    $active = ($file == $current_page) ? 'active' : '';
                    echo "<li class='nav-item'><a class='nav-link {$active}' href='{$file}'>{$name}</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>