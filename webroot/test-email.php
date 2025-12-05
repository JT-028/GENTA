<?php
// Temporary test script for SMTP email sending. Delete after testing.
require __DIR__ . '/..//vendor/autoload.php';
require __DIR__ . '/..//config/bootstrap.php';

use Cake\Mailer\Mailer;

try {
    echo "<h2>GENTA SMTP Test</h2>";
    $mailer = new Mailer('default');
    $to = getenv('TEST_EMAIL') ?: 'your-test-email@example.com'; // replace or set env var

    $result = $mailer
        ->setTo($to)
        ->setSubject('GENTA Email Test - ' . date('Y-m-d H:i:s'))
        ->deliver('This is a test email from GENTA. If you receive this, SMTP is configured correctly.');

    echo "<div style='color:green; font-weight:bold;'>✅ Email attempted to send to: " . htmlspecialchars($to) . "</div>";
    echo "<pre>Mailer result: ";
    var_dump($result);
    echo "</pre>";
    echo "<p>Check the recipient inbox (and spam folder).</p>";
} catch (\Throwable $e) {
    echo "<div style='color:red; font-weight:bold;'>❌ Error sending email:</div>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Check Cloudways Application Logs for more details.</p>";
}

echo "<hr><p><strong>Important:</strong> Delete this file after testing.</p>";
?>