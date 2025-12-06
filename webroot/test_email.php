<?php
/**
 * Email Configuration Test Script
 * 
 * This script tests your email configuration to ensure password reset emails can be sent.
 * Place this in webroot/ and access it via browser to test email functionality.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;

// Load configuration
Configure::config('default', new PhpConfig());
Configure::load('app', 'default', false);
Configure::load('app_local', 'default');

// Initialize email transport
TransportFactory::drop('default');
TransportFactory::setConfig(Configure::read('EmailTransport'));

echo "<h2>GENTA Email Configuration Test</h2>";
echo "<hr>";

// Display current email configuration
echo "<h3>Current Email Configuration:</h3>";
echo "<pre>";
$emailConfig = Configure::read('EmailTransport.default');
echo "Transport: " . ($emailConfig['className'] ?? 'Not set') . "\n";
echo "Host: " . ($emailConfig['host'] ?? 'Not set') . "\n";
echo "Port: " . ($emailConfig['port'] ?? 'Not set') . "\n";
echo "Username: " . ($emailConfig['username'] ?? 'Not set') . "\n";
echo "Password: " . (isset($emailConfig['password']) && !empty($emailConfig['password']) ? '***SET***' : 'Not set') . "\n";
echo "TLS: " . (($emailConfig['tls'] ?? false) ? 'Enabled' : 'Disabled') . "\n";
echo "</pre>";

$fromEmail = Configure::read('Email.default.from');
echo "<h3>From Address:</h3>";
echo "<pre>";
print_r($fromEmail);
echo "</pre>";

// Test email sending
if (isset($_GET['send_test']) && $_GET['send_test'] === '1') {
    $testEmail = $_GET['test_email'] ?? '';
    
    if (!empty($testEmail) && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<h3>Sending Test Email to: $testEmail</h3>";
        
        try {
            $mailer = new Mailer('default');
            $mailer
                ->setTo($testEmail)
                ->setSubject('GENTA - Test Email from Password Reset System')
                ->setEmailFormat('html')
                ->deliver('<h2>Test Email</h2><p>If you received this email, your GENTA password reset system is configured correctly!</p><p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>');
            
            echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<strong>✓ SUCCESS!</strong> Test email sent successfully.";
            echo "</div>";
        } catch (\Exception $e) {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<strong>✗ ERROR:</strong> Failed to send email<br>";
            echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "<strong>File:</strong> " . $e->getFile() . ":" . $e->getLine();
            echo "</div>";
            
            echo "<h4>Full Error Trace:</h4>";
            echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto;'>";
            echo htmlspecialchars($e->getTraceAsString());
            echo "</pre>";
        }
    } else {
        echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
        echo "<strong>⚠ WARNING:</strong> Please provide a valid email address.";
        echo "</div>";
    }
}

// Show test form
echo "<hr>";
echo "<h3>Send Test Email:</h3>";
echo "<form method='GET'>";
echo "<input type='hidden' name='send_test' value='1'>";
echo "<label>Email Address: <input type='email' name='test_email' required placeholder='your-email@example.com' style='width: 300px; padding: 5px;'></label><br><br>";
echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Send Test Email</button>";
echo "</form>";

echo "<hr>";
echo "<h3>Environment Variables Check:</h3>";
echo "<pre>";
echo "SMTP_HOST: " . (getenv('SMTP_HOST') ?: 'Not set') . "\n";
echo "SMTP_PORT: " . (getenv('SMTP_PORT') ?: 'Not set') . "\n";
echo "SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ?: 'Not set') . "\n";
echo "SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ? '***SET***' : 'Not set') . "\n";
echo "EMAIL_FROM_ADDRESS: " . (getenv('EMAIL_FROM_ADDRESS') ?: 'Not set') . "\n";
echo "</pre>";

echo "<hr>";
echo "<h3>Instructions for Cloudways:</h3>";
echo "<ol>";
echo "<li>Go to Cloudways Dashboard → Your Application → Settings & Packages → Packages</li>";
echo "<li>Install/Enable <strong>PHP Mail</strong> or configure SMTP</li>";
echo "<li>Go to Application → Access Details → Add Environment Variables:</li>";
echo "<ul>";
echo "<li><code>SMTP_HOST=smtp.gmail.com</code> (or your SMTP server)</li>";
echo "<li><code>SMTP_PORT=587</code></li>";
echo "<li><code>SMTP_USERNAME=your-email@gmail.com</code></li>";
echo "<li><code>SMTP_PASSWORD=your-app-password</code></li>";
echo "<li><code>EMAIL_FROM_ADDRESS=noreply@yourdomain.com</code></li>";
echo "</ul>";
echo "<li>For Gmail, create an <a href='https://support.google.com/accounts/answer/185833' target='_blank'>App Password</a></li>";
echo "<li>Restart PHP-FPM in Cloudways dashboard</li>";
echo "</ol>";

echo "<p><small>After configuration, delete this test file for security.</small></p>";
?>
