<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, fullname, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($uid, $fullname, $hashed);
        $stmt->fetch();

        if (password_verify($pass, $hashed)) {
            $_SESSION['user_id'] = $uid;
            $_SESSION['fullname'] = $fullname;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Sabon de Amor</title>
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
        width: 420px;
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

    .create-link {
        margin-top: 1rem;
        display: block;
        font-size: 0.9rem;
        color: #2e7d32;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
    }

    .create-link:hover {
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

    <h2>Login to System</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button class="btn" type="submit">Login</button>
    </form>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <a href="create_user.php" class="create-link">Don't have an account? Create one</a>

</div>

</body>
</html>
