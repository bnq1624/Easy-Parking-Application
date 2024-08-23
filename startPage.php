<?php
session_start();
require_once "database.php";
require_once "User.php";

$db = new Database();
$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // use the information retrieved to try to login
    $loginInformation = $user->login($username, $password);
    if ($loginInformation) {
        $_SESSION["authenticated"] = true;

        // retrieve the username and user type for implementing the functionalities of each type of user
        $_SESSION["username"] = $username;                          
        $_SESSION["type"] = $loginInformation["type"];

        // Each type of user will move to a different dashboard
        if($_SESSION["type"] == "User") {
            header("Location: UserDashboard.php");
        }elseif($_SESSION["type"] == "Administrator") {
            header("Location: AdministratorDashboard.php");
        }
        exit();
    } else {
        $errorMessage = "Invalid username or password, please try again.";
    }
}

$db->close();
?>

<html>
<head>
    <title>Login</title>
    <style>
        #header {
            font-family: Arial, sans-serif;
        }

        .credentials {
            width: 163px;
            height: 25px;
            margin-bottom: 12px;
        }

        #loginButton {
            padding: 7px 17px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            background-color: black;
            border: none;
            border-radius: 2px;
            font-family: Arial, sans-serif;
        }

        #signUpButton {
            padding: 7px 17px;
            margin: 5px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: black;
            background-color: white;
            border: 1px solid gray;
            border-radius: 2px;
            font-family: Arial, sans-serif;
        }

        #loginButton:hover {
            background-color: #282828;
        }

        #signUpButton:hover {
            background-color: #989898;
        }
    </style>
</head>
<body>
    <h2 id="header">Easy Parking</h2>

    <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <input placeholder="Username" type="text" id="username" name="username" required class="credentials" /><br />
        <input placeholder="Password" type="password" id="password" name="password" required class="credentials" /><br />
        <input type="submit" id="loginButton" value="Login" />
        <a href="signUp.php" id="signUpButton">Sign Up</a>
    </form>

    <?php if (isset($errorMessage)): ?>
        <?php echo $errorMessage; ?>
    <?php endif; ?>
</body>
</html>
