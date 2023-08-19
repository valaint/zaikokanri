<?php
require_once('functions.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<html>
<head>
    <script src="src/jquery.min.js"></script>
    <script src="src/jquery-ui.min.js"></script>
    <script src="src/bootstrap.bundle.min.js"></script>
    <link href="src/jquery-ui.min.css" rel="stylesheet">
    <link href="src/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <style>
        .header-row {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .header-row h1 {
            padding: 20px 0;
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .sidebar {
            height: 100vh;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar .nav-item {
            padding: 5px 0;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row header-row">
        <div class="col-12">
            <h1>Admin Page</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-2 sidebar bg-primary">
            <div class="nav">
                <ul class="nav flex-column">
                    <?php
                    $pages = array(
                        'admin_stock.php' => '物品管理',
                        'add_article.php' => 'Add New Article',
                        'admin_barcodelist.php' => 'バーコードリスト',
                        'admin_category.php' => '種目リスト',
                        'admin_contact.php' => '担当者リスト',
                        'admin_stocktaking.php' => '棚卸',
                        'admin_restore.php' => '棚卸データ・復元',
                        'logout.php' => 'ログアウト'
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
        <div class="col-10 bg-light content">
