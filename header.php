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
    <title>在庫管理</title>
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
            <h1>在庫管理</h1>
        </div>
    </div>