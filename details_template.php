<?php
/* details_template.php */
require_once 'functions.php';

// The $keyId is passed from the individual client file (e.g., zinyaw.php)
if (!isset($keyId)) {
    die("Access Denied.");
}

$history = getFullHistory();
if (!isset($history[$keyId])) {
    die("<div style='text-align:center; padding:50px;'><h2>Key Expired or Deleted</h2></div>");
}

$data = $history[$keyId];
$usageData = getLiveUsage();
$used = $usageData[$keyId] ?? 0;
$limit = $data['limit'] ?? 1;
$percent = min(100, ($used / $limit) * 100);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Status - <?php echo htmlspecialchars($data['name']); ?></title>
</head>
<body>
<div class="container" style="text-align: center;">
    <h2>Client Usage Details</h2>
    <hr>
    <h1 style="margin: 10px 0;"><?php echo htmlspecialchars($data['name']); ?></h1>
    
    <div class="progress-container" style="height: 20px;">
        <div class="progress-fill" style="width: <?php echo $percent; ?>%; background: #27ae60;"></div>
    </div>
    
    <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 20px;">
        <span>Used: <?php echo round($used/1000000000, 2); ?> GB</span>
        <span>Limit: <?php echo $limit/1000000000; ?> GB</span>
    </div>

    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">
        <p style="margin: 0; font-size: 0.8rem; color: #666;">EXPIRATION DATE</p>
        <h3 style="margin: 5px 0; color: #e74c3c;"><?php echo $data['expire_date']; ?></h3>
    </div>

    <div style="text-align: left; margin-top: 20px;">
        <label style="font-size: 0.8rem; color: #666;">Access Key:</label>
        <textarea id="clientKey" readonly style="height: 70px; font-size: 0.7rem;"><?php echo htmlspecialchars($data['url']); ?></textarea>
        <button onclick="copyKey()" class="btn-generate">Copy Key</button><br>
        <a href="test.php"><button class="btn-generate">Test Network Speed</button></a>
    </div>

    <p style="margin-top: 30px; font-size: 0.7rem; color: #aaa;">Managed by Zin Yaw</p>
</div>

<script>
function copyKey() {
    var copyText = document.getElementById("clientKey");
    copyText.select();
    document.execCommand("copy");
    alert("Key copied to clipboard!");
}
</script>
</body>
</html>