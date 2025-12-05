<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<?php $fieldErrors = $this->Field->errors($user ?? null, ['first_name', 'last_name', 'email', 'password']); ?>

<!-- Mascot centered above the original single-column register form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<h4 class="auth-title">New Account</h4>
<div class="auth-subtitle">To register a new account, kindly fill up the following.</div>

<!-- REGISTER FORM -->
<?= $this->Form->create(NULL, ['url' => ['controller' => 'Users', 'action' => 'register']]) ?>
    <div class="form-group">
        <?= $this->Form->text('first_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['first_name']['class'] ?? ''), 'id' => 'first_name', 'placeholder' => 'First Name', 'required' => 'required', 'pattern' => '[a-zA-Z\s]+', 'title' => 'Only letters and spaces allowed']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['first_name']['message'] ?? '' ?></div>
    </div>
    <div class="form-group">
        <?= $this->Form->text('last_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['last_name']['class'] ?? ''), 'id' => 'last_name', 'placeholder' => 'Last Name', 'required' => 'required', 'pattern' => '[a-zA-Z\s]+', 'title' => 'Only letters and spaces allowed']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['last_name']['message'] ?? '' ?></div>
    </div>
    <div class="form-group">
        <?php // TEMPORARILY ACCEPTING ALL EMAILS - Remove pattern attribute to allow Gmail
              // TODO: Restore pattern='.*@deped\.gov\.ph$' when DepEd email is available ?>
        <?= $this->Form->email('email', ['class' => 'form-control form-control-lg ' . ($fieldErrors['email']['class'] ?? ''), 'id' => 'email', 'placeholder' => 'Email Address', 'required' => 'required', 'title' => 'Enter your email address']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['email']['message'] ?? '' ?></div>
        <small class="form-text text-muted">You will need to verify your email address. (Currently accepting all email addresses for testing)</small>
    </div>
    <div class="form-group position-relative">
        <?= $this->Form->password('password', ['class' => 'form-control form-control-lg ' . ($fieldErrors['password']['class'] ?? ''), 'id' => 'password', 'placeholder' => 'Password (8-16 alphanumeric)', 'required' => 'required', 'maxlength' => '16', 'pattern' => '[a-zA-Z0-9]{8,16}']) ?>
    <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility"><i class="mdi mdi-eye-off-outline" aria-hidden="true"></i></button>
        <div class="invalid-feedback"><?= $fieldErrors['password']['message'] ?? '' ?></div>
        <div id="password-strength-indicator" class="small mt-1" style="display:none;" aria-live="polite">
            <div class="d-flex align-items-center">
                <span id="password-strength-text" class="text-danger">⚠ Must be 8-16 alphanumeric characters</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= $this->Form->password('confirm_password', ['class' => 'form-control form-control-lg', 'id' => 'confirm_password', 'placeholder' => 'Confirm Password', 'required' => 'required']) ?>
        <div id="password-match-indicator" class="small text-muted mt-1" aria-live="polite"></div>
    </div>
    <div class="mb-4">
        <div class="form-check">
            <label class="form-check-label text-muted">
                <?= $this->Form->checkbox('terms_and_conditions', ['class' => 'form-check-input', 'required' => 'required']) ?> I agree to the Terms & Conditions
            </label>
        </div>
    </div>
    <div class="mt-3">
        <?= $this->Form->button('SIGN UP', ['class' => 'btn btn-primary btn-block btn-lg', 'type' => 'submit']) ?>
    </div>
    <div class="text-center mt-4 font-weight-light">
        Already have an account? <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['escape' => false, 'class' => 'text-primary']) ?>
    </div>
<?= $this->Form->end() ?>

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>" defer></script>
<script>
(function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('password-strength-indicator');
    const strengthText = document.getElementById('password-strength-text');
    const matchIndicator = document.getElementById('password-match-indicator');
    let hideTimeout = null;

    // Brief character visibility for password field
    if (passwordField) {
        passwordField.addEventListener('input', function(e) {
            const cursorPos = this.selectionStart;
            const val = this.value;
            
            // Temporarily show the last character typed
            clearTimeout(hideTimeout);
            this.type = 'text';
            
            hideTimeout = setTimeout(() => {
                this.type = 'password';
            }, 500); // Show for 500ms
            
            // Validate password strength
            validatePasswordStrength(val);
        });
    }

    function validatePasswordStrength(password) {
        if (!password) {
            strengthIndicator.style.display = 'none';
            return;
        }
        
        strengthIndicator.style.display = 'block';
        
        const isValidLength = password.length >= 8 && password.length <= 16;
        const isAlphanumeric = /^[a-zA-Z0-9]+$/.test(password);
        
        if (isValidLength && isAlphanumeric) {
            strengthText.className = 'text-success';
            strengthText.innerHTML = '✓ Password meets requirements';
            passwordField.setCustomValidity('');
        } else {
            strengthText.className = 'text-danger';
            if (!isValidLength) {
                strengthText.innerHTML = '⚠ Must be 8-16 characters';
            } else if (!isAlphanumeric) {
                strengthText.innerHTML = '⚠ Only letters and numbers allowed';
            }
            passwordField.setCustomValidity('Password must be 8-16 alphanumeric characters');
        }
        
        // Also check password match if confirm field has value
        if (confirmPasswordField && confirmPasswordField.value) {
            checkPasswordMatch();
        }
    }

    function checkPasswordMatch() {
        if (!confirmPasswordField.value) {
            matchIndicator.textContent = '';
            return;
        }
        
        if (passwordField.value === confirmPasswordField.value) {
            matchIndicator.className = 'small text-success mt-1';
            matchIndicator.textContent = '✓ Passwords match';
            confirmPasswordField.setCustomValidity('');
        } else {
            matchIndicator.className = 'small text-danger mt-1';
            matchIndicator.textContent = '✗ Passwords do not match';
            confirmPasswordField.setCustomValidity('Passwords must match');
        }
    }

    // Validate name fields (letters only)
    const firstNameField = document.getElementById('first_name');
    const lastNameField = document.getElementById('last_name');
    
    function validateNameField(field) {
        if (!field) return;
        
        field.addEventListener('input', function(e) {
            // Remove any non-letter characters except spaces
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
    }
    
    validateNameField(firstNameField);
    validateNameField(lastNameField);

    // Confirm password matching
    if (confirmPasswordField) {
        confirmPasswordField.addEventListener('input', checkPasswordMatch);
    }
})();
</script>