<?php 
require_once 'functions.php';
checkLogin();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Outline VPN manager</title>
</head>
<body>
<div class="container">
    <h2>Outline VPN Manager</h2>
    <form method="POST">
        <input type="text" name="key_name" placeholder="Client Name" required>
        <input type="number" step="0.1" name="data_limit" placeholder="Limit (GB)" required>
        <label style="font-size: 0.8rem; color: #666;">Expiration Date:</label>
        <input type="date" name="expire_date" required>
        <button type="submit" name="generate" class="btn-generate">Generate Key</button>
    </form>

    <h3>Clients Usage</h3>
    <?php foreach ($history as $id => $data): 
        $used = $usageData[$id] ?? 0;
        $limit = $data['limit'] ?? 1;
        $percent = min(100, ($used / $limit) * 100);
        $keyId = "key-" . $id;
        
        // Expiration Styling
        $expire_date = $data['expire_date'] ?? 'No Date';
        $is_expired = (strtotime($expire_date) < strtotime(date('Y-m-d')));
        $date_color = $is_expired ? 'color: red; font-weight: bold;' : 'color: #27ae60;';
    ?>
        <div class="history-item" style="<?php echo $is_expired ? 'border-left: 4px solid #e74c3c;' : ''; ?>">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                <button class="delete-btn">Delete</button>
            </form>
            <strong><?php echo htmlspecialchars($data['name']); ?></strong>
            
            <div class="progress-container">
                <div class="progress-fill" style="width: <?php echo $percent; ?>%; background: #27ae60;"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 5px;">
                <span>Used: <?php echo round($used/1000000000, 2); ?> GB / <?php echo $limit/1000000000; ?> GB</span>
                <span style="<?php echo $date_color; ?>">Expires: <?php echo $expire_date; ?></span>
            </div>
            
            <input type="text" id="<?php echo $keyId; ?>" value="<?php echo htmlspecialchars($data['url']); ?>" readonly style="margin-top: 5px; font-size: 0.7rem;">
            <button onclick="copySpecificKey('<?php echo $keyId; ?>')" style="padding: 5px; font-size: 0.7rem; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Copy this Key</button>
        </div>
    <?php endforeach; ?>
    <br>
    <center><p>Managed By Zin Yaw</p></center>
</div>

<script>
function copySpecificKey(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    document.execCommand("copy");
    alert("Key copied to clipboard!");
}
</script>
</body>
</html>