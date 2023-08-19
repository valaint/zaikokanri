<?php
    session_start();
    
    // Destroy the session.
    if(session_destroy()) {
        // Redirecting To Home Page
        header("Location: login.php");
    }
?>