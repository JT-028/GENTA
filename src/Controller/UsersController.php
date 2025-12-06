<?php declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;
use Cake\Core\Configure;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Controller\Component\SecurityComponent $Security
 * @property \App\Controller\Component\CaptchaComponent $Captcha
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Initialize controller
     */
    public function initialize(): void
    {
        parent::initialize();
        // Security component is now loaded in AppController
        $this->loadComponent('Captcha');
    }
    public function logout()
    {
        // AUTHENTICATE
        $result = $this->Authentication->getResult();

        // EXTRA DIAGNOSTICS: log request-level info that may affect authentication
        try {
            $reqPath = (string)$this->request->getUri()->getPath();
            $parsed = $this->request->getData() ?: [];
            $parsedKeys = array_keys((array)$parsed);
            $csrfHeader = $this->request->getHeaderLine('X-CSRF-Token');
            $csrfInBody = array_key_exists('_csrfToken', (array)$parsed) || array_key_exists('csrfToken', (array)$parsed);
            $sessionCookieName = (string)\Cake\Core\Configure::read('Session.cookie');
            if ($sessionCookieName === '') { $sessionCookieName = 'CAKEPHP'; }
            $sessionCookieVal = $this->request->getCookie($sessionCookieName);
            $sessionCookieInfo = $sessionCookieVal ? 'present len=' . strlen($sessionCookieVal) : 'missing';

            $authErrors = '';
            if ($result && method_exists($result, 'getErrors')) {
                try { $authErrors = var_export($result->getErrors(), true); } catch (\Throwable $_) { $authErrors = '<err>'; }
            }

            // Lowered to debug: useful for troubleshooting but noisy at info level
            \Cake\Log\Log::write('debug', 'Login request diag: path=' . $reqPath . ' parsed_keys=' . implode(',', $parsedKeys) . ' csrfHeaderPresent=' . ($csrfHeader ? '1' : '0') . ' csrfInBody=' . ($csrfInBody ? '1' : '0') . ' sessionCookie(' . $sessionCookieName . ')=' . $sessionCookieInfo . ' authErrors=' . $authErrors);
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'Login request diagnostics failed: ' . $e->getMessage());
        }

        // Log authentication result details (non-sensitive) for diagnostics
        try {
            if ($result) {
                $identity = null;
                try { $identity = $result->getData(); } catch (\Throwable $_) { $identity = null; }
                $idInfo = '<none>';
                if (is_object($identity) && property_exists($identity, 'id')) {
                    $idInfo = 'id=' . $identity->id;
                } elseif (is_array($identity) && array_key_exists('id', $identity)) {
                    $idInfo = 'id=' . $identity['id'];
                }
                \Cake\Log\Log::write('debug', 'Authentication result: isValid=' . ($result->isValid() ? '1' : '0') . ' identity=' . $idInfo);
            } else {
                \Cake\Log\Log::write('debug', 'Authentication result: none');
            }
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'Auth result diagnostics failed: ' . $e->getMessage());
        }

        // VALIDATE AUTH RESULT
        if ($result && $result->isValid())
        {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        // Get client IP for rate limiting
        $clientIp = $this->Security->getClientIp();
        $email = null;
        
        // Check rate limiting before processing
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $rateLimitCheck = $this->Security->checkRateLimit($email, $clientIp);
            
            if (!$rateLimitCheck['allowed']) {
                $minutes = ceil(($rateLimitCheck['lockoutTime'] - time()) / 60);
                $this->Flash->error(__('Too many failed login attempts. Please try again in {0} minutes.', $minutes));
                $this->set('rateLimited', true);
                $this->set('lockoutMinutes', $minutes);
                return;
            }
            
            // Check CAPTCHA if it was required and shown
            $captchaRequired = $rateLimitCheck['remainingAttempts'] <= 3;
            if ($captchaRequired && $this->request->getData('captcha') !== null) {
                $captchaAnswer = $this->request->getData('captcha');
                if (!$this->Captcha->verify($captchaAnswer)) {
                    $this->Flash->error(__('Invalid CAPTCHA answer. Please try again.'));
                    $this->Security->recordFailedAttempt($email, $clientIp);
                    $challenge = $this->Captcha->generateChallenge();
                    $this->set('captchaChallenge', $challenge['question']);
                    $this->set('showCaptcha', true);
                    $this->set('remainingAttempts', $rateLimitCheck['remainingAttempts'] - 1);
                    return;
                }
            }
        }

        // AUTHENTICATE
        $result = $this->Authentication->getResult();

        // Diagnostic logging: record that a login route was hit, whether POST data arrived,
        // and the submitted email address (never log passwords). Add explicit parsed-body
        // diagnostics so we can see if the Form authenticator is seeing the fields.
        try {
            $isPost = $this->request->is('post');
            $posted = $this->request->getData() ?: [];
            $parsedKeys = implode(',', array_keys((array)$posted));
            $emailPresent = array_key_exists('email', (array)$posted) ? '1' : '0';
            $pwPresent = array_key_exists('password', (array)$posted) ? '1' : '0';
            $email = $emailPresent === '1' ? (string)$posted['email'] : '<none>';
            $authState = 'none';
            if ($result) { $authState = $result->isValid() ? 'valid' : 'invalid'; }
            \Cake\Log\Log::write('debug', 'Login attempt: isPost=' . ($isPost ? '1' : '0') . ' parsed_keys=' . $parsedKeys . ' email_present=' . $emailPresent . ' password_present=' . $pwPresent . ' email=' . $email . ' authResult=' . $authState);
        } catch (\Throwable $e) {
            // Ensure diagnostics never break the login flow
            \Cake\Log\Log::write('error', 'Login diagnostics failed: ' . $e->getMessage());
        }

        // Additional diagnostics on POST: check whether the submitted password would match
        // the stored hash for the user (without logging the plain password).
        try {
            if ($this->request->is('post')) {
                $posted = $this->request->getData();
                $email = isset($posted['email']) ? (string)$posted['email'] : null;
                $pwProvided = isset($posted['password']) ? true : false;
                if ($email) {
                    $usersTable = $this->loadModel('Users');
                    $user = $usersTable->find()->where(['email' => strtolower($email)])->first();
                    if ($user) {
                        // EARLY CHECK: if the account exists but is not approved (status != 1),
                        // block the login attempt immediately to prevent Authentication from
                        // establishing a session for a pending account.
                        if ((int)($user->status ?? 0) !== 1) {
                            \Cake\Log\Log::write('warning', 'Blocked login attempt for ' . $email . ' due to status=' . ($user->status ?? '<null>'));
                            $this->Flash->error(__('Your account is not active. It may be pending admin approval.'));
                            return;
                        }

                        $storedHash = $user->password ?? '<none>';
                        // Use DefaultPasswordHasher to verify
                        $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
                        $check = false;
                        if ($pwProvided && !empty($storedHash)) {
                            $check = $hasher->check((string)$posted['password'], $storedHash);
                        }
                        \Cake\Log\Log::write('debug', 'Login password check: user_found=1 pw_provided=' . ($pwProvided ? '1' : '0') . ' password_matches=' . ($check ? '1' : '0'));
                    } else {
                        \Cake\Log\Log::write('debug', 'Login password check: user_found=0 email=' . $email);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'Login password-check diagnostics failed: ' . $e->getMessage());
        }

        // EXTRA: if an authentication result exists, log any errors/messages it provides
        try {
            if ($result) {
                $err = [];
                if (method_exists($result, 'getErrors')) {
                    try { $err = $result->getErrors(); } catch (\Throwable $_) { $err = ['err' => '<error fetching>']; }
                }
                \Cake\Log\Log::write('debug', 'Login auth result details: isValid=' . ($result->isValid() ? '1' : '0') . ' errors=' . json_encode($err));
            } else {
                \Cake\Log\Log::write('debug', 'Login auth result details: none');
            }
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'Login auth-result diagnostics failed: ' . $e->getMessage());
        }

        // VALIDATE AUTH RESULT
        if ($result && $result->isValid())
        {
            // Prevent users who are not approved (status != 1) from logging in.
            try {
                $identity = null;
                try { $identity = $result->getData(); } catch (\Throwable $_) { $identity = null; }
                $userId = null;
                if (is_object($identity) && property_exists($identity, 'id')) {
                    $userId = $identity->id;
                } elseif (is_array($identity) && array_key_exists('id', $identity)) {
                    $userId = $identity['id'];
                }

                if ($userId) {
                    $usersTable = $this->loadModel('Users');
                    $userEntity = $usersTable->get($userId);
                    if ((int)($userEntity->status ?? 0) !== 1) {
                        // Deny login for pending/suspended accounts
                        \Cake\Log\Log::write('warning', 'Login blocked for user ' . $userId . ' due to status=' . ($userEntity->status ?? '<null>'));
                        $this->Flash->error(__('Your account is not active. It may be pending admin approval.'));
                        // Ensure no identity is kept
                        try { $this->Authentication->logout(); } catch (\Throwable $_) { }
                        // Do not redirect into the app
                        return;
                    }
                }
            } catch (\Throwable $e) {
                \Cake\Log\Log::write('error', 'Error while checking user status on login: ' . $e->getMessage());
            }

            // Clear failed attempts on successful login
            $this->Security->clearFailedAttempts($email, $clientIp);
            
            // Regenerate session for security
            $this->Security->regenerateSession();

            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Dashboard',
                'action' => 'index',
                'prefix' => 'Teacher'
            ]);

            return $this->redirect($redirect);
        }

        // IF WRONG CREDENTIALS
        if ($this->request->is('post') && !$result->isValid())
        {
            // Record failed attempt
            $this->Security->recordFailedAttempt($email, $clientIp);
            
            // Check remaining attempts
            $rateLimitCheck = $this->Security->checkRateLimit($email, $clientIp);
            $remainingAttempts = $rateLimitCheck['remainingAttempts'];
            
            if ($remainingAttempts <= 3 && $remainingAttempts > 0) {
                $this->Flash->error(__('Invalid email or password. {0} attempts remaining.', $remainingAttempts));
                // Generate CAPTCHA for next attempt
                $challenge = $this->Captcha->generateChallenge();
                $this->set('captchaChallenge', $challenge['question']);
                $this->set('showCaptcha', true);
                $this->set('remainingAttempts', $remainingAttempts);
            } else {
                $this->Flash->error(__('Invalid email or password.'));
                $this->set('remainingAttempts', $remainingAttempts);
            }
        }
        
        // Generate CAPTCHA if needed for GET request
        if ($this->request->is('get')) {
            $rateLimitCheck = $this->Security->checkRateLimit(null, $clientIp);
            if ($rateLimitCheck['remainingAttempts'] <= 3) {
                $challenge = $this->Captcha->generateChallenge();
                $this->set('captchaChallenge', $challenge['question']);
                $this->set('showCaptcha', true);
            }
            $this->set('remainingAttempts', $rateLimitCheck['remainingAttempts']);
        }
    }
    /**
     * Fix for PHP 8.2 dynamic property deprecation
     * @var \App\Model\Table\UsersTable|null
     */
    public $Users = null;
    public function register()
    {
        $usersTable = $this->loadModel('Users');
        $user = $usersTable->newEmptyEntity();

        if ($this->request->is('post'))
        {
            $postedData = $this->request->getData() ?: [];
            try {
                \Cake\Log\Log::write('debug', 'UsersController::register POST received keys=' . implode(',', array_keys((array)$postedData)) . ' email=' . (isset($postedData['email']) ? $postedData['email'] : '<none>'));
            } catch (\Throwable $_) { }

            $user = $usersTable->patchEntity($user, $postedData);

            try {
                \Cake\Log\Log::write('debug', 'UsersController::register: after patchEntity hasErrors=' . ($user->hasErrors() ? '1' : '0') . ' terms_and_conditions=' . (isset($postedData['terms_and_conditions']) ? (string)$postedData['terms_and_conditions'] : '<missing>'));
                if ($user->hasErrors()) {
                    \Cake\Log\Log::write('debug', 'UsersController::register: validation_errors=' . json_encode($user->getErrors()));
                }
            } catch (\Throwable $_) { }

            if (!$user->hasErrors())
            {
                if ($this->request->getData('password') === $this->request->getData('confirm_password'))
                {
                    if ($this->request->getData('terms_and_conditions'))
                    {
                        // Generate email verification token
                        $verificationToken = bin2hex(random_bytes(32));
                        $user->email_verified = 0;
                        $user->verification_token = $verificationToken;
                        $user->verification_token_expires = new \DateTime('+24 hours');
                        
                        // Set account to pending status (unverified)
                        $user->status = 0;

                        if ($usersTable->save($user))
                        {
                            // Send email verification
                            try {
                                $mailer = new \Cake\Mailer\Mailer('default');
                                
                                // Build verification URL
                                $uri = $this->request->getUri();
                                $scheme = $uri->getScheme() ?: 'http';
                                $host = $uri->getHost();
                                $port = $uri->getPort();
                                $baseUrl = $scheme . '://' . $host;
                                if ($port && !in_array($port, [80, 443])) {
                                    $baseUrl .= ':' . $port;
                                }
                                $appBase = Configure::read('App.base') ?: '';
                                $verificationUrl = rtrim($baseUrl, '\\/') . ($appBase ? rtrim($appBase, '/') : '') . '/users/verify-email/' . $verificationToken;
                                
                                $mailer
                                    ->setTo($user->email)
                                    ->setSubject('GENTA - Verify Your DepEd Email Address')
                                    ->setViewVars([
                                        'firstName' => $user->first_name,
                                        'lastName' => $user->last_name,
                                        'verificationUrl' => $verificationUrl
                                    ])
                                    ->viewBuilder()
                                        ->setTemplate('verify_email')
                                        ->setLayout('default');
                                
                                $mailer->deliver();
                                
                                \Cake\Log\Log::write('debug', 'Verification email sent to: ' . $user->email);
                            } catch (\Throwable $e) {
                                \Cake\Log\Log::write('error', 'Failed to send verification email: ' . $e->getMessage());
                                // Don't stop registration if email fails, user can request resend
                            }

                            $this->Flash->success(__('Registration successful! Please check your DepEd email inbox to verify your email address. The verification link will expire in 24 hours.'));
                            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
                        } else {
                            \Cake\Log\Log::write('warning', 'UsersController::register: save returned false; validation_errors=' . json_encode($user->getErrors()));
                        }
                    } else {
                        $this->Flash->error(__('You need to agree with the Terms and Conditions.'));
                    }
                } else {
                    $this->Flash->error(__('Confirm Password did not match the password.'));
                }
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Request password reset
     */
    public function forgotPassword()
    {
        $this->request->allowMethod(['get', 'post']);
        
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            
            if ($email) {
                $usersTable = $this->loadModel('Users');
                $user = $usersTable->find()->where(['email' => strtolower($email)])->first();
                
                if ($user) {
                    // Generate password reset token
                    $resetToken = bin2hex(random_bytes(32));
                    $user->password_reset_token = $resetToken;
                    $user->password_reset_expires = new \DateTime('+1 hour');
                    
                    \Cake\Log\Log::write('debug', 'Generated reset token for ' . $user->email . ': ' . $resetToken);
                    \Cake\Log\Log::write('debug', 'Attempting to save user with ID: ' . $user->id);
                    
                    // Disable validation for this save operation
                    if ($usersTable->save($user, ['validate' => false, 'checkRules' => false])) {
                        \Cake\Log\Log::write('debug', 'User saved with reset token successfully');
                        
                        // Send password reset email
                        try {
                            \Cake\Log\Log::write('debug', 'Attempting to send password reset email...');
                            $mailer = new \Cake\Mailer\Mailer('default');
                            
                            // Build reset URL using Router
                            $resetUrl = \Cake\Routing\Router::url([
                                'controller' => 'Users',
                                'action' => 'resetPassword',
                                '?' => ['token' => $resetToken]
                            ], true);
                            
                            $mailer
                                ->setTo($user->email)
                                ->setSubject('GENTA - Password Reset Request')
                                ->setEmailFormat('html')
                                ->setViewVars([
                                    'firstName' => $user->first_name,
                                    'resetUrl' => $resetUrl
                                ])
                                ->viewBuilder()
                                    ->setTemplate('password_reset');
                            
                            \Cake\Log\Log::write('debug', 'About to call deliver() with URL: ' . $resetUrl);
                            $result = $mailer->deliver();
                            
                            \Cake\Log\Log::write('info', 'Password reset email sent successfully to: ' . $user->email);
                            \Cake\Log\Log::write('debug', 'Email result: ' . print_r($result, true));
                        } catch (\Throwable $e) {
                            \Cake\Log\Log::write('error', 'Failed to send password reset email to ' . $user->email . ': ' . $e->getMessage());
                            \Cake\Log\Log::write('error', 'Error file: ' . $e->getFile() . ':' . $e->getLine());
                            \Cake\Log\Log::write('error', 'Stack trace: ' . $e->getTraceAsString());
                            
                            // Fallback: Try sending without template
                            try {
                                \Cake\Log\Log::write('debug', 'Trying fallback email method without template...');
                                $simpleMailer = new \Cake\Mailer\Mailer('default');
                                
                                $htmlBody = "
                                <html>
                                <body style='font-family: Arial, sans-serif; padding: 20px;'>
                                    <h2>Password Reset Request</h2>
                                    <p>Hello {$user->first_name},</p>
                                    <p>Click the link below to reset your password:</p>
                                    <p><a href='{$resetUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
                                    <p>Or copy this link: <br>{$resetUrl}</p>
                                    <p><strong>This link expires in 1 hour.</strong></p>
                                </body>
                                </html>
                                ";
                                
                                $simpleMailer
                                    ->setTo($user->email)
                                    ->setSubject('GENTA - Password Reset Request')
                                    ->setEmailFormat('html')
                                    ->deliver($htmlBody);
                                
                                \Cake\Log\Log::write('info', 'Fallback email sent successfully to: ' . $user->email);
                            } catch (\Throwable $fallbackError) {
                                \Cake\Log\Log::write('error', 'Fallback email also failed: ' . $fallbackError->getMessage());
                            }
                        }
                    } else {
                        \Cake\Log\Log::write('error', 'CRITICAL: Failed to save user with reset token');
                        \Cake\Log\Log::write('error', 'User ID: ' . $user->id);
                        \Cake\Log\Log::write('error', 'Validation errors: ' . print_r($user->getErrors(), true));
                        \Cake\Log\Log::write('error', 'Dirty fields: ' . print_r($user->getDirty(), true));
                        \Cake\Log\Log::write('error', 'Token value: ' . $user->password_reset_token);
                    }
                } else {
                    \Cake\Log\Log::write('debug', 'Password reset requested for non-existent email: ' . $email);
                }
            }
            
            // Always show success message (don't reveal if email exists)
            $this->Flash->success(__('If the email address exists, a password reset link has been sent. Please check your inbox.'));
            return $this->redirect(['action' => 'login']);
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword()
    {
        $this->request->allowMethod(['get', 'post']);
        
        // Get token from query string - try both methods
        $token = $this->request->getQuery('token');
        if (!$token) {
            $token = $this->request->getData('token');
        }
        
        // Clean the token (remove any whitespace or special characters)
        if ($token) {
            $token = trim($token);
        }
        
        \Cake\Log\Log::write('debug', 'Reset password accessed');
        \Cake\Log\Log::write('debug', 'Token: ' . ($token ?: 'NONE'));
        \Cake\Log\Log::write('debug', 'Token length: ' . strlen($token ?: ''));
        \Cake\Log\Log::write('debug', 'Query string: ' . $this->request->getUri()->getQuery());
        \Cake\Log\Log::write('debug', 'Full URL: ' . $this->request->getUri());
        
        if (!$token) {
            \Cake\Log\Log::write('warning', 'Reset password accessed without token');
            $this->Flash->error(__('Invalid password reset link. Please request a new one.'));
            return $this->redirect(['action' => 'forgotPassword']);
        }
        
        $usersTable = $this->loadModel('Users');
        
        // Debug: Check if column exists
        try {
            $schema = $usersTable->getSchema();
            $hasTokenColumn = $schema->hasColumn('password_reset_token');
            \Cake\Log\Log::write('debug', 'password_reset_token column exists: ' . ($hasTokenColumn ? 'YES' : 'NO'));
        } catch (\Exception $e) {
            \Cake\Log\Log::write('error', 'Error checking schema: ' . $e->getMessage());
        }
        
        $user = $usersTable->find()
            ->where(['password_reset_token' => $token])
            ->first();
        
        if (!$user) {
            \Cake\Log\Log::write('warning', 'Reset password token not found in database: ' . $token);
            // Check if ANY user has this token (case sensitivity issue?)
            $allUsers = $usersTable->find()
                ->where(['password_reset_token IS NOT' => null])
                ->select(['id', 'email', 'password_reset_token'])
                ->limit(5)
                ->toArray();
            \Cake\Log\Log::write('debug', 'Users with reset tokens: ' . count($allUsers));
            foreach ($allUsers as $u) {
                \Cake\Log\Log::write('debug', 'User ' . $u->email . ' has token: ' . substr($u->password_reset_token, 0, 20) . '...');
            }
            
            $this->Flash->error(__('Invalid or expired password reset link. Please request a new one.'));
            return $this->redirect(['action' => 'forgotPassword']);
        }
        
        \Cake\Log\Log::write('debug', 'User found for reset token: ' . $user->email);
        
        // Check if token is expired
        $expiresAt = $user->password_reset_expires;
        \Cake\Log\Log::write('debug', 'Token expires at: ' . ($expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'NULL'));
        \Cake\Log\Log::write('debug', 'Current time: ' . (new \DateTime())->format('Y-m-d H:i:s'));
        
        if (!$expiresAt || $expiresAt < new \DateTime()) {
            \Cake\Log\Log::write('warning', 'Token expired for user: ' . $user->email);
            $this->Flash->error(__('Password reset link has expired. Please request a new one.'));
            return $this->redirect(['action' => 'forgotPassword']);
        }
        
        if ($this->request->is('post')) {
            $password = $this->request->getData('password');
            $confirmPassword = $this->request->getData('password_confirm');
            
            if ($password && $confirmPassword && $password === $confirmPassword) {
                $user->password = $password;
                $user->password_reset_token = null;
                $user->password_reset_expires = null;
                $user->failed_login_attempts = 0;
                $user->account_locked_until = null;
                
                // Validate password only
                $user->setDirty('password', true);
                
                if ($usersTable->save($user, ['validate' => 'default'])) {
                    $this->Flash->success(__('Password has been reset successfully. You can now login with your new password.'));
                    return $this->redirect(['action' => 'login']);
                } else {
                    $errors = $user->getErrors();
                    if (isset($errors['password'])) {
                        foreach ($errors['password'] as $error) {
                            $this->Flash->error($error);
                        }
                    } else {
                        $this->Flash->error(__('Failed to reset password. Please try again.'));
                    }
                }
            } else {
                $this->Flash->error(__('Passwords do not match.'));
            }
        }
        
        $this->set(compact('token'));
    }

    public function beforeFilter(
        \Cake\Event\EventInterface $event
    ) {
        parent::beforeFilter($event);
        // Allow these endpoints to be called without authentication:
        // - approvalCallback: server-to-server from Flask admin
        // - verifyEmail: users clicking verification link in email
        // - register: new user registration
        // - registrationPending: status page after registration
        // - forgotPassword: password reset request
        // - resetPassword: password reset with token
        try {
            if (isset($this->Authentication)) {
                $this->Authentication->addUnauthenticatedActions([
                    'approvalCallback',
                    'verifyEmail',
                    'register',
                    'registrationPending',
                    'forgotPassword',
                    'resetPassword'
                ]);
            }
        } catch (\Throwable $_) {
            \Cake\Log\Log::write('error', 'Failed to register unauthenticated actions: ' . $_->getMessage());
        }
    }

    /**
     * Endpoint for Flask to POST approval/rejection callbacks.
     * Expects JSON or form-data with: { teacher_id, status }
     * status should be 'approved' or 'rejected' (or 1/0).
     */
    public function approvalCallback()
    {
        $this->request->allowMethod(['post']);

        // Verify HMAC signature (if configured)
        try {
            // Prefer raw_body attribute if middleware captured it; fall back to php://input
            $attrRaw = $this->request->getAttribute('raw_body');
            if (!empty($attrRaw)) {
                $rawBody = (string)$attrRaw;
            } else {
                $rawBody = (string)$this->request->getInput();
            }
            $sigHeader = trim($this->request->getHeaderLine('X-Callback-Signature') ?: '');
            // Log other request-level headers helpful for debugging
            // Do not emit header-level debug logging here to avoid leaking sensitive headers.
            $secret = getenv('CALLBACK_SECRET') ?: (Configure::read('App.callbackSecret') ?: '');
            if (!empty($secret)) {
                // normalize header: accept 'sha256=<hex>' or just hex
                $provided = $sigHeader;
                if (strpos($provided, 'sha256=') === 0) {
                    $provided = substr($provided, 7);
                }
                // Compute a short hex preview of the raw body to detect byte-level
                // differences (BOMs, trailing newlines, CRLF vs LF). Limit size to avoid
                // huge log entries.
                $rawPreview = substr($rawBody, 0, 128);
                $rawHexPrefix = bin2hex($rawPreview);
                $expected = hash_hmac('sha256', $rawBody, $secret);
                $matches = (function_exists('hash_equals') && hash_equals($expected, $provided));
                if (!$matches) {
                    \Cake\Log\Log::write('warning', 'approvalCallback signature mismatch. provided=' . $sigHeader . ' expected_prefix=sha256=' . substr($expected, 0, 16));
                    $this->response = $this->response->withType('application/json')
                        ->withStatus(403)
                        ->withStringBody(json_encode(['success' => false, 'message' => 'invalid signature']));
                    return $this->response;
                }
            } else {
                // No secret configured: allow but log (development mode)
                \Cake\Log\Log::write('warning', 'approvalCallback: no CALLBACK_SECRET configured; accepting unsigned callback');
            }
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'approvalCallback signature verification error: ' . $e->getMessage());
            $this->response = $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode(['success' => false, 'message' => 'signature verification error']));
            return $this->response;
        }

        $data = $this->request->getData() ?: json_decode($rawBody, true);
        $teacherId = $data['teacher_id'] ?? $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$teacherId || $status === null) {
            \Cake\Log\Log::write('warning', 'approvalCallback missing parameters: ' . json_encode($data));
            $this->response = $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'message' => 'missing teacher_id or status']));
            return $this->response;
        }

        try {
            $usersTable = $this->loadModel('Users');
            $user = $usersTable->get($teacherId);
            // Normalize status: accept 'approved', 'approve', '1' as approval
            $approved = in_array(strtolower((string)$status), ['approved', 'approve', '1', 'true'], true);

            if ($approved) {
                $user->status = 1;
                if ($usersTable->save($user)) {
                    \Cake\Log\Log::write('debug', 'approvalCallback approved user ' . $teacherId . ' status=' . $status);
                    $this->response = $this->response->withType('application/json')
                        ->withStringBody(json_encode(['success' => true]));
                    return $this->response;
                }
                \Cake\Log\Log::write('error', 'approvalCallback failed to save (approve) user ' . $teacherId);
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode(['success' => false, 'message' => 'failed to save user']));
                return $this->response;
            } else {
                // On rejection, delete the pending user record so it cannot be used.
                try {
                    if ($usersTable->delete($user)) {
                        \Cake\Log\Log::write('info', 'approvalCallback rejected and deleted user ' . $teacherId);
                        $this->response = $this->response->withType('application/json')
                            ->withStringBody(json_encode(['success' => true, 'deleted' => true]));
                        return $this->response;
                    } else {
                        \Cake\Log\Log::write('error', 'approvalCallback failed to delete rejected user ' . $teacherId);
                        $this->response = $this->response->withType('application/json')
                            ->withStringBody(json_encode(['success' => false, 'message' => 'failed to delete user']));
                        return $this->response;
                    }
                } catch (\Throwable $e) {
                    \Cake\Log\Log::write('error', 'approvalCallback exception while deleting user ' . $teacherId . ': ' . $e->getMessage());
                    $this->response = $this->response->withType('application/json')
                        ->withStringBody(json_encode(['success' => false, 'message' => 'exception deleting user']));
                    return $this->response;
                }
            }
        } catch (\Throwable $e) {
            \Cake\Log\Log::write('error', 'approvalCallback exception: ' . $e->getMessage());
            $this->response = $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'message' => 'exception']));
            return $this->response;
        }
    }

    // AJAX endpoint to set walkthrough_shown for logged-in user
    public function setWalkthroughShown()
    {
        $this->request->allowMethod(['post']);
        $user = $this->Authentication->getIdentity();
    \Cake\Log\Log::write('debug', 'setWalkthroughShown called. User: ' . ($user ? $user->id : 'none'));
        if ($user) {
            $usersTable = $this->loadModel('Users');
            $entity = $usersTable->get($user->id);
            $newCount = (int)($entity->walkthrough_shown ?? 0) + 1;
            \Cake\Log\Log::write('debug', 'Current walkthrough_shown: ' . $entity->walkthrough_shown . ', newCount: ' . $newCount);
            $entity = $usersTable->patchEntity($entity, ['walkthrough_shown' => $newCount]);
            if ($usersTable->save($entity)) {
                \Cake\Log\Log::write('debug', 'walkthrough_shown updated and saved for user ' . $user->id);
            } else {
                \Cake\Log\Log::write('error', 'Failed to save walkthrough_shown for user ' . $user->id);
            }
            // Force session/identity refresh
            $fresh = $usersTable->get($user->id);
            $this->Authentication->setIdentity($fresh);
            $this->autoRender = false;
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode(['success' => true, 'walkthrough_shown' => $fresh->walkthrough_shown]));
            return $this->response;
        }
        throw new \Cake\Http\Exception\UnauthorizedException('Not logged in');
    }

    /**
     * Email verification endpoint
     * 
     * @param string|null $token Verification token from email
     * @return \Cake\Http\Response|null
     */
    public function verifyEmail($token = null)
    {
        $this->viewBuilder()->setLayout('login');
        
        if (!$token) {
            $this->Flash->error(__('Invalid verification link.'));
            return $this->redirect(['action' => 'login']);
        }

        $usersTable = $this->loadModel('Users');
        $user = $usersTable->find()
            ->where([
                'verification_token' => $token,
                'email_verified' => 0
            ])
            ->first();

        if (!$user) {
            $this->Flash->error(__('Invalid or expired verification link.'));
            return $this->redirect(['action' => 'login']);
        }

        // Check if token is expired
        $now = new \DateTime();
        if ($user->verification_token_expires && $user->verification_token_expires < $now) {
            $this->Flash->error(__('Verification link has expired. Please request a new one.'));
            return $this->redirect(['action' => 'login']);
        }

        // Verify the email
        $user->email_verified = 1;
        $user->verification_token = null;
        $user->verification_token_expires = null;

        if ($usersTable->save($user)) {
            // Now notify Flask admin panel about verified teacher registration
            try {
                $flaskUrl = getenv('FLASK_PENDING_URL') ?: 'http://127.0.0.1:5000/api/pending_teachers';
                $flaskKey = getenv('FLASK_API_KEY') ?: '';
                $http = new Client();
                
                $envBase = getenv('APP_BASE_URL') ?: '';
                if (empty($envBase)) {
                    $cfg = Configure::read('App.fullBaseUrl');
                    if (!empty($cfg)) { $envBase = $cfg; }
                }
                if (!empty($envBase)) {
                    $baseUrl = rtrim($envBase, '\/');
                } else {
                    try {
                        $uri = $this->request->getUri();
                        $scheme = $uri->getScheme() ?: 'http';
                        $host = $uri->getHost();
                        $port = $uri->getPort();
                        $baseUrl = $scheme . '://' . $host;
                        if ($port && !in_array($port, [80, 443])) {
                            $baseUrl .= ':' . $port;
                        }
                    } catch (\Throwable $_) {
                        $baseUrl = 'http://127.0.0.1';
                    }
                }

                $appBase = Configure::read('App.base') ?: '';
                $callbackBase = rtrim($baseUrl, '\\/') . ($appBase ? rtrim($appBase, '/') : '');
                $payload = [
                    'teacher_id' => (string)$user->id,
                    'email' => $user->email,
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'callback_url' => $callbackBase . '/users/approvalCallback'
                ];
                $headers = ['Content-Type' => 'application/json'];
                if (!empty($flaskKey)) { $headers['X-API-Key'] = $flaskKey; }
                $resp = $http->post($flaskUrl, json_encode($payload), ['headers' => $headers, 'timeout' => 5]);
                \Cake\Log\Log::write('debug', 'Notified Flask pending_teachers: ' . $flaskUrl . ' -> ' . $resp->getStatusCode());
            } catch (\Throwable $e) {
                \Cake\Log\Log::write('warning', 'Failed to notify Flask about verified teacher: ' . $e->getMessage());
            }

            $this->Flash->success(__('✓ Email verified successfully! Your account has been sent to the administrator for approval. You will receive an email once your account is approved and you can login.'));
        } else {
            $this->Flash->error(__('Unable to verify email. Please try again or contact support.'));
        }

        return $this->redirect(['action' => 'login']);
    }

    /**
     * Registration pending page - shown after email verification
     * 
     * @return void
     */
    public function registrationPending()
    {
        $this->viewBuilder()->setLayout('login');
    }
}


