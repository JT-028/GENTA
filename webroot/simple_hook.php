<?php
// Minimal webhook test endpoint to log raw request and headers for debugging.
// Writes to GENTA/tmp/hook_requests.log
try {
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'hook_requests.log';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $headers = [];
    // getallheaders may not be available in some SAPIs; fall back
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
                $headers[$name] = $v;
            }
        }
    }

    $raw = file_get_contents('php://input');

    $entry = [
        'time' => date('c'),
        'method' => $method,
        'uri' => $uri,
        'headers' => $headers,
        'body' => $raw,
    ];

    file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // Respond with a simple JSON acknowledgement
    header('Content-Type: application/json');
    echo json_encode(['received' => true, 'ts' => time()]);
} catch (\Throwable $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}

