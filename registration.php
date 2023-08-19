<?php
    include_once 'connect.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);

        // Password hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO `users` (username, password) VALUES ('$username', '$hashedPassword')";
        $result = mysqli_query($con, $query);

        if ($result) {
            $successMsg = "You are registered successfully.";
        } else {
            $errorMsg = "Something went wrong. Please try again.";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <link rel="stylesheet" href="src/bootstrap.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <h2 class="text-center">Registration</h2>
                <?php if(isset($successMsg)) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $successMsg; ?>
                    </div>
                <?php } ?>
                <?php if(isset($errorMsg)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php } ?>
                <form method="post" name="registration">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Register</button>
                </form>
                <p class="text-center">Already have an account? <a href="login.php">Login Now</a></p>
            </div>
        </div>
    </div>
    <script src="src/jquery.js"></script>
    <script src="src/bootstrap.bundle.js"></script>
</body>
</html>