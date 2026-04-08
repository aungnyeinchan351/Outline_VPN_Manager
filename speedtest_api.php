<?php
// speedtest_api.php
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Increase limits for large tests
ini_set('memory_limit', '512M');
set_time_limit(60);

$action = $_GET['action'] ?? '';

if ($action === 'download') {
    // Send raw data in 64KB chunks to be memory efficient
    $sizeInMb = isset($_GET['size']) ? (int)$_GET['size'] : 20;
    $chunkSize = 64 * 1024; // 64KB
    $totalChunks = ($sizeInMb * 1024 * 1024) / $chunkSize;

    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . ($sizeInMb * 1024 * 1024));

    for ($i = 0; $i < $totalChunks; $i++) {
        echo str_repeat('0', $chunkSize);
        // Flush buffer to ensure data is sent immediately
        if (ob_get_level() > 0) ob_flush();
        flush();
    }
    exit;
}

if ($action === 'upload') {
    $input = fopen("php://input", "r");
    $count = 0;
    while ($data = fread($input, 8192)) {
        $count += strlen($data);
    }
    fclose($input);
    echo json_encode(['received' => $count]);
    exit;
}