<?php
require 'vendor/autoload.php';

use Cake\Utility\Security;

// Set salt directly
Security::setSalt('ded7e756378faccdc885dac6cf43e2b85e3fbf06185d57efedc807886003ee9c');

$key = 'e22ddfe8ecb5356550aff2a23b70b35e';

// Test decrypting the NEW ID from the console logs
$hex = '984867d6bef9d589324a99ef3c378ac7bb7130bb92efb7e3363633663235303434316236326236335df52493640f1321e99e827335306c948cf9eb328165137e0c38ef7e9ae5ed87';

echo "Input hex length: " . strlen($hex) . "\n";

// Decrypt
$hmac = substr($hex, 0, 48);
$ciphertextHex = substr($hex, 48);
$ciphertext = hex2bin($ciphertextHex);
$reconstructed = $hmac . $ciphertext;
$decrypted = Security::decrypt($reconstructed, $key);

if ($decrypted === false) {
    echo "ERROR: Decryption failed!\n";
} else {
    echo "Decrypted to: $decrypted\n";
}

// Now check if question 30 exists
require 'config/bootstrap.php';
use Cake\ORM\TableRegistry;
$table = TableRegistry::getTableLocator()->get('Questions');
try {
    $q = $table->get(30);
    echo "Question 30 EXISTS: Status=" . $q->status . "\n";
} catch (Exception $e) {
    echo "Question 30 NOT FOUND\n";
}


