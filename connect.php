<?php
$host = 'localhost';
$user = 'eeismzak';
$password = 'zaikokanrimysql';
$dbname = 'eeismzak';

$con = new mysqli($host, $user, $password, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>