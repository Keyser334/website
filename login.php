<?php
session_start();
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'mcg_test';
$username_db = 'clyde';
$password_db = 'PurpleHorse@01';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
	exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
	echo json_encode(['success' => false, 'error' => 'Username and password required.']);
	exit;
}

try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	$stmt = $pdo->prepare("SELECT player_id, username, password_hash, display_name FROM players WHERE username = ? LIMIT 1");
	$stmt->execute([$username]);
	$user = $stmt->fetch();

	if ($user && password_verify($password, $user['password_hash'])) {
		// Login success
		$_SESSION['player_id'] = $user['player_id'];
		$_SESSION['username'] = $user['username'];
		$_SESSION['display_name'] = $user['display_name'] ?? $user['username'];
		echo json_encode(['success' => true, 'username' => $_SESSION['username'], 'display_name' => $_SESSION['display_name']]);
	} else {
		// Login failed
		echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
	}
} catch (PDOException $e) {
	echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
