<?php
/**
 * Password Reset Debug Page
 * Access this page to verify password reset functionality
 * URL: /debug_reset.php?token=YOUR_TOKEN
 */

// Get token from URL
$token = $_GET['token'] ?? null;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Reset Debug Tool</h1>
        
        <?php if (!$token): ?>
            <div class="warning">
                <strong>⚠ No Token Provided</strong><br>
                Access this page with a token: <code>?token=YOUR_TOKEN_HERE</code>
            </div>
        <?php else: ?>
            <div class="info">
                <strong>Token Received:</strong><br>
                <code><?= htmlspecialchars($token) ?></code><br>
                <small>Length: <?= strlen($token) ?> characters</small>
            </div>
        <?php endif; ?>

        <h2>Database Connection Test</h2>
        <?php
        try {
            // Load CakePHP bootstrap - fix path for webroot location
            require __DIR__ . '/../config/bootstrap.php';
            
            // Get database connection
            $connection = \Cake\Datasource\ConnectionManager::get('default');
            
            echo '<div class="success">✓ Database connection successful</div>';
            
            // Check if users table exists
            $tables = $connection->execute('SHOW TABLES LIKE "users"')->fetchAll();
            if (count($tables) > 0) {
                echo '<div class="success">✓ Users table exists</div>';
                
                // Check for security columns
                $columns = $connection->execute('SHOW COLUMNS FROM users')->fetchAll();
                $columnNames = array_column($columns, 'Field');
                
                echo '<h2>Security Columns Status</h2>';
                echo '<table>';
                echo '<tr><th>Column Name</th><th>Status</th></tr>';
                
                $requiredColumns = [
                    'password_reset_token',
                    'password_reset_expires',
                    'failed_login_attempts',
                    'account_locked_until',
                    'two_factor_secret',
                    'two_factor_enabled'
                ];
                
                $missingColumns = [];
                foreach ($requiredColumns as $col) {
                    $exists = in_array($col, $columnNames);
                    $status = $exists ? '✓ Exists' : '✗ Missing';
                    $class = $exists ? 'success' : 'error';
                    echo "<tr style='background: " . ($exists ? '#d4edda' : '#f8d7da') . "'>";
                    echo "<td><code>$col</code></td>";
                    echo "<td>$status</td>";
                    echo "</tr>";
                    
                    if (!$exists) {
                        $missingColumns[] = $col;
                    }
                }
                echo '</table>';
                
                if (!empty($missingColumns)) {
                    echo '<div class="error">';
                    echo '<strong>⚠ Missing Columns Detected!</strong><br>';
                    echo 'Run the migration script: <code>config/schema/verify_security_columns.sql</code>';
                    echo '</div>';
                }
                
                // If token provided, try to find user
                if ($token && empty($missingColumns)) {
                    echo '<h2>Token Lookup</h2>';
                    
                    $query = $connection->execute(
                        'SELECT id, email, password_reset_expires FROM users WHERE password_reset_token = ?',
                        [$token]
                    );
                    $user = $query->fetch('assoc');
                    
                    if ($user) {
                        echo '<div class="success">✓ User found with this token</div>';
                        echo '<table>';
                        echo '<tr><th>Field</th><th>Value</th></tr>';
                        echo '<tr><td>User ID</td><td>' . htmlspecialchars($user['id']) . '</td></tr>';
                        echo '<tr><td>Email</td><td>' . htmlspecialchars($user['email']) . '</td></tr>';
                        echo '<tr><td>Expires</td><td>' . htmlspecialchars($user['password_reset_expires']) . '</td></tr>';
                        
                        $now = new DateTime();
                        $expires = new DateTime($user['password_reset_expires']);
                        $isExpired = $expires < $now;
                        
                        echo '<tr><td>Status</td><td style="background: ' . ($isExpired ? '#f8d7da' : '#d4edda') . '">';
                        echo $isExpired ? '✗ Expired' : '✓ Valid';
                        echo '</td></tr>';
                        echo '</table>';
                        
                        if (!$isExpired) {
                            $resetUrl = '/users/reset-password?token=' . urlencode($token);
                            echo '<div class="success">';
                            echo '<strong>✓ Token is valid! Redirect to reset page:</strong><br>';
                            echo '<a href="' . $resetUrl . '" class="btn">Go to Reset Password</a>';
                            echo '</div>';
                        } else {
                            echo '<div class="error">Token has expired. Request a new password reset.</div>';
                        }
                    } else {
                        echo '<div class="error">✗ No user found with this token</div>';
                        
                        // Show recent tokens for debugging
                        $recentQuery = $connection->execute(
                            'SELECT email, LEFT(password_reset_token, 20) as token_prefix, password_reset_expires 
                             FROM users 
                             WHERE password_reset_token IS NOT NULL 
                             ORDER BY password_reset_expires DESC 
                             LIMIT 5'
                        );
                        $recentTokens = $recentQuery->fetchAll('assoc');
                        
                        if (!empty($recentTokens)) {
                            echo '<h3>Recent Password Reset Tokens</h3>';
                            echo '<table>';
                            echo '<tr><th>Email</th><th>Token Prefix</th><th>Expires</th></tr>';
                            foreach ($recentTokens as $row) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                echo '<td><code>' . htmlspecialchars($row['token_prefix']) . '...</code></td>';
                                echo '<td>' . htmlspecialchars($row['password_reset_expires']) . '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                    }
                }
                
            } else {
                echo '<div class="error">✗ Users table not found</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '<strong>✗ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>

        <h2>Quick Actions</h2>
        <a href="/users/forgot-password" class="btn">Request New Reset</a>
        <a href="/users/login" class="btn">Go to Login</a>
        
        <div class="info" style="margin-top: 30px;">
            <strong>📝 Note:</strong> Delete this file (<code>debug_reset.php</code>) after deployment for security.
        </div>
    </div>
</body>
</html>
