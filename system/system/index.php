<?php
require_once('../connect.php');

if ($con) {
    echo "Successfully connected to the database.";
} else {
    echo "Failed to connect to the database.";
}
?>