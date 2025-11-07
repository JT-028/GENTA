<?php declare(strict_types=1);

namespace App\Controller;

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

        // VALIDATE AUTH RESULT
        if ($result && $result->isValid())
        {
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
            $user = $usersTable->patchEntity($user, $this->request->getData());

            if (!$user->hasErrors())
            {
                if ($this->request->getData('password') === $this->request->getData('confirm_password'))
                {
                    if ($this->request->getData('terms_and_conditions'))
                    {
                        if ($usersTable->save($user))
                        {
                            $this->Flash->success(__('You successfully registered a new account!'));
                            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
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


