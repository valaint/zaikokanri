<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
?>
<html>
<head>
    <script src="src/bootstrap.bundle.min.js"></script>
    <link href="src/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理</title>
</head>
<body>
<div class="container-fluid">
    <div class="row header-row">
        <div class="col-12">
            <h1>在庫管理</h1>
        </div>
    </div>
