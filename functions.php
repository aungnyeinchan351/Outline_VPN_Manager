<?php
/* functions.php */
session_start();

function checkLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}

// Ensure these match your actual server details
define('OUTLINE_API_URL', 'https://3.0.43.43:41079/_Z0Qmza1Wey1YPwJWhEXTw'); // [cite: 2]
define('HISTORY_FILE', 'key_history.json'); // [cite: 2]

// Helper to convert names like "Zin Yaw" to "zinyaw"
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function apiRequest($endpoint, $method = 'GET', $payload = null) {
    $ch = curl_init(OUTLINE_API_URL . $endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($payload) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($payload) ? http_build_query($payload) : $payload);
        if (!is_array($payload)) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function generateOutlineKey($name, $limitGb, $expireDate) {
    $res = apiRequest('/access-keys', 'POST');
    if (!isset($res['id'])) return false;

    $id = $res['id'];
    apiRequest("/access-keys/$id/name", 'PUT', ['name' => $name]);
    apiRequest("/access-keys/$id/data-limit", 'PUT', json_encode(["limit" => ["bytes" => $limitGb * 1000000000]]));

    $slug = slugify($name);
    $filename = $slug . ".php";

    $history = getFullHistory();
    $history[$id] = [
        'name' => $name, 
        'limit' => $limitGb * 1000000000, 
        'url' => $res['accessUrl'], 
        'date' => date('Y-m-d H:i'),
        'expire_date' => $expireDate,
        'client_file' => $filename // Store filename for later reference
    ];
    file_put_contents(HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT));

    // Create the individual client file automatically
    $content = "<?php \$keyId = '$id'; include 'details_template.php'; ?>";
    file_put_contents($filename, $content);

    return $res['accessUrl'];
}

function deleteOutlineKey($id) {
    $history = getFullHistory();
    if (isset($history[$id])) {
        // Delete the physical .php file if it exists
        if (!empty($history[$id]['client_file']) && file_exists($history[$id]['client_file'])) {
            unlink($history[$id]['client_file']);
        }
        
        apiRequest("/access-keys/$id", 'DELETE');
        unset($history[$id]);
        file_put_contents(HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT));
    }
}

function checkAndCleanExpiredKeys() {
    $history = getFullHistory();
    $today = date('Y-m-d');
    $changed = false;

    foreach ($history as $id => $data) {
        // If the expire date is in the past
        if (isset($data['expire_date']) && !empty($data['expire_date']) && $data['expire_date'] < $today) {
            
            // 1. Delete the physical .php file
            if (!empty($data['client_file']) && file_exists($data['client_file'])) {
                unlink($data['client_file']);
            }
            
            // 2. Delete from Outline API
            apiRequest("/access-keys/$id", 'DELETE');
            
            // 3. Remove from history array
            unset($history[$id]);
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents(HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT));
    }
    return $history;
}

function getFullHistory() {
    return file_exists(HISTORY_FILE) ? json_decode(file_get_contents(HISTORY_FILE), true) : [];
}

function getLiveUsage() {
    $metrics = apiRequest('/metrics/transfer');
    return $metrics['bytesTransferredByUserId'] ?? [];
}