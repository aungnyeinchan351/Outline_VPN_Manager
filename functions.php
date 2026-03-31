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
define('OUTLINE_API_URL', 'https://.............');
define('HISTORY_FILE', 'key_history.json');

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

    $history = getFullHistory();
    $history[$id] = [
        'name' => $name, 
        'limit' => $limitGb * 1000000000, 
        'url' => $res['accessUrl'], 
        'date' => date('Y-m-d H:i'),
        'expire_date' => $expireDate // New Field
    ];
    file_put_contents(HISTORY_FILE, json_encode($history));
    return $res['accessUrl'];
}

function deleteOutlineKey($id) {
    apiRequest("/access-keys/$id", 'DELETE');
    $history = getFullHistory();
    if (isset($history[$id])) {
        unset($history[$id]);
        file_put_contents(HISTORY_FILE, json_encode($history));
    }
}

// Automatically delete keys that have passed their expiration date
function checkAndCleanExpiredKeys() {
    $history = getFullHistory();
    $today = date('Y-m-d');
    $changed = false;

    foreach ($history as $id => $data) {
        if (isset($data['expire_date']) && $data['expire_date'] < $today) {
            deleteOutlineKey($id);
            $changed = true;
        }
    }
    
    // Refresh history after cleaning
    if ($changed) {
        return getFullHistory();
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

// Logic for generating key
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
    generateOutlineKey($_POST['key_name'], $_POST['data_limit'], $_POST['expire_date']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Logic for deleting
if (isset($_POST['delete_id'])) { 
    deleteOutlineKey($_POST['delete_id']); 
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Run the auto-cleaner every time the page loads
checkAndCleanExpiredKeys();

$history = array_reverse(getFullHistory(), true);
$usageData = getLiveUsage();
?>