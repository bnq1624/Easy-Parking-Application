<?php
require_once "Database.php";
require_once "User.php";

$db = new Database();
$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // all sign up information
    $username = $_POST["username"];
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $userType = $_POST["userType"];

    if ($user->userExists($username)) {
        $message = "<p>Username already exists.</p>";
    } else {
        if ($user->signUp($username, $name, $surname, $phone, $email, $password, $userType)) {
            $message = "<p>Sign up successfully!</p>";
            $message .= "<p><a href='startPage.php' id='loginLink'>Log in Now?</a></p>";
        }
    }
}

$db->close();
?>

<html>
<head>
    <title>Sign Up</title>
    <style>
        #loginLink {
            text-decoration: none;
            color: blue;
        }

        #header {
            font-family: Arial, sans-serif;
        }

        .inputFields {
            margin-bottom: 12px;
        }

        #userType {
            font-size: 12px;
            margin-bottom: 10px;
        }

        #userTypeValues {
            margin-bottom: 10px;
            width: 170px;
            height: 22px;
        }

        #signUpButton {
            padding: 7px 17px;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            background-color: #40a040;
            border: none;
            border-radius: 2px;
            font-family: Arial, sans-serif;
            margin-left: 40px;
        }

        #signUpButton:hover {
            background-color: #89c489;
        }
    </style>
</head>

<body>
    <h2 id="header">Sign Up</h2>
    
    <form id="signUpForm" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <input placeholder="Username" type="text" id="username" name="username" required class="inputFields" /><br />
        <input placeholder="First name" type="text" id="name" name="name" required class="inputFields" /><br />
        <input placeholder="Surname" type="text" id="surname" name="surname" required class="inputFields" /><br />
        <input placeholder="Phone" type="text" id="phone" name="phone" required class="inputFields" /><br />
        <input placeholder="Email" type="text" id="email" name="email" required class="inputFields" /><br />
        <input placeholder="Password" type="password" id="password" name="password" required class="inputFields" /><br />
        <label for="userType" id="userType">User Type?</label><br />
        <select id="userTypeValues" name="userType">
            <option value="Administrator">Administrator</option>
            <option value="User">User</option>
        </select>
        <br />
        <input type="submit" id="signUpButton" value="Sign Up" />
    </form>

    <?php if (isset($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>
</body>
</html>
