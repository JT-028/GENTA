<?php
/**
 * Quick log viewer for debugging password reset
 * DELETE THIS FILE AFTER DEBUGGING!
 */

$logFile = dirname(__DIR__) . '/logs/error.log';

echo "<h2>GENTA Debug - Last 100 Log Lines</h2>";
echo "<p><strong>Log file:</strong> $logFile</p>";
echo "<hr>";

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    
    echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow: auto;'>";
    echo "<pre style='margin: 0; white-space: pre-wrap; word-wrap: break-word;'>";
    
    foreach ($lastLines as $line) {
        // Highlight different log levels
        if (strpos($line, 'error') !== false) {
            echo "<span style='color: red;'>$line</span>";
        } elseif (strpos($line, 'warning') !== false) {
            echo "<span style='color: orange;'>$line</span>";
        } elseif (strpos($line, 'info') !== false) {
            echo "<span style='color: green;'>$line</span>";
        } elseif (strpos($line, 'debug') !== false) {
            echo "<span style='color: blue;'>$line</span>";
        } else {
            echo $line;
        }
    }
    
    echo "</pre>";
    echo "</div>";
    
    // Filter for password reset related logs
    echo "<hr>";
    echo "<h3>Password Reset Related Logs:</h3>";
    $resetLines = array_filter($lastLines, function($line) {
        return stripos($line, 'password') !== false || 
               stripos($line, 'reset') !== false || 
               stripos($line, 'forgot') !== false ||
               stripos($line, 'token') !== false;
    });
    
    if (!empty($resetLines)) {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107;'>";
        echo "<pre style='margin: 0; white-space: pre-wrap; word-wrap: break-word;'>";
        foreach ($resetLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<p>No password reset related logs found.</p>";
    }
    
} else {
    echo "<p style='color: red;'>Log file not found: $logFile</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT: Delete this file after debugging for security!</strong></p>";
echo "<p><code>rm webroot/view_logs.php</code></p>";
?>
