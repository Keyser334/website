<?php
// Database configuration
$host = '127.0.0.1';
$dbname = 'mcg_test';
$username = 'clyde';
$password = 'PurpleHorse@01';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $player_id = 1;
    
    $stmt = $pdo->prepare("SELECT resource_type, quantity FROM player_resources WHERE player_id = ? AND resource_type IN (1,2,3,4)");
    $stmt->execute([$player_id]);
    $results = $stmt->fetchAll();
    
    $resources = ['aetherite' => 0, 'crysalon' => 0, 'ignisium' => 0, 'veltrium' => 0];
    
    foreach ($results as $row) {
        switch ($row['resource_type']) {
            case 1: $resources['aetherite'] = (int)$row['quantity']; break;
            case 2: $resources['crysalon'] = (int)$row['quantity']; break;
            case 3: $resources['ignisium'] = (int)$row['quantity']; break;
            case 4: $resources['veltrium'] = (int)$row['quantity']; break;
        }
    }
} catch(Exception $e) {
    $resources = ['aetherite' => 0, 'crysalon' => 0, 'ignisium' => 0, 'veltrium' => 0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; background: #222; color: white; padding: 20px; }
        .resource { margin: 10px; padding: 10px; background: #444; border-radius: 5px; }
        .value { font-size: 24px; color: #0ff; }
    </style>
</head>
<body>
    <h1>Resource Test</h1>
    
    <div class="resource">
        <div>Aetherite: <span class="value" id="aetherite"><?php echo $resources['aetherite']; ?></span></div>
    </div>
    <div class="resource">
        <div>Crysalon: <span class="value" id="crysalon"><?php echo $resources['crysalon']; ?></span></div>
    </div>
    <div class="resource">
        <div>Ignisium: <span class="value" id="ignisium"><?php echo $resources['ignisium']; ?></span></div>
    </div>
    <div class="resource">
        <div>Veltrium: <span class="value" id="veltrium"><?php echo $resources['veltrium']; ?></span></div>
    </div>
    
    <div id="status" style="margin-top: 20px; color: #0f0;">Ready</div>
    
    <script>
        function updateResources() {
            document.getElementById('status').textContent = 'Updating...';
            
            var formData = new FormData();
            formData.append('player_id', '1');
            
		fetch('get_resources.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'player_id=1'
		})
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                console.log('Data received:', data);
                if (data.success) {
                    document.getElementById('aetherite').textContent = data.resources.aetherite;
                    document.getElementById('crysalon').textContent = data.resources.crysalon;
                    document.getElementById('ignisium').textContent = data.resources.ignisium;
                    document.getElementById('veltrium').textContent = data.resources.veltrium;
                    document.getElementById('status').textContent = 'Updated: ' + new Date().toLocaleTimeString();
                } else {
                    document.getElementById('status').textContent = 'Error: ' + data.error;
                }
            })
            .catch(function(error) {
                console.log('Error:', error);
                document.getElementById('status').textContent = 'Connection error';
            });
        }
        
        // Test once on load
        setTimeout(updateResources, 1000);
        
        // Then every 3 seconds
        setInterval(updateResources, 3000);
    </script>
</body>
</html>