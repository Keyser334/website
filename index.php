<?php
session_start();
// Database configuration
$host = '127.0.0.1';
$dbname = 'mcg_test';
$username = 'clyde';
$password = 'PurpleHorse@01';

// You can remove this initial query if you only want AJAX updates
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Initial load query for normalized resources table
    $player_id = 1;
    
    $stmt = $pdo->prepare("
        SELECT resource_type, quantity 
        FROM player_resources 
        WHERE player_id = ? AND resource_type IN (1,2,3,4)
    ");
    $stmt->execute([$player_id]);
    $results = $stmt->fetchAll();
    
    // Initialize resources array with your actual data
    $resources = [
        'aetherite' => 0,
        'crysalon' => 0,
        'ignisium' => 0,
        'veltrium' => 0
    ];
    
    // Map the results
    foreach ($results as $row) {
        switch ($row['resource_type']) {
            case 1: $resources['aetherite'] = (int)$row['quantity']; break;
            case 2: $resources['crysalon'] = (int)$row['quantity']; break;
            case 3: $resources['ignisium'] = (int)$row['quantity']; break;
            case 4: $resources['veltrium'] = (int)$row['quantity']; break;
        }
    }
} catch(PDOException $e) {
    // Fallback values if database connection fails
    $resources = [
        'aetherite' => 1247,
        'crysalon' => 89,
        'ignisium' => 15743,
        'veltrium' => 67
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exodus Genesis - Space Exploration</title>
    <style>
        .login-status {
            position: absolute;
            top: 20px;
            right: 40px;
            background: rgba(30,30,40,0.95);
            border-radius: 12px;
            padding: 18px 28px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            z-index: 100;
            min-width: 220px;
            text-align: right;
        }
        .login-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .indicator-logged {
            background: #00ff88;
            box-shadow: 0 0 8px #00ff88;
        }
        .indicator-not {
            background: #ff3c3c;
            box-shadow: 0 0 8px #ff3c3c;
        }
        .login-status button, .login-status input[type="submit"] {
            background: linear-gradient(45deg, #00d4ff, #ff0080);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 18px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 8px;
        }
        .login-status a {
            color: #ffed4e;
            text-decoration: underline;
            margin-left: 10px;
            font-size: 0.95rem;
        }
        .login-status form {
            margin-top: 10px;
        }
        .login-status input[type="text"], .login-status input[type="password"] {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #444;
            margin-bottom: 7px;
            width: 90%;
            background: #222;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-status">
        <?php if (isset($_SESSION['username'])): ?>
            <span class="login-indicator indicator-logged"></span>
            Logged in as <strong><?php echo htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username']); ?></strong><br>
            <form method="post" action="logout.php" style="display:inline;">
                <input type="submit" value="Logout">
            </form>
        <?php else: ?>
            <span class="login-indicator indicator-not"></span>
            <span style="color:#ff3c3c;font-weight:bold;">Not Logged In</span><br>
            <button id="show-login-btn">Login</button>
            <div id="login-form" style="display:none; margin-top:10px;">
                <form method="post" action="login.php" id="loginForm">
                    <input type="text" name="username" placeholder="Username" required><br>
                    <input type="password" name="password" placeholder="Password" required><br>
                    <input type="submit" value="Login">
                </form>
                <a href="register.php">Register a new account</a>
                <div id="login-error" style="color:#ff3c3c; margin-top:7px;"></div>
            </div>
            <script>
            // Show login form when button is clicked
            document.getElementById('show-login-btn').onclick = function() {
                document.getElementById('login-form').style.display = 'block';
                this.style.display = 'none';
            };
            // Attach submit handler after DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                var loginForm = document.getElementById('loginForm');
                if (loginForm) {
                    loginForm.onsubmit = function(e) {
                        e.preventDefault();
                        var formData = new FormData(loginForm);
                        fetch('login.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                document.getElementById('login-error').textContent = data.error || 'Login failed.';
                            }
                        })
                        .catch(() => {
                            document.getElementById('login-error').textContent = 'Network error.';
                        });
                    };
                }
            });
            </script>
        <?php endif; ?>
    </div>
    <!-- Main dashboard section (single instance) -->
    <div class="container">
        <div class="header">
            <h1 class="game-title">Exodus Genesis</h1>
            <p class="subtitle">Space Exploration 1.01</p>
        </div>
        <div class="realtime-section">
            <h2 class="section-title">Verbil's Resources</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="aetherite"><?php echo number_format($resources['aetherite'] ?? 0); ?></div>
                    <div class="stat-label">Aetherite</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="crysalon"><?php echo number_format($resources['crysalon'] ?? 0); ?></div>
                    <div class="stat-label">Crysalon</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="ignisium"><?php echo number_format($resources['ignisium'] ?? 0); ?></div>
                    <div class="stat-label">Ignisium</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="veltrium"><?php echo ($resources['veltrium'] ?? 0); ?></div>
                    <div class="stat-label">Veltrium</div>
                </div>
            </div>
            <div class="update-status">
                <span class="status-indicator"></span>
                <span id="update-text">Real-time updates active</span>
                <span id="last-update">Last updated: <?php echo date('H:i:s'); ?></span>
            </div>
        </div>
    </div>
    <script>
        function updateResources() {
            // Add updating visual feedback
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.add('updating');
            });
            
            // Make AJAX request with better error handling
            const formData = new FormData();
            formData.append('player_id', '1');
            
            fetch('get_resources.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                console.log('Success flag:', data.success);
                console.log('Resources:', data.resources);
                
                if (data.success) {
                    // Update each resource value with smooth transition
                    document.getElementById('aetherite').textContent = data.resources.aetherite.toLocaleString();
                    document.getElementById('crysalon').textContent = data.resources.crysalon.toLocaleString();
                    document.getElementById('ignisium').textContent = data.resources.ignisium.toLocaleString();
                    document.getElementById('veltrium').textContent = data.resources.veltrium.toLocaleString();
                    
                    // Update timestamp
                    document.getElementById('last-update').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
                    document.getElementById('update-text').textContent = 'Real-time updates active';
                } else {
                    console.error('Server error:', data.error);
                    document.getElementById('update-text').textContent = 'Update failed - ' + (data.error || 'Unknown error');
                }
            }) // <-- Only one closing parenthesis here
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('update-text').textContent = 'Connection error: ' + error.message;
            })
            .finally(() => {
                // Remove updating visual feedback
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.remove('updating');
                });
            });
        }

        // Update resources every 1 second
        setInterval(updateResources, 1000);

        // Also update when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateResources();
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exodus Genesis - Space Exploration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e, #16213e);
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        .game-title {
            font-size: 3.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4ff, #ff0080, #ffed4e);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 3s ease-in-out infinite;
            text-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
        }

        .subtitle {
            font-size: 1.2rem;
            color: #b0b0b0;
            margin-top: 10px;
            opacity: 0.8;
        }

        .realtime-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 25px;
            text-align: center;
            color: #00d4ff;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 212, 255, 0.2);
            border-color: rgba(0, 212, 255, 0.5);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(45deg, #00d4ff, #ff0080);
            animation: pulse 2s infinite;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00d4ff;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.5);
            transition: all 0.3s ease;
        }

        .stat-label {
            font-size: 1rem;
            color: #b0b0b0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .update-status {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            font-size: 0.9rem;
            color: #888;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00ff88;
            margin-right: 8px;
            animation: statusPulse 2s infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes statusPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .updating {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="game-title">Exodus Genesis</h1>
            <p class="subtitle">Space Exploration 1.01</p>
        </div>
        
        <div class="realtime-section">
            <h2 class="section-title">Verbil's Resources</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="aetherite"><?php echo number_format($resources['aetherite'] ?? 0); ?></div>
                    <div class="stat-label">Aetherite</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="crysalon"><?php echo number_format($resources['crysalon'] ?? 0); ?></div>
                    <div class="stat-label">Crysalon</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="ignisium"><?php echo number_format($resources['ignisium'] ?? 0); ?></div>
                    <div class="stat-label">Ignisium</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="veltrium"><?php echo ($resources['veltrium'] ?? 0); ?></div>
                    <div class="stat-label">Veltrium</div>
                </div>
            </div>
            
            <div class="update-status">
                <span class="status-indicator"></span>
                <span id="update-text">Real-time updates active</span>
                <span id="last-update">Last updated: <?php echo date('H:i:s'); ?></span>
            </div>
        </div>
    </div>

    <script>
        function updateResources() {
            // Add updating visual feedback
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.add('updating');
            });
            
            // Make AJAX request with better error handling
            const formData = new FormData();
            formData.append('player_id', '1');
            
            fetch('get_resources.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                console.log('Success flag:', data.success);
                console.log('Resources:', data.resources);
                
                if (data.success) {
                    // Update each resource value with smooth transition
                    document.getElementById('aetherite').textContent = data.resources.aetherite.toLocaleString();
                    document.getElementById('crysalon').textContent = data.resources.crysalon.toLocaleString();
                    document.getElementById('ignisium').textContent = data.resources.ignisium.toLocaleString();
                    document.getElementById('veltrium').textContent = data.resources.veltrium.toLocaleString();
                    
                    // Update timestamp
                    document.getElementById('last-update').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
                    document.getElementById('update-text').textContent = 'Real-time updates active';
                } else {
                    console.error('Server error:', data.error);
                    document.getElementById('update-text').textContent = 'Update failed - ' + (data.error || 'Unknown error');
                }
            }) // <-- Only one closing parenthesis here
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('update-text').textContent = 'Connection error: ' + error.message;
            })
            .finally(() => {
                // Remove updating visual feedback
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.remove('updating');
                });
            });
        }

        // Update resources every 1 second
        setInterval(updateResources, 1000);

        // Also update when page becomes visible (user switches back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateResources();
            }
        });
    </script>
</body>
</html>