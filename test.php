<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speed Test Manager</title>
    <style>
        :root { --primary: #3498db; --success: #2ecc71; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 15px; }
        
        .card { 
            background: white; padding: 30px; border-radius: 15px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center;
        }

        .speed-display { margin: 20px 0; }
        .speed-value { font-size: 4rem; font-weight: bold; color: #2c3e50; line-height: 1; }
        .unit { font-size: 1.2rem; color: #7f8c8d; display: block; margin-top: 5px; }

        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 10px; border-radius: 10px; }
        .stat-label { font-size: 0.8rem; color: #95a5a6; text-transform: uppercase; }
        .stat-value { font-size: 1.1rem; font-weight: bold; display: block; }

        .progress-bar { width: 100%; height: 8px; background: #eee; border-radius: 4px; margin-bottom: 25px; overflow: hidden; }
        .progress-fill { width: 0%; height: 100%; background: var(--primary); transition: width 0.2s; }

        button { 
            background: var(--primary); color: white; border: none; padding: 15px; 
            border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1rem;
        }
        button:disabled { background: #bdc3c7; }
    </style>
</head>
<body>

<div class="card">
    <h2 id="status">Network Speed Tester</h2>
    
    <div class="speed-display">
        <span class="speed-value" id="live-display">0.0</span>
        <span class="unit">Mbps</span>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="progress-bar"></div>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <span class="stat-label">Download</span>
            <span class="stat-value" id="final-down">--</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Upload</span>
            <span class="stat-value" id="final-up">--</span>
        </div>
    </div>

    <button id="startBtn" onclick="runTest()">Start Speed Test</button>
    <br><br>
<center><p>Managed By Zin Yaw</p></center>
</div>

<script>
async function runTest() {
    const display = document.getElementById('live-display');
    const progress = document.getElementById('progress-bar');
    const btn = document.getElementById('startBtn');
    const status = document.getElementById('status');
    const downLabel = document.getElementById('final-down');
    const upLabel = document.getElementById('final-up');

    btn.disabled = true;
    status.innerText = "Testing...";
    
    // --- DOWNLOAD TEST ---
    const downloadSizeMb = 20; 
    let receivedBytes = 0;
    const startTime = performance.now();

    try {
        const response = await fetch(`speedtest_api.php?action=download&size=${downloadSizeMb}&t=${Date.now()}`);
        
        if (!response.ok) throw new Error("API file not found (404). Ensure speedtest_api.php is in the same folder.");

        const reader = response.body.getReader();

        while (true) {
            const {done, value} = await reader.read();
            if (done) break;
            receivedBytes += value.length;
            
            const duration = (performance.now() - startTime) / 1000;
            if (duration > 0.1) {
                const speedMbps = (receivedBytes * 8 / duration) / 1048576;
                display.innerText = speedMbps.toFixed(1);
                progress.style.width = ((receivedBytes / (downloadSizeMb * 1048576)) * 100) + "%";
            }
        }
        downLabel.innerText = display.innerText + " Mbps";
    } catch (e) {
        alert(e.message);
        btn.disabled = false;
        return;
    }

    // --- UPLOAD TEST ---
    progress.style.width = "0%";
    const uploadSizeMb = 5;
    const uploadData = new Uint8Array(uploadSizeMb * 1048576);
    
    const upStartTime = performance.now();
    try {
        await fetch('speedtest_api.php?action=upload', { method: 'POST', body: uploadData });
        const upDuration = (performance.now() - upStartTime) / 1000;
        const upSpeed = (uploadSizeMb * 8 / upDuration);
        
        display.innerText = upSpeed.toFixed(1);
        upLabel.innerText = display.innerText + " Mbps";
        progress.style.width = "100%";
    } catch (e) {
        console.error("Upload failed");
    }
    
    status.innerText = "Test Complete";
    btn.disabled = false;
    btn.innerText = "Retest";
}
</script>

</body>
</html>