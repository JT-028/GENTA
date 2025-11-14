<?php
// Temporary diagnostic: show which UsersController.php mtime/path and key env vars
// Visit: http://<host>/GENTA/which_users.php
header('Content-Type: text/plain');
$rel = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'UsersController.php';
$real = realpath($rel);
if ($real) {
    echo "UsersController path: " . $real . "\n";
    echo "mtime: " . @date('c', filemtime($real)) . "\n";
} else {
    echo "UsersController.php not found at expected path: " . $rel . "\n";
}

echo "\nEnvironment variables seen by PHP (empty means not set):\n";
echo "FLASK_PENDING_URL=" . (getenv('FLASK_PENDING_URL') ?: '<unset>') . "\n";
echo "FLASK_API_KEY=" . (getenv('FLASK_API_KEY') ?: '<unset>') . "\n";
echo "APP_BASE_URL=" . (getenv('APP_BASE_URL') ?: '<unset>') . "\n";

echo "\nPHP SAPI: " . php_sapi_name() . "\n";

?>