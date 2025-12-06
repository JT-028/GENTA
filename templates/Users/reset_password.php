<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- RESET PASSWORD TITLE -->
<h4 class="auth-title"><i class="mdi mdi-lock-reset"></i> Reset Your Password</h4>
<div class="auth-subtitle">Enter your new password below.</div>

<!-- RESET PASSWORD FORM -->
<?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'resetPassword', 'prefix' => false, '?' => ['token' => $token]]]) ?>
    <div class="form-group position-relative">
        <?= $this->Form->password('password', [
            'class' => 'form-control form-control-lg', 
            'id' => 'password', 
            'placeholder' => 'New Password', 
            'required' => 'required', 
            'aria-label' => 'New Password',
            'autocomplete' => 'new-password'
        ]) ?>
        <button type="button" id="toggle-password" class="password-toggle-icon" aria-label="Toggle password visibility">
            <i class="mdi mdi-eye-off-outline" aria-hidden="true"></i>
        </button>
    </div>
    
    <div class="password-requirements mb-3">
        <small class="text-muted">
            Password must contain:
            <ul class="mb-0 mt-1">
                <li>At least 8 characters</li>
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character (@, #, !, $, %)</li>
            </ul>
        </small>
    </div>
    
    <div class="form-group position-relative">
        <?= $this->Form->password('password_confirm', [
            'class' => 'form-control form-control-lg', 
            'id' => 'password_confirm', 
            'placeholder' => 'Confirm New Password', 
            'required' => 'required', 
            'aria-label' => 'Confirm Password',
            'autocomplete' => 'new-password'
        ]) ?>
        <button type="button" id="toggle-password-confirm" class="password-toggle-icon" aria-label="Toggle password confirmation visibility">
            <i class="mdi mdi-eye-off-outline" aria-hidden="true"></i>
        </button>
    </div>
    
    <div class="mt-3">
        <?= $this->Form->button('Reset Password', [
            'class' => 'btn btn-primary btn-block btn-lg font-weight-medium', 
            'type' => 'submit'
        ]) ?>
    </div>
    
    <div class="text-center mt-4 font-weight-light">
        <i class="mdi mdi-arrow-left"></i>
        <?= $this->Html->link('Back to Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'text-primary']) ?>
    </div>
<?= $this->Form->end() ?>

<script>
// Password visibility toggle
document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('mdi-eye-off-outline');
        icon.classList.add('mdi-eye-outline');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('mdi-eye-outline');
        icon.classList.add('mdi-eye-off-outline');
    }
});

document.getElementById('toggle-password-confirm').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirm');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('mdi-eye-off-outline');
        icon.classList.add('mdi-eye-outline');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('mdi-eye-outline');
        icon.classList.add('mdi-eye-off-outline');
    }
});

// Password match validation
document.getElementById('password_confirm').addEventListener('input', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = e.target.value;
    
    if (confirmPassword && password !== confirmPassword) {
        e.target.setCustomValidity('Passwords do not match');
    } else {
        e.target.setCustomValidity('');
    }
});
</script>

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>" defer></script>
