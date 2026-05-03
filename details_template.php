<?php
/* details_template.php */
require_once 'functions.php';

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
<style>
/* Style for the second independent card */
.identity-container {
    background: #fff;
    padding: 10px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
    margin-top: 20px; /* Space between the two cards */
    box-sizing: border-box;
}

.details-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    border-bottom: 1px solid #f0f2f5;
    padding-bottom: 10px;
    text-align: left;
}

.details-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #1a202c;
}

.status-dot {
    width: 10px;
    height: 10px;
    background: #cbd5e0;
    border-radius: 50%;
    transition: background 0.3s;
}

.status-dot.online { background: #48bb78; box-shadow: 0 0 8px #48bb78; }

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 20px;
    text-align: left;
}

.detail-item .label {
    display: block;
    font-size: 0.75rem;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.detail-item .value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d3748;
    word-break: break-all;
}
</style>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Status - <?php echo htmlspecialchars($data['name']); ?></title>
</head>
<body style="flex-direction: column; align-items: center; display: flex;">

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
</div>

<div class="identity-container">
    <div class="details-header">
        <div class="status-dot" id="connection-status"></div>
        <h3>Connection Identity</h3>
    </div>
    
    <div class="details-grid">
        <div class="detail-item">
            <span class="label">Public IP</span>
            <span class="value" id="ip-address">Detecting...</span>
        </div>
        <div class="detail-item">
            <span class="label">Internet Provider</span>
            <span class="value" id="isp-name">Detecting...</span>
        </div>
        <div class="detail-item">
            <span class="label">Geo Location</span>
            <span class="value" id="geo-location">Detecting...</span>
        </div>
    </div>
</div>

<p style="margin-top: 30px; font-size: 0.7rem; color: #aaa;">Managed by Zin Yaw</p>

<script>
function copyKey() {
    var copyText = document.getElementById("clientKey");
    copyText.select();
    document.execCommand("copy");
    alert("Key copied to clipboard!");
}

async function updateConnectionDetails() {
    const ipVal = document.getElementById('ip-address');
    const ispVal = document.getElementById('isp-name');
    const locVal = document.getElementById('geo-location');
    const statusDot = document.getElementById('connection-status');

    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();

        if (!data.error) {
            ipVal.innerText = data.ip;
            ispVal.innerText = data.org;
            locVal.innerText = `${data.city}, ${data.country_name}`;
            statusDot.classList.add('online');
        } else {
            throw new Error('API Error');
        }
    } catch (err) {
        ipVal.innerText = "Check Connection";
        ispVal.innerText = "Offline/VPN Blocked";
        locVal.innerText = "Unknown";
        statusDot.style.background = "#f56565";
    }
}

document.addEventListener('DOMContentLoaded', updateConnectionDetails);
</script>
</body>
</html>