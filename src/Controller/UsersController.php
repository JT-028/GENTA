<?php declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
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
                // Prefer to read status directly from the identity when available.
                $identity = null;
                try { $identity = $this->Authentication->getIdentity(); } catch (\Throwable $_) { $identity = null; }

                $statusVal = null;
                $userId = null;

                if ($identity) {
                    if (is_object($identity)) {
                        if (property_exists($identity, 'status')) { $statusVal = $identity->status; }
                        if (property_exists($identity, 'id')) { $userId = $identity->id; }
                        if (!$userId && property_exists($identity, 'user_id')) { $userId = $identity->user_id; }
                    } elseif (is_array($identity)) {
                        if (array_key_exists('status', $identity)) { $statusVal = $identity['status']; }
                        if (array_key_exists('id', $identity)) { $userId = $identity['id']; }
                        if (!$userId && array_key_exists('user_id', $identity)) { $userId = $identity['user_id']; }
                    }
                }

                $usersTable = $this->loadModel('Users');
                if ($statusVal === null) {
                    if ($userId) {
                        $userEntity = $usersTable->get($userId);
                        $statusVal = $userEntity->status ?? null;
                    } else {
                        // Fallback: try to find by posted email
                        $posted = $this->request->getData() ?: [];
                        $email = isset($posted['email']) ? strtolower((string)$posted['email']) : null;
                        if ($email) {
                            $user = $usersTable->find()->where(['email' => $email])->first();
                            if ($user) {
                                $statusVal = $user->status ?? null;
                                $userId = $user->id;
                            }
                        }
                    }
                }

                if ((int)($statusVal ?? 0) !== 1) {
                    // Deny login for pending/suspended accounts
                    \Cake\Log\Log::write('warning', 'Login blocked for user ' . ($userId ?? '<unknown>') . ' due to status=' . ($statusVal ?? '<null>'));
                    $this->Flash->error(__('Your account is not active. It may be pending admin approval.'));
                    try { $this->Authentication->logout(); } catch (\Throwable $_) { }
                    return;
                }
            } catch (\Throwable $e) {
                \Cake\Log\Log::write('error', 'Error while checking user status on login: ' . $e->getMessage());
            }

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
            $this->Flash->error(__('Invalid email or password.'));
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
                        // Ensure newly registered accounts are created in a "pending" state
                        // so an admin must explicitly approve them before they can be used.
                        // If you only want this for teacher accounts, change this to set
                        // status to 0 when $this->request->getData('type') indicates teacher.
                        $user->status = 0;

                        // Attempt to save and log the result so we can track whether the notify block runs
                        try {
                            $saved = $usersTable->save($user);
                        } catch (\Throwable $e) {
                            \Cake\Log\Log::write('error', 'UsersController::register save exception: ' . $e->getMessage());
                            $saved = false;
                        }

                        \Cake\Log\Log::write('debug', 'UsersController::register: save_result=' . ($saved ? '1' : '0') . ' user_id=' . ($saved ? $saved->id : '<none>'));

                        if ($saved)
                        {
                            // Notify Flask admin panel about new teacher registration (server-to-server)
                            // Configure FLASK_PENDING_URL and FLASK_API_KEY in environment for production
                            try {
                                $flaskUrl = getenv('FLASK_PENDING_URL') ?: 'http://127.0.0.1:5000/api/pending_teachers';
                                $flaskKey = getenv('FLASK_API_KEY') ?: '';
                                $http = new Client();
                                // Build a fully-qualified callback URL. Prefer explicit APP_BASE_URL env var
                                // but fall back to the current request's host/scheme so callbacks work in dev.
                                $envBase = getenv('APP_BASE_URL') ?: '';
                                if (!empty($envBase)) {
                                    $baseUrl = rtrim($envBase, '/');
                                } else {
                                    // Use the request URI host/scheme (e.g. http://localhost:8765)
                                    $baseUrl = rtrim((string)$this->request->getUri()->getSchemeAndHttpHost(), '/');
                                }
                                $payload = [
                                    'teacher_id' => (string)$saved->id,
                                    'email' => $saved->email,
                                    'name' => trim(($saved->first_name ?? '') . ' ' . ($saved->last_name ?? '')),
                                    'callback_url' => $baseUrl . '/users/approvalCallback'
                                ];
                                \Cake\Log\Log::write('debug', 'UsersController::register: notifying Flask ' . $flaskUrl . ' payload=' . json_encode($payload));
                                $headers = ['Content-Type' => 'application/json'];
                                if (!empty($flaskKey)) { $headers['X-API-Key'] = $flaskKey; }
                                $resp = $http->post($flaskUrl, json_encode($payload), ['headers' => $headers, 'timeout' => 5]);
                                \Cake\Log\Log::write('debug', 'Notified Flask pending_teachers: ' . $flaskUrl . ' -> ' . $resp->getStatusCode() . ' body=' . (string)$resp->getBody());
                            } catch (\Throwable $e) {
                                \Cake\Log\Log::write('warning', 'Failed to notify Flask about new teacher: ' . $e->getMessage());
                            }

                            $this->Flash->success(__('You successfully registered a new account!'));
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
     * Endpoint for Flask to POST approval/rejection callbacks.
     * Expects JSON or form-data with: { teacher_id, status }
     * status should be 'approved' or 'rejected' (or 1/0).
     */
    public function approvalCallback()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData() ?: json_decode((string)$this->request->getInput(), true);
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
}


