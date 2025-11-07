<?php
// Temporary test file - delete after testing
require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Utility\Security;

// Use the ACTUAL app salt
Security::setSalt('ded7e756378faccdc885dac6cf43e2b85e3fbf06185d57efedc807886003ee9c');

$testId = '14';  // Use the actual ID from the browser test
$key = 'e22ddfe8ecb5356550aff2a23b70b35e';

echo "Testing encryption/decryption with hex (treating all as binary):\n";
echo "Original ID: $testId\n\n";

// Test encryption
$encrypted = Security::encrypt($testId, $key);
echo "Encrypted length: " . strlen($encrypted) . " bytes\n";
echo "First 48 chars: " . substr($encrypted, 0, 48) . "\n";
echo "First 48 is hex: " . (ctype_xdigit(substr($encrypted, 0, 48)) ? 'YES' : 'NO') . "\n\n";

// Test hex encoding - treat ALL 96 bytes as binary (even though first 48 are hex chars)
$hexEncoded = bin2hex($encrypted);
echo "Hex encoded length: " . strlen($hexEncoded) . " chars\n";
echo "Hex encoded: " . $hexEncoded . "\n\n";

// Test decryption
$decoded = hex2bin($hexEncoded);
echo "Decoded length: " . strlen($decoded) . " bytes\n";
$decrypted = Security::decrypt($decoded, $key);
echo "Decrypted: $decrypted\n";
echo "Match: " . ($testId === $decrypted ? 'YES ✓' : 'NO ✗') . "\n";
