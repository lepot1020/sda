<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO users(fullname, username, password) VALUES(?,?,?)");
    $stmt->bind_param("sss", $fullname, $username, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
        exit;
    } else {
        $error = "Username already exists.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
</head>
<body>
<h2>Create Account</h2>

<form method="POST">
    <input type="text" name="fullname" placeholder="Full Name" required><br><br>
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
</form>

<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

</body>
</html>
