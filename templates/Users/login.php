<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the original single-column form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- WELCOME TEXT -->
<div class="text-center mb-4">
    <h2 class="auth-title mb-2" style="font-weight: 600; color: #2c3e50;">Welcome Back! 👋</h2>
    <p class="auth-subtitle" style="color: #6c757d; font-size: 0.95rem;">Sign in to continue managing your quizzes, students, and results</p>
</div>

<!-- LOG IN FORM -->
<?php
// Use a canonical route-array for the login action so the generated action
// is always '/users/login' (prefixed by App.base). For safety, force no
// prefix to avoid inheriting any current routing prefix (e.g. 'teacher').
?>
<?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login', 'prefix' => false], 'id' => 'loginForm']) ?>
    <div class="form-group">
        <?= $this->Form->email('email', ['class' => 'form-control form-control-lg', 'id' => 'email', 'placeholder' => 'Email Address', 'required' => 'required', 'aria-label' => 'Email']) ?>
    </div>
    <div class="form-group position-relative">
        <?= $this->Form->password('password', ['class' => 'form-control form-control-lg', 'id' => 'password', 'placeholder' => 'Password', 'required' => 'required', 'aria-label' => 'Password']) ?>
    <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility"><i class="mdi mdi-eye-off-outline" aria-hidden="true"></i></button>
    </div>
    
    <?php if (isset($showCaptcha) && $showCaptcha): ?>
    <div class="form-group captcha-group">
        <label class="font-weight-medium">Security Check</label>
        <div class="captcha-challenge mb-2">
            <span class="captcha-question"><?= h($captchaChallenge) ?></span>
        </div>
        <?= $this->Form->control('captcha', [
            'class' => 'form-control form-control-lg',
            'placeholder' => 'Enter your answer',
            'required' => true,
            'label' => false,
            'type' => 'text',
            'autocomplete' => 'off'
        ]) ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($remainingAttempts) && $remainingAttempts < 5 && $remainingAttempts > 0): ?>
    <div class="alert alert-warning mb-3" role="alert">
        <i class="mdi mdi-alert-outline"></i>
        <strong>Warning:</strong> <?= $remainingAttempts ?> login attempt(s) remaining before temporary lockout.
    </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <div class="text-right">
            <?= $this->Html->link('Forgot Password?', ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'text-primary font-weight-medium']) ?>
        </div>
    </div>
    
    <div class="mt-3">
        <?= $this->Form->button('LOG IN', ['class' => 'btn btn-primary btn-block btn-lg font-weight-medium', 'type' => 'submit']) ?>
    </div>
    <div class="text-center mt-4 font-weight-light"> 
        <?= $this->Html->link('Create new account', ['controller' => 'Users', 'action' => 'register'], ['escape' => false, 'class' => 'text-primary']) ?>
    </div>
<?= $this->Form->end() ?>

<!-- Session Timeout Detection -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show a friendly message if the user was redirected after session timeout
    try {
        var urlParams = new URLSearchParams(window.location.search);
        var redirectParam = urlParams.get('redirect');
        if (redirectParam && typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Session Expired',
                html: '<div style="text-align: center;">Your account has been logged out due to a long period of inactivity. Please log in again to continue.</div>',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    } catch (err) {
        // Non-fatal: don't break the page if Swal or URL parsing fails
        console.warn('Session timeout helper error', err);
    }
});
</script>

<script>
// Password toggle for login page
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-password-visibility');
    const passwordField = document.getElementById('password');
    
    if (toggleBtn && passwordField) {
        // Use mousedown to prevent default focus behavior
        toggleBtn.addEventListener('mousedown', function(e) {
            e.preventDefault(); // Prevent focus from shifting away from password field
        });
        
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const icon = this.querySelector('i');
            
            // Save cursor position before toggle
            const cursorPos = passwordField.selectionStart;
            const cursorEnd = passwordField.selectionEnd;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('mdi-eye-off-outline');
                icon.classList.add('mdi-eye-outline');
                window.__passwordRevealed = true;
            } else {
                passwordField.type = 'password';
                icon.classList.remove('mdi-eye-outline');
                icon.classList.add('mdi-eye-off-outline');
                window.__passwordRevealed = false;
            }
            
            // Keep focus on password field and restore cursor position after type change completes
            requestAnimationFrame(() => {
                passwordField.focus();
                passwordField.setSelectionRange(cursorPos, cursorEnd);
                
                // Trigger mascot eye update immediately
                if (typeof window.passwordStateRefresh === 'function') {
                    window.passwordStateRefresh(true);
                }
            });
        });
    }
});
</script>

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>?v=<?= filemtime(WWW_ROOT . 'assets/js/mascot.js') ?>" defer></script>