<?php 
require_once 'functions.php';
checkLogin();

checkAndCleanExpiredKeys();
// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
    generateOutlineKey($_POST['key_name'], $_POST['data_limit'], $_POST['expire_date']);
    header("Location: index.php");
    exit();
}

// Handle Deletion
if (isset($_POST['delete_id'])) { 
    deleteOutlineKey($_POST['delete_id']); 
    header("Location: index.php");
    exit();
}

// DEFINING VARIABLES BEFORE THE LOOP
$history = array_reverse(getFullHistory(), true);
$usageData = getLiveUsage();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Outline VPN Manager</title>
</head>
<body>
<div class="container">
    <h2>Outline VPN Manager</h2>
    <form method="POST">
        <input type="text" name="key_name" placeholder="Client Name" required>
        <input type="number" step="0.1" name="data_limit" placeholder="Limit (GB)" required>
        <label style="display:block; margin-bottom:5px; font-size:0.8rem; color:#666;">Expire Date:</label>
        <input type="date" name="expire_date" required>
        <button type="submit" name="generate" class="btn-generate">Generate Key</button>
    </form>

    <h3>Clients Usage</h3>
    <?php if (!empty($history)): ?>
        <?php foreach ($history as $id => $data): 
            $used = $usageData[$id] ?? 0;
            $limit = $data['limit'] ?? 1;
            $percent = min(100, ($used / $limit) * 100);
            $keyId = "key-" . $id;
        ?>
            <div class="history-item">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                    <button class="delete-btn" onclick="return confirm('Delete key and client file?')">Delete</button>
                </form>
                <strong><?php echo htmlspecialchars($data['name']); ?></strong>
                
                <div style="margin: 5px 0;">
                    <a href="<?php echo $data['client_file']; ?>" target="_blank" style="font-size: 0.75rem; color: #3498db; text-decoration: none; font-weight: bold;">
                        View Page: /<?php echo $data['client_file']; ?>
                    </a>
                </div>

                <div class="progress-container">
                    <div class="progress-fill" style="width: <?php echo $percent; ?>%; background: #27ae60;"></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.7rem; color:#666;">
                    <span>Used: <?php echo round($used/1000000000, 2); ?> GB / <?php echo $limit/1000000000; ?> GB</span>
                    <span>Exp: <?php echo $data['expire_date'] ?? 'N/A'; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; color:#999;">No keys found.</p>
    <?php endif; ?>
    <br>
    <center><p>Managed By Zin Yaw</p></center>
</div>
</body>
</html>