<?php
// Debug endpoint to test encryption in web context
// Access via: http://localhost:8765/debug_encrypt.php

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

use Cake\Utility\Security;

header('Content-Type: text/plain');

$testId = '14';
$key = 'e22ddfe8ecb5356550aff2a23b70b35e';

$enc = Security::encrypt($testId, $key);

echo "=== NEW APPROACH: Split HMAC and ciphertext ===\n\n";
echo "Security::encrypt() output:\n";
echo "Length: " . strlen($enc) . " bytes\n";
echo "First 48 chars (HMAC): " . substr($enc, 0, 48) . "\n";
echo "HMAC is hex: " . (ctype_xdigit(substr($enc, 0, 48)) ? 'YES' : 'NO') . "\n";
echo "Last 48 bytes (ciphertext): [binary data]\n\n";

// NEW approach: Only encode the binary part
$hmac = substr($enc, 0, 48);
$ciphertext = substr($enc, 48);
$encoded = $hmac . bin2hex($ciphertext);

echo "After encoding (HMAC kept + ciphertext to hex):\n";
echo "Length: " . strlen($encoded) . " chars (48 + 96 = 144)\n";
echo "First 48: " . substr($encoded, 0, 48) . "\n";
echo "Next 96: " . substr($encoded, 48, 96) . "\n\n";

// Test decryption
$hmacDecoded = substr($encoded, 0, 48);
$ciphertextHex = substr($encoded, 48);
$ciphertextBin = hex2bin($ciphertextHex);
$reconstructed = $hmacDecoded . $ciphertextBin;

echo "After decoding:\n";
echo "Reconstructed length: " . strlen($reconstructed) . " bytes\n";
echo "Matches original: " . ($reconstructed === $enc ? 'YES ✓' : 'NO ✗') . "\n";

$decrypted = Security::decrypt($reconstructed, $key);
echo "Decrypted value: " . $decrypted . "\n";
echo "Matches input: " . ($decrypted === $testId ? 'YES ✓' : 'NO ✗') . "\n";
