<?php 
require_once 'functions.php';
checkLogin();
checkAndCleanExpiredKeys();

date_default_timezone_set('Asia/Bangkok');

// Determine Greeting
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} elseif ($hour >= 17 && $hour < 21) {
    $greeting = "Good Evening";
} else {
    $greeting = "Good Night";
}

$currentDateTime = date('l, F j, Y | g:i A');

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

$history = array_reverse(getFullHistory(), true);
$usageData = getLiveUsage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Outline VPN Manager</title>
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --secondary-text: #666666;
            --border-color: #dddddd;
            --input-bg: #ffffff;
            --switch-bg: #ccc;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-color: #f0f0f0;
            --secondary-text: #aaaaaa;
            --border-color: #444444;
            --input-bg: #3d3d3d;
            --switch-bg: #27ae60;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        .container {
            background-color: var(--card-bg);
            color: var(--text-color);
            padding: 20px;
            max-width: 600px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        /* Toggle Switch Styling */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: #27ae60; }
        input:checked + .slider:before { transform: translateX(26px); }

        .header-welcome {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .header-welcome h1 { margin: 0; font-size: 1.4rem; }
        .header-welcome p { margin: 5px 0 0 0; font-size: 0.85rem; color: var(--secondary-text); }

        input, #clientSearch { background-color: var(--input-bg); color: var(--text-color); border: 1px solid var(--border-color); }
        .history-item { background-color: var(--card-bg); border: 1px solid var(--border-color); margin-bottom: 15px; padding: 15px; border-radius: 8px; position: relative; }
        
        .client-link {
            font-size: 0.75rem;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            display: block;
            margin: 5px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <label class="switch">
            <input type="checkbox" id="themeToggle" onclick="toggleTheme()">
            <span class="slider"></span>
        </label>
    </div>

    <div class="header-welcome">
        <h1><?php echo $greeting; ?>!</h1>
        <p><?php echo $currentDateTime; ?></p>
    </div>

    <h2>Outline VPN Manager</h2>
    <form method="POST">
        <input type="text" name="key_name" placeholder="Client Name" required style="width:100%; padding:10px; margin-bottom:10px; border-radius: 6px;">
        <input type="number" step="0.1" name="data_limit" placeholder="Limit (GB)" required style="width:100%; padding:10px; margin-bottom:10px; border-radius: 6px;">
        <label style="display:block; margin-bottom:5px; font-size:0.8rem;">Expire Date:</label>
        <input type="date" name="expire_date" required style="width:100%; padding:10px; margin-bottom:10px; border-radius: 6px;">
        <button type="submit" name="generate" class="btn-generate">Generate Key</button>
    </form>

    <h3 style="margin-top:20px;">Clients Usage</h3>
    <div class="search-container" style="margin: 15px 0;">
        <input type="text" id="clientSearch" onkeyup="filterClients()" placeholder="Search by client name..." style="width: 100%; padding: 10px; border-radius: 6px; box-sizing: border-box;">
    </div>

    <div id="clientList">
        <?php if (!empty($history)): ?>
            <?php foreach ($history as $id => $data): 
                $used = $usageData[$id] ?? 0;
                $limit = $data['limit'] ?? 1;
                $percent = min(100, ($used / $limit) * 100);
                $clientName = htmlspecialchars($data['name']);
            ?>
                <div class="history-item client-card" data-name="<?php echo strtolower($clientName); ?>">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                        <button class="delete-btn" onclick="return confirm('Delete key and client file?')">Delete</button>
                    </form>
                    <strong><?php echo $clientName; ?></strong>
                    
                    <a href="<?php echo $data['client_file']; ?>" target="_blank" class="client-link">
                        View Page: /<?php echo $data['client_file']; ?>
                    </a>

                    <div class="progress-container" style="background:#eee; height:10px; border-radius:5px; margin:10px 0;">
                        <div style="width: <?php echo $percent; ?>%; background: #27ae60; height:100%; border-radius:5px;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.7rem;">
                        <span>Used: <?php echo round($used/1000000000, 2); ?> GB / <?php echo $limit/1000000000; ?> GB</span>
                        <span>Exp: <?php echo $data['expire_date'] ?? 'N/A'; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No keys found.</p>
        <?php endif; ?>
    </div>
    
    <center style="margin-top:20px;"><p style="font-size:0.8rem; color:var(--secondary-text);">Managed By Zin Yaw</p></center>
</div>

<script>
function filterClients() {
    const filter = document.getElementById('clientSearch').value.toLowerCase();
    const cards = document.getElementsByClassName('client-card');
    for (let card of cards) {
        card.style.display = card.getAttribute('data-name').includes(filter) ? "" : "none";
    }
}

function toggleTheme() {
    const body = document.body;
    const checkbox = document.getElementById('themeToggle');
    if (checkbox.checked) {
        body.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    } else {
        body.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    const checkbox = document.getElementById('themeToggle');
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        checkbox.checked = true;
    }
});
</script>
</body>
</html>