<?php
/**
 * Simple Password Reset Debug - Direct Database Connection
 * No CakePHP required
 */

// Database credentials
$host = 'localhost';
$username = 'zfepxctexd';
$password = 'VVyb8UNZ93';
$database = 'zfepxctexd';

$token = $_GET['token'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Debug - Direct DB</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Reset Debug (Direct DB)</h1>
        
        <?php if (!$token): ?>
            <div class="warning">
                <strong>⚠ No Token Provided</strong><br>
                Access: <code>?token=YOUR_TOKEN</code>
            </div>
        <?php else: ?>
            <div class="info">
                <strong>Token:</strong> <code><?= htmlspecialchars($token) ?></code><br>
                <small>Length: <?= strlen($token) ?> characters</small>
            </div>
        <?php endif; ?>

        <h2>Database Connection</h2>
        <?php
        try {
            $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo '<div class="success">✓ Connected to database: ' . htmlspecialchars($database) . '</div>';
            
            // Check if security columns exist
            echo '<h2>Security Columns Check</h2>';
            $stmt = $conn->query("SHOW COLUMNS FROM users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = [
                'password_reset_token',
                'password_reset_expires',
                'failed_login_attempts',
                'account_locked_until',
                'two_factor_secret',
                'two_factor_enabled'
            ];
            
            echo '<table><tr><th>Column</th><th>Status</th></tr>';
            foreach ($requiredColumns as $col) {
                $exists = in_array($col, $columns);
                $status = $exists ? '✓ Exists' : '✗ Missing';
                $color = $exists ? '#d4edda' : '#f8d7da';
                echo "<tr style='background: $color'><td><code>$col</code></td><td>$status</td></tr>";
            }
            echo '</table>';
            
            // If token provided, search for it
            if ($token) {
                echo '<h2>Token Search Results</h2>';
                
                $stmt = $conn->prepare("SELECT id, email, password_reset_expires FROM users WHERE password_reset_token = ?");
                $stmt->execute([$token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo '<div class="success">✓ Token found in database!</div>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    echo '<tr><td>User ID</td><td>' . htmlspecialchars($user['id']) . '</td></tr>';
                    echo '<tr><td>Email</td><td>' . htmlspecialchars($user['email']) . '</td></tr>';
                    echo '<tr><td>Expires At</td><td>' . htmlspecialchars($user['password_reset_expires']) . '</td></tr>';
                    
                    $now = new DateTime();
                    $expires = new DateTime($user['password_reset_expires']);
                    $isExpired = $expires < $now;
                    
                    echo '<tr><td>Current Time</td><td>' . $now->format('Y-m-d H:i:s') . '</td></tr>';
                    echo '<tr style="background: ' . ($isExpired ? '#f8d7da' : '#d4edda') . '">';
                    echo '<td><strong>Status</strong></td><td><strong>' . ($isExpired ? '✗ EXPIRED' : '✓ VALID') . '</strong></td></tr>';
                    echo '</table>';
                    
                    if (!$isExpired) {
                        $resetUrl = '/users/reset-password?token=' . urlencode($token);
                        echo '<div class="success">';
                        echo '<strong>Token is valid! Try this link:</strong><br>';
                        echo '<a href="' . $resetUrl . '" class="btn">Go to Reset Password Page</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="error">';
                        echo '<strong>Token has expired.</strong><br>';
                        echo 'Expired: ' . $expires->format('Y-m-d H:i:s') . '<br>';
                        echo 'Time difference: ' . $now->diff($expires)->format('%h hours %i minutes ago');
                        echo '</div>';
                    }
                } else {
                    echo '<div class="error">✗ Token NOT found in database</div>';
                    
                    // Show recent tokens
                    echo '<h3>Recent Password Reset Requests</h3>';
                    $stmt = $conn->query("
                        SELECT email, 
                               LEFT(password_reset_token, 20) as token_prefix,
                               password_reset_expires,
                               CASE 
                                   WHEN password_reset_expires < NOW() THEN 'Expired'
                                   ELSE 'Valid'
                               END as status
                        FROM users 
                        WHERE password_reset_token IS NOT NULL 
                        ORDER BY password_reset_expires DESC 
                        LIMIT 10
                    ");
                    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($recent) > 0) {
                        echo '<table>';
                        echo '<tr><th>Email</th><th>Token Prefix</th><th>Expires</th><th>Status</th></tr>';
                        foreach ($recent as $row) {
                            $color = $row['status'] === 'Valid' ? '#d4edda' : '#f8d7da';
                            echo "<tr style='background: $color'>";
                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td><code>' . htmlspecialchars($row['token_prefix']) . '...</code></td>';
                            echo '<td>' . htmlspecialchars($row['password_reset_expires']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                        echo '<div class="info">';
                        echo '<strong>Debug Tip:</strong> Compare the token prefix from email with tokens above.<br>';
                        echo 'Your token starts with: <code>' . htmlspecialchars(substr($token, 0, 20)) . '</code>';
                        echo '</div>';
                    } else {
                        echo '<div class="warning">No password reset tokens found in database.</div>';
                    }
                }
            }
            
            $conn = null;
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>✗ Database Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>

        <h2>Quick Actions</h2>
        <a href="/users/forgot-password" class="btn">Request New Reset</a>
        <a href="/users/login" class="btn">Go to Login</a>
        
        <div class="info" style="margin-top: 30px;">
            <strong>📝 Instructions:</strong><br>
            1. Request password reset from forgot-password page<br>
            2. Check email for reset link<br>
            3. Copy the token from the URL<br>
            4. Access this page: <code>?token=PASTE_TOKEN_HERE</code>
        </div>
    </div>
</body>
</html>
