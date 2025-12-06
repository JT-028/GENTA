<?php
// Force clear cache and show config - DELETE AFTER USE
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

use Cake\Core\Configure;
use Cake\Cache\Cache;

echo "<h2>GENTA Cache Clear & Config Check</h2>";
echo "<hr>";

// STEP 1: Clear ALL caches
echo "<h3>Step 1: Clearing ALL Caches...</h3>";
$cacheConfigs = Cache::configured();
foreach ($cacheConfigs as $config) {
    try {
        Cache::clear($config);
        echo "<div style='color:green;'>✓ Cleared cache: $config</div>";
    } catch (\Exception $e) {
        echo "<div style='color:red;'>✗ Failed to clear $config: " . $e->getMessage() . "</div>";
    }
}

// Clear file-based cache manually
$tmpDirs = [
    '../tmp/cache/models',
    '../tmp/cache/persistent',
    '../tmp/cache/views',
];

foreach ($tmpDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "<div style='color:green;'>✓ Cleared files in: $dir</div>";
    }
}

echo "<hr>";

// STEP 2: Check current App.base value
echo "<h3>Step 2: Current App.base Configuration:</h3>";
$appBase = Configure::read('App.base');
echo "<pre>";
echo "App.base = ";
var_dump($appBase);
echo "</pre>";

if ($appBase === false || $appBase === null || $appBase === '') {
    echo "<div style='color:green; font-size:20px; font-weight:bold;'>✓ CORRECT! App.base is false/null (no GENTA prefix)</div>";
} else {
    echo "<div style='color:red; font-size:20px; font-weight:bold;'>✗ WRONG! App.base = '$appBase' (should be false)</div>";
    echo "<p><strong>ACTION REQUIRED:</strong> Edit /config/app_local.php on Cloudways and change 'base' => '$appBase' to 'base' => false</p>";
}

echo "<hr>";

// STEP 3: Test URL generation
echo "<h3>Step 3: URL Generation Test:</h3>";
$testUrl = \Cake\Routing\Router::url(['controller' => 'Teacher', 'action' => 'index'], true);
echo "<p>Generated URL for Teacher dashboard:</p>";
echo "<pre style='font-size:16px;'>" . htmlspecialchars($testUrl) . "</pre>";

if (strpos($testUrl, '/GENTA/') !== false) {
    echo "<div style='color:red; font-weight:bold;'>✗ URL contains /GENTA/ - STILL WRONG!</div>";
} else {
    echo "<div style='color:green; font-weight:bold;'>✓ URL does NOT contain /GENTA/ - CORRECT!</div>";
}

echo "<hr>";

// STEP 4: Check app_local.php file content
echo "<h3>Step 4: Check app_local.php File:</h3>";
$configFile = CONFIG . 'app_local.php';
if (file_exists($configFile)) {
    echo "<div style='color:green;'>✓ File exists: $configFile</div>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($configFile)) . "</p>";
    
    // Read and show the App section
    $content = file_get_contents($configFile);
    if (strpos($content, "'App'") !== false || strpos($content, '"App"') !== false) {
        echo "<div style='color:green;'>✓ File contains 'App' configuration section</div>";
        
        // Try to extract the App section
        if (preg_match("/'App'\s*=>\s*\[(.*?)\]/s", $content, $matches)) {
            echo "<p>App configuration found:</p>";
            echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
        }
    } else {
        echo "<div style='color:red;'>✗ File does NOT contain 'App' configuration section!</div>";
        echo "<p><strong>This is the problem!</strong> The 'App' section is missing from app_local.php</p>";
    }
} else {
    echo "<div style='color:red;'>✗ File NOT found: $configFile</div>";
}

echo "<hr>";

// STEP 5: Instructions
echo "<h3>Step 5: What to Do:</h3>";

$appBase = Configure::read('App.base');
if ($appBase !== false && $appBase !== null && $appBase !== '') {
    echo "<div style='background:#ffebee; padding:20px; border-left:4px solid #f44336;'>";
    echo "<h4 style='color:#c62828;'>⚠️ CONFIGURATION ISSUE FOUND</h4>";
    echo "<p><strong>Problem:</strong> App.base = '$appBase' (should be false)</p>";
    echo "<p><strong>Solution:</strong></p>";
    echo "<ol>";
    echo "<li>Open WinSCP and navigate to: <code>/applications/zfepxctexd/public_html/config/app_local.php</code></li>";
    echo "<li>Find the line: <code>'base' => '$appBase',</code></li>";
    echo "<li>Change it to: <code>'base' => false,</code></li>";
    echo "<li>Save the file</li>";
    echo "<li>Visit this page again to verify</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background:#e8f5e9; padding:20px; border-left:4px solid #4caf50;'>";
    echo "<h4 style='color:#2e7d32;'>✓ CONFIGURATION IS CORRECT!</h4>";
    echo "<p>App.base is set correctly. URLs should work now.</p>";
    echo "<p><strong>Try these:</strong></p>";
    echo "<ol>";
    echo "<li>Clear your browser cache (Ctrl + Shift + Delete)</li>";
    echo "<li>Try logging in again</li>";
    echo "<li>If still having issues, check the generated URL above</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#999;'><strong>DELETE THIS FILE after fixing the issue!</strong></p>";
?>