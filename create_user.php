<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        // Check if username already exists
        $check = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already taken.";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO users (fullname, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fullname, $username, $hashed);

            if ($stmt->execute()) {
                $success = "User created successfully!";
            } else {
                $error = "Error creating user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create User - Sabon de Amor</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #ffb347, #ffcc33, #7ed957);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
    }

    .container {
        background: #fff;
        padding: 3rem;
        width: 450px;
        border-radius: 1.7rem;
        box-shadow: 0 0 25px rgba(0,0,0,0.15);
        text-align: center;
        animation: fadeIn 1.2s ease;
    }

    .logo img {
        width: 120px;
        height: 120px;
        object-fit: contain;
        border-radius: 50%;
        margin-bottom: 1.5rem;
        box-shadow: 0 0 20px rgba(126,217,87,0.8);
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2e7d32;
        margin-bottom: 1.5rem;
    }

    input {
        width: 100%;
        padding: 0.9rem;
        margin-bottom: 1rem;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 1rem;
        transition: 0.3s;
    }

    input:focus {
        border-color: #7ed957;
        box-shadow: 0 0 8px rgba(126,217,87,0.5);
        outline: none;
    }

    .btn {
        width: 100%;
        background: linear-gradient(90deg, #ff6f00, #ff9100);
        padding: 0.9rem;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(255,145,0,0.6);
    }

    .error {
        margin-top: 1rem;
        color: red;
        font-size: 0.9rem;
    }

    .success {
        margin-top: 1rem;
        color: green;
        font-size: 0.9rem;
    }

    .link {
        margin-top: 1rem;
        display: block;
        color: #2e7d32;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
    }

    .link:hover {
        text-decoration: underline;
        transform: scale(1.05);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>

<body>

<div class="container">
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>

    <h2>Create New User</h2>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Choose Username" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>

        <button class="btn" type="submit">Create User</button>
    </form>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <!-- Always visible login link -->
    <a class="link" href="login.php">Go to Login</a>

</div>

</body>
</html>
