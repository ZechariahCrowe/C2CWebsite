<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] === 'POST') {

    include("database.php");
    /** @var mysqli $conn */

    $username = $_POST['username'];
    $password = $_POST['password'];

    //Basic validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!empty($errors)) {
        echo "<h2>Errors:</h2>";
        foreach ($errors as $error) {
            echo "<p class='error-message'>$error</p>";
        }

        echo "<div class= 'switch-link'><a href='login.php'> Go back to login</a></div>";
    } else {

        $stmt = $conn->prepare("SELECT * FROM tbluser WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();
            // learn about sessions in PHP to maintain user login state across pages: https://www.youtube.com/watch?v=H3n9qQyXo2k

            // start the session
            session_start();

            // save user information into the $_SESSION global variable
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $user['username'];

            //redirect to homepage
            header("Location: homePage.php");
            exit(); // always call exit after header redirect

        }
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles/registerStyles.css">
    <title>Login</title>

</head>

<body>
    <div>

        <div class="form-container">
            <h2>Login</h2>

            <form action="login.php" method="POST">

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required><br><br>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required><br><br>
                </div>

                <button type="submit">Login</button>

            </form>

            <a href="register.html" class="register">
                <h3>Dont have an account?</h3>
            </a>

        </div>

    </div>

</body>

</html>