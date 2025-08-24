<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Always return JSON for POST requests
    header('Content-Type: application/json');
    // Suppress any output before JSON
    ob_start();
    $host = '127.0.0.1';
    $dbname = 'mcg_test';
    $username = 'clyde';
    $password = 'PurpleHorse@01';

    $username_in = trim($_POST['username'] ?? '');
    $email_in = trim($_POST['email'] ?? '');
    $password_in = $_POST['password'] ?? '';

    // Basic validation
    if (!$username_in || !$email_in || !$password_in) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }
    if (!filter_var($email_in, FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }
    if (strlen($username_in) < 3 || strlen($username_in) > 32) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Username must be 3-32 characters.']);
        exit;
    }
    if (strlen($password_in) < 6) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for duplicate username/email
        $stmt = $pdo->prepare("SELECT player_id FROM players WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username_in, $email_in]);
        if ($stmt->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Username or email already exists.']);
            exit;
        }

        $password_hash = password_hash($password_in, PASSWORD_DEFAULT);
        $display_name = $username_in;
        $stmt = $pdo->prepare("INSERT INTO players (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username_in, $password_hash, $display_name, $email_in]);
        ob_end_clean();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e, #16213e);
            color: #fff;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: rgba(30,30,40,0.97);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            padding: 36px 32px 28px 32px;
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 18px;
            color: #00d4ff;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid #444;
            background: #222;
            color: #fff;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: linear-gradient(45deg, #00d4ff, #ff0080);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 8px;
        }
        .error, .success {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .error { color: #ff3c3c; }
        .success { color: #00ff88; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register New Account</h2>
        <form id="registerForm" method="post" action="register.php">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Register">
        </form>
        <div id="register-message"></div>
        <div style="text-align:center; margin-top:10px;">
            <a href="index.php" style="color:#ffed4e; text-decoration:underline;">Back to Login</a>
        </div>
    </div>
    <script>
    document.getElementById('registerForm').onsubmit = function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        fetch('register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            var msg = document.getElementById('register-message');
            if (data.success) {
                msg.textContent = 'Registration successful! You can now log in.';
                msg.className = 'success';
                form.reset();
            } else {
                msg.textContent = data.error || 'Registration failed.';
                msg.className = 'error';
            }
        })
        .catch(() => {
            var msg = document.getElementById('register-message');
            msg.textContent = 'Network error.';
            msg.className = 'error';
        });
    };
    </script>
</body>
</html>
