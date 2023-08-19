<?php
    session_start();
    
    // Include database configuration file
    include_once 'connect.php';

    // When form submitted, check and create user session.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        
        // Check user is exist in the database
        $query    = "SELECT * FROM `users` WHERE username='$username'";
        $result = mysqli_query($con, $query) or die(mysql_error());
        $rows = mysqli_num_rows($result);
        
        if ($rows == 1) {
            $user = mysqli_fetch_assoc($result);
            $passwordIsValid = password_verify($_POST['password'], $user['password']);
            if ($passwordIsValid) {
                $_SESSION['username'] = $username;
                // Redirect to user dashboard page
                header("Location: admin.php");
            } else {
                $errorMsg = "Incorrect Username/password.";
            }
        } else {
            $errorMsg = "Incorrect Username/password.";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <!-- CSS only -->
    <link rel="stylesheet" href="src/bootstrap.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <h2 class="text-center">Login</h2>
                <?php if(isset($errorMsg)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php } ?>
                <form method="post" name="login">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <!-- JS, Popper.js, and jQuery -->
    <script src="src/jquery.js"></script>
    <script src="src/bootstrap.bundle.js"></script>
</body>
</html>