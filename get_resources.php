<?php
// get_resources.php - AJAX endpoint for fetching resource data
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Database configuration
$host = '127.0.0.1';
$dbname = 'mcg_test';
$username = 'clyde';
$password = 'PurpleHorse@01';

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method not allowed. Received: ' . $_SERVER['REQUEST_METHOD'] . '. Expected: POST'
    ]);
    exit;
}

// Get player name from POST data
$player = $_POST['player'] ?? 'Verbil';

// Sanitize player name to prevent SQL injection
$player = filter_var($player, FILTER_SANITIZE_STRING);

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Use logged-in player's ID from session if available
    session_start();
    if (isset($_SESSION['player_id'])) {
        $player_id = (int)$_SESSION['player_id'];
    } else {
        $player_id = 1; // fallback for not logged in
    }
    // Query for normalized resources table - simplified approach
    $stmt = $pdo->prepare("
        SELECT resource_type, quantity 
        FROM player_resources 
        WHERE player_id = ? AND resource_type IN (1,2,3,4)
    ");
    $stmt->execute([$player_id]);
    
    $results = $stmt->fetchAll();
    
    // Initialize resources array
    $resources = [
        'aetherite' => 0,
        'crysalon' => 0, 
        'ignisium' => 0,
        'veltrium' => 0
    ];
    
    // Map the results to resource names
    foreach ($results as $row) {
        switch ($row['resource_type']) {
            case 1:
                $resources['aetherite'] = (int)$row['quantity'];
                break;
            case 2:
                $resources['crysalon'] = (int)$row['quantity'];
                break;
            case 3:
                $resources['ignisium'] = (int)$row['quantity'];
                break;
            case 4:
                $resources['veltrium'] = (int)$row['quantity'];
                break;
        }
    }
    
    // Always return success if we got here without errors
    if (count($results) >= 0) {  // Changed from > 0 to >= 0
        // Format the response
        $response = [
            'success' => true,
            'resources' => $resources,
            'timestamp' => date('Y-m-d H:i:s'),
            'player_id' => $player_id,
            'debug' => 'Found ' . count($results) . ' resource records',
            'raw_results' => $results,  // Temporary debug info
            'post_data' => $_POST  // Debug: show what POST data was received
        ];
    } else {
        // No data found for this player
        $response = [
            'success' => false,
            'error' => 'No data found for player_id: ' . $player_id,
            'resources' => [
                'aetherite' => 0,
                'crysalon' => 0,
                'ignisium' => 0,
                'veltrium' => 0
            ]
        ];
    }
    
} catch (PDOException $e) {
    // Database error
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'resources' => [
            'aetherite' => 0,
            'crysalon' => 0,
            'ignisium' => 0,
            'veltrium' => 0
        ]
    ];
    
    // Log the error (optional)
    error_log("Database error in get_resources.php: " . $e->getMessage());
    
} catch (Exception $e) {
    // General error
    $response = [
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'resources' => [
            'aetherite' => 0,
            'crysalon' => 0,
            'ignisium' => 0,
            'veltrium' => 0
        ]
    ];
}

// Return JSON response
echo json_encode($response);