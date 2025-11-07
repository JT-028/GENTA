<?php
// Test what EncryptHelper actually outputs
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

use App\View\Helper\EncryptHelper;
use Cake\View\View;

$view = new View();
$encryptHelper = new EncryptHelper($view);

$testId = '14';
echo "Original ID: $testId\n";

$encrypted = $encryptHelper->hex($testId);
echo "Encrypted by helper: $encrypted\n";
echo "Length: " . strlen($encrypted) . " chars\n";

// Check if it's double-encoded by decoding once
$decoded = hex2bin($encrypted);
echo "After first hex2bin: " . substr($decoded, 0, 64) . "\n";
echo "First 64 chars is hex: " . (ctype_xdigit(substr($decoded, 0, 64)) ? 'YES - DOUBLE ENCODED!' : 'NO - correct') . "\n";
