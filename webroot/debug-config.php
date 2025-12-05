<?php
// Debug script to check App.base configuration
// DELETE THIS FILE AFTER DEBUGGING
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

use Cake\Core\Configure;

echo "<h2>GENTA Configuration Debug</h2>";
echo "<hr>";

echo "<h3>1. App Configuration:</h3>";
echo "<pre>";
echo "App.base: ";
var_dump(Configure::read('App.base'));
echo "\nApp.fullBaseUrl: ";
var_dump(Configure::read('App.fullBaseUrl'));
echo "\nApp.callbackSecret: ";
var_dump(Configure::read('App.callbackSecret'));
echo "</pre>";

echo "<hr>";

echo "<h3>2. URL Generation Test:</h3>";
$router = \Cake\Routing\Router::url(['controller' => 'Teacher', 'action' => 'index'], true);
echo "<p>Router::url(['controller' => 'Teacher', 'action' => 'index'], true):</p>";
echo "<pre>" . htmlspecialchars($router) . "</pre>";

echo "<hr>";

echo "<h3>3. Environment Variables:</h3>";
echo "<pre>";
echo "FULL_BASE_URL: " . (getenv('FULL_BASE_URL') ?: 'not set') . "\n";
echo "CALLBACK_SECRET: " . (getenv('CALLBACK_SECRET') ?: 'not set') . "\n";
echo "</pre>";

echo "<hr>";

echo "<h3>4. Config File Path:</h3>";
echo "<p>Check if app_local.php is being loaded:</p>";
$configPath = CONFIG . 'app_local.php';
if (file_exists($configPath)) {
    echo "<div style='color:green;'>✅ app_local.php exists at: $configPath</div>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($configPath)) . "</p>";
} else {
    echo "<div style='color:red;'>❌ app_local.php NOT FOUND at: $configPath</div>";
}

echo "<hr>";

echo "<h3>5. All App.* Configuration:</h3>";
echo "<pre>";
print_r(Configure::read('App'));
echo "</pre>";

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER DEBUGGING!</strong></p>";
?>