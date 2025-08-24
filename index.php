<?php
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
    <!-- Login Status Bar -->
    <div id="login-status-bar" style="position:fixed;top:20px;right:30px;z-index:1000;text-align:right;">
        <?php
        session_start();
        if (isset($_SESSION['username'])) {
            $display_name = $_SESSION['display_name'] ?? $_SESSION['username'];
            echo '<span style="color:#00ff88;font-weight:bold;"><span class="status-indicator"></span>' . htmlspecialchars($display_name) . '</span> ';
            echo '<button id="logout-btn" style="margin-left:10px;padding:5px 12px;">Logout</button>';
        } else {
            echo '<button id="login-btn" style="padding:5px 12px;">Login</button>';
        }
        ?>
    </div>

    <!-- Login Modal -->
    <div id="login-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);z-index:2000;align-items:center;justify-content:center;">
        <div style="background:#222;padding:30px 40px;border-radius:12px;box-shadow:0 8px 32px #000;min-width:300px;max-width:90vw;">
            <h2 style="color:#00d4ff;margin-bottom:18px;">Login</h2>
            <form id="login-form">
                <input type="text" name="username" placeholder="Username" required style="width:100%;margin-bottom:12px;padding:8px;">
                <input type="password" name="password" placeholder="Password" required style="width:100%;margin-bottom:18px;padding:8px;">
                <button type="submit" style="width:100%;padding:8px 0;background:#00d4ff;color:#fff;border:none;border-radius:6px;font-weight:bold;">Login</button>
            </form>
            <div id="login-error" style="color:#ff4e4e;margin-top:10px;display:none;"></div>
        </div>
    </div>

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
    // Login Modal Logic
    const loginBtn = document.getElementById('login-btn');
    const logoutBtn = document.getElementById('logout-btn');
    const loginModal = document.getElementById('login-modal');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');

    if (loginBtn) {
        loginBtn.onclick = function() {
            loginModal.style.display = 'flex';
        };
    }
    if (logoutBtn) {
        logoutBtn.onclick = function() {
            fetch('logout.php', { method: 'POST' })
                .then(r => r.json())
                .then(data => { window.location.reload(); });
        };
    }
    if (loginModal) {
        loginModal.onclick = function(e) {
            if (e.target === loginModal) loginModal.style.display = 'none';
        };
    }
    if (loginForm) {
        loginForm.onsubmit = function(e) {
            e.preventDefault();
            loginError.style.display = 'none';
            const formData = new FormData(loginForm);
            fetch('login.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loginModal.style.display = 'none';
                        window.location.reload();
                    } else {
                        loginError.textContent = data.error || 'Login failed.';
                        loginError.style.display = 'block';
                    }
                })
                .catch(() => {
                    loginError.textContent = 'Server error.';
                    loginError.style.display = 'block';
                });
        };
    }
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
    </script>
</body>
</html>