<?php
// Debug script to check verification token in database
// DELETE THIS FILE AFTER DEBUGGING
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/bootstrap.php';

use Cake\ORM\TableRegistry;

$token = '7458a3e3b5aa7d629148e7aafacd9b437632a0439ef5584ce3cc5bc9aae93b54';

echo "<h2>GENTA Verification Token Debug</h2>";
echo "<p><strong>Looking for token:</strong> " . htmlspecialchars($token) . "</p>";
echo "<hr>";

try {
    $usersTable = TableRegistry::getTableLocator()->get('Users');
    
    // Check if the token exists in database
    echo "<h3>1. Check if token exists in database:</h3>";
    $user = $usersTable->find()
        ->where(['verification_token' => $token])
        ->first();
    
    if ($user) {
        echo "<div style='color:green;'>✅ Token found in database!</div>";
        echo "<pre>";
        echo "User ID: " . $user->id . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Email Verified: " . ($user->email_verified ? 'Yes' : 'No') . "\n";
        echo "Verification Token: " . $user->verification_token . "\n";
        echo "Token Expires: " . ($user->verification_token_expires ? $user->verification_token_expires->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "Status: " . $user->status . "\n";
        echo "</pre>";
        
        // Check if expired
        if ($user->verification_token_expires) {
            $now = new \DateTime();
            if ($user->verification_token_expires < $now) {
                echo "<div style='color:red;'>⚠️ Token is EXPIRED!</div>";
                echo "<p>Expired at: " . $user->verification_token_expires->format('Y-m-d H:i:s') . "</p>";
                echo "<p>Current time: " . $now->format('Y-m-d H:i:s') . "</p>";
            } else {
                echo "<div style='color:green;'>✅ Token is still valid</div>";
            }
        }
    } else {
        echo "<div style='color:red;'>❌ Token NOT found in database</div>";
    }
    
    echo "<hr>";
    
    // Check unverified users
    echo "<h3>2. All unverified users with tokens:</h3>";
    $unverified = $usersTable->find()
        ->where(['email_verified' => 0])
        ->where(['verification_token IS NOT' => null])
        ->all();
    
    if ($unverified->count() > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Email</th><th>Token (first 20 chars)</th><th>Expires</th></tr>";
        foreach ($unverified as $u) {
            echo "<tr>";
            echo "<td>" . $u->id . "</td>";
            echo "<td>" . htmlspecialchars($u->email) . "</td>";
            echo "<td>" . substr($u->verification_token, 0, 20) . "...</td>";
            echo "<td>" . ($u->verification_token_expires ? $u->verification_token_expires->format('Y-m-d H:i:s') : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No unverified users with tokens found</p>";
    }
    
    echo "<hr>";
    
    // Check table structure
    echo "<h3>3. Check users table structure:</h3>";
    $schema = $usersTable->getSchema();
    $columns = $schema->columns();
    
    $requiredColumns = ['email_verified', 'verification_token', 'verification_token_expires'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "<div style='color:green;'>✅ Column exists: $col</div>";
        } else {
            echo "<div style='color:red;'>❌ Column MISSING: $col</div>";
        }
    }
    
    echo "<hr>";
    
    // Check User entity accessible fields
    echo "<h3>4. Check User entity accessible fields:</h3>";
    $entity = $usersTable->newEmptyEntity();
    $accessible = $entity->getAccessible();
    
    foreach ($requiredColumns as $col) {
        if (isset($accessible[$col]) && $accessible[$col]) {
            echo "<div style='color:green;'>✅ Field is accessible: $col</div>";
        } else {
            echo "<div style='color:red;'>❌ Field NOT accessible: $col</div>";
        }
    }
    
} catch (\Throwable $e) {
    echo "<div style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER DEBUGGING!</strong></p>";
?>