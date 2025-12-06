<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- RESET PASSWORD TITLE -->
<h4 class="auth-title"><i class="mdi mdi-lock-reset"></i> Reset Your Password</h4>
<div class="auth-subtitle">Enter your new password below.</div>

<!-- RESET PASSWORD FORM -->
<?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'resetPassword', 'prefix' => false]]) ?>
    <?= $this->Form->hidden('token', ['value' => $token]) ?>
    
    <div class="form-group position-relative">
        <?= $this->Form->password('password', [
            'class' => 'form-control form-control-lg', 
            'id' => 'password', 
            'placeholder' => 'New Password', 
            'required' => 'required',
            'minlength' => '8',
            'maxlength' => '32',
            'aria-label' => 'New Password',
            'autocomplete' => 'new-password'
        ]) ?>
        <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility">
            <i class="mdi mdi-eye-off-outline" aria-hidden="true"></i>
        </button>
    </div>
    
    <div id="password-instruction" class="small mb-2 px-2 py-1 text-muted" style="border-radius: 4px; font-size: 0.8rem; background-color: #f8f9fa; border: 1px solid #e9ecef;">
        <i class="mdi mdi-information-outline"></i> Password must have: 8-32 characters, uppercase, lowercase, number, special character (@,#,!,$,%)
    </div>
    
    <div id="password-strength-indicator" class="small mb-2 px-2 py-1" style="display:none; border-radius: 4px; font-size: 0.8rem;" role="alert">
        <i class="mdi mdi-information-outline"></i> <span id="password-strength-text">Password requirements</span>
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
        <small id="password-match-indicator" class="form-text" style="font-size: 0.75rem; min-height: 20px; display: block; margin-top: 0.25rem;" aria-live="polite"></small>
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

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>" defer></script>
<script>
(function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirm');
    const strengthIndicator = document.getElementById('password-strength-indicator');
    const strengthText = document.getElementById('password-strength-text');
    const matchIndicator = document.getElementById('password-match-indicator');
    const passwordInstruction = document.getElementById('password-instruction');
    let hideTimeout = null;

    // Brief character visibility for password field (per character)
    let actualPassword = '';
    let lastCharTimer = null;
    
    if (passwordField) {
        passwordField.addEventListener('input', function(e) {
            const currentValue = this.value;
            const cursorPos = this.selectionStart;
            
            // Detect what changed
            if (currentValue.length > actualPassword.length) {
                // Character(s) added
                const addedChars = currentValue.length - actualPassword.length;
                const insertPos = cursorPos - addedChars;
                const newChars = currentValue.substring(insertPos, cursorPos);
                
                // Update actual password
                actualPassword = actualPassword.substring(0, insertPos) + newChars + actualPassword.substring(insertPos);
                
                // Show last typed character briefly, mask others
                clearTimeout(lastCharTimer);
                const maskedValue = '•'.repeat(actualPassword.length - 1) + actualPassword.charAt(actualPassword.length - 1);
                this.value = maskedValue;
                this.setSelectionRange(cursorPos, cursorPos);
                
                // Mask all characters after 500ms
                lastCharTimer = setTimeout(() => {
                    if (passwordField.value.length === actualPassword.length) {
                        passwordField.value = '•'.repeat(actualPassword.length);
                        passwordField.setSelectionRange(cursorPos, cursorPos);
                    }
                }, 500);
            } else if (currentValue.length < actualPassword.length) {
                // Character(s) deleted
                const deletedCount = actualPassword.length - currentValue.length;
                actualPassword = actualPassword.substring(0, cursorPos) + actualPassword.substring(cursorPos + deletedCount);
                
                // Show all as masked
                clearTimeout(lastCharTimer);
                this.value = '•'.repeat(actualPassword.length);
                this.setSelectionRange(cursorPos, cursorPos);
            }
            
            // Validate password strength with actual password
            validatePasswordStrength(actualPassword);
        });
        
        // Prevent copying masked characters
        passwordField.addEventListener('copy', function(e) {
            e.preventDefault();
            if (e.clipboardData) {
                e.clipboardData.setData('text/plain', actualPassword);
            }
        });
        
        // Prevent cutting masked characters
        passwordField.addEventListener('cut', function(e) {
            e.preventDefault();
            if (e.clipboardData) {
                e.clipboardData.setData('text/plain', actualPassword);
                actualPassword = '';
                this.value = '';
                validatePasswordStrength('');
            }
        });
        
        // On form submit, use actual password
        const form = passwordField.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                passwordField.value = actualPassword;
            });
        }
    }

    // Brief character visibility for confirm password field (per character)
    let actualConfirmPassword = '';
    let confirmLastCharTimer = null;
    
    if (confirmPasswordField) {
        confirmPasswordField.addEventListener('input', function(e) {
            const currentValue = this.value;
            const cursorPos = this.selectionStart;
            
            // Detect what changed
            if (currentValue.length > actualConfirmPassword.length) {
                // Character(s) added
                const addedChars = currentValue.length - actualConfirmPassword.length;
                const insertPos = cursorPos - addedChars;
                const newChars = currentValue.substring(insertPos, cursorPos);
                
                // Update actual confirm password
                actualConfirmPassword = actualConfirmPassword.substring(0, insertPos) + newChars + actualConfirmPassword.substring(insertPos);
                
                // Show last typed character briefly, mask others
                clearTimeout(confirmLastCharTimer);
                const maskedValue = '•'.repeat(actualConfirmPassword.length - 1) + actualConfirmPassword.charAt(actualConfirmPassword.length - 1);
                this.value = maskedValue;
                this.setSelectionRange(cursorPos, cursorPos);
                
                // Mask all characters after 500ms
                confirmLastCharTimer = setTimeout(() => {
                    if (confirmPasswordField.value.length === actualConfirmPassword.length) {
                        confirmPasswordField.value = '•'.repeat(actualConfirmPassword.length);
                        confirmPasswordField.setSelectionRange(cursorPos, cursorPos);
                    }
                }, 500);
            } else if (currentValue.length < actualConfirmPassword.length) {
                // Character(s) deleted
                const deletedCount = actualConfirmPassword.length - currentValue.length;
                actualConfirmPassword = actualConfirmPassword.substring(0, cursorPos) + actualConfirmPassword.substring(cursorPos + deletedCount);
                
                // Show all as masked
                clearTimeout(confirmLastCharTimer);
                this.value = '•'.repeat(actualConfirmPassword.length);
                this.setSelectionRange(cursorPos, cursorPos);
            }
            
            // Check password match with actual passwords
            checkPasswordMatchWithActual();
        });
        
        // Prevent copying masked characters
        confirmPasswordField.addEventListener('copy', function(e) {
            e.preventDefault();
            if (e.clipboardData) {
                e.clipboardData.setData('text/plain', actualConfirmPassword);
            }
        });
        
        // Prevent cutting masked characters
        confirmPasswordField.addEventListener('cut', function(e) {
            e.preventDefault();
            if (e.clipboardData) {
                e.clipboardData.setData('text/plain', actualConfirmPassword);
                actualConfirmPassword = '';
                this.value = '';
                checkPasswordMatchWithActual();
            }
        });
        
        // On form submit, use actual confirm password
        const form = confirmPasswordField.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                confirmPasswordField.value = actualConfirmPassword;
            });
        }
    }

    function validatePasswordStrength(password) {
        if (!password) {
            strengthIndicator.style.display = 'none';
            if (passwordInstruction) passwordInstruction.style.display = 'block';
            return;
        }
        
        // Hide initial instruction, show dynamic indicator
        if (passwordInstruction) passwordInstruction.style.display = 'none';
        strengthIndicator.style.display = 'block';
        
        const isValidLength = password.length >= 8 && password.length <= 32;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[@#!$%&*()_+\-=\[\]{};:'",.<>?\/\\|`~]/.test(password);
        
        const requirements = [];
        if (!isValidLength) requirements.push('8-32 chars');
        if (!hasUppercase) requirements.push('uppercase');
        if (!hasLowercase) requirements.push('lowercase');
        if (!hasNumber) requirements.push('number');
        if (!hasSpecial) requirements.push('special (@,#,!,$,%)');
        
        if (requirements.length === 0) {
            strengthIndicator.className = 'small mb-2 px-2 py-1 text-success';
            strengthIndicator.style.backgroundColor = '#d4edda';
            strengthIndicator.style.border = '1px solid #c3e6cb';
            strengthText.innerHTML = '<i class="mdi mdi-check-circle"></i> Strong password';
            passwordField.setCustomValidity('');
        } else {
            strengthIndicator.className = 'small mb-2 px-2 py-1 text-warning';
            strengthIndicator.style.backgroundColor = '#fff3cd';
            strengthIndicator.style.border = '1px solid #ffeaa7';
            strengthText.innerHTML = '<i class="mdi mdi-alert"></i> Need: ' + requirements.join(', ');
            passwordField.setCustomValidity('Password does not meet requirements');
        }
        
        // Also check password match if confirm field has value
        if (confirmPasswordField && actualConfirmPassword) {
            checkPasswordMatchWithActual();
        }
    }

    function checkPasswordMatchWithActual() {
        if (!actualConfirmPassword) {
            matchIndicator.innerHTML = '';
            return;
        }
        
        if (actualPassword === actualConfirmPassword) {
            matchIndicator.className = 'form-text text-success';
            matchIndicator.innerHTML = '<i class="mdi mdi-check-circle"></i> Passwords match';
            confirmPasswordField.setCustomValidity('');
        } else {
            matchIndicator.className = 'form-text text-danger';
            matchIndicator.innerHTML = '<i class="mdi mdi-close-circle"></i> Passwords do not match';
            confirmPasswordField.setCustomValidity('Passwords must match');
        }
    }
    
    // Legacy function kept for compatibility
    function checkPasswordMatch() {
        checkPasswordMatchWithActual();
    }

    // Password visibility toggles
    let passwordVisible = false;
    const togglePasswordBtn = document.getElementById('toggle-password-visibility');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            clearTimeout(lastCharTimer);
            passwordVisible = !passwordVisible;
            
            if (passwordVisible) {
                // Show actual password
                passwordField.value = actualPassword;
                icon.classList.remove('mdi-eye-off-outline');
                icon.classList.add('mdi-eye-outline');
            } else {
                // Show masked password
                passwordField.value = '•'.repeat(actualPassword.length);
                icon.classList.remove('mdi-eye-outline');
                icon.classList.add('mdi-eye-off-outline');
            }
        });
    }

    let confirmPasswordVisible = false;
    const togglePasswordConfirmBtn = document.getElementById('toggle-password-confirm');
    if (togglePasswordConfirmBtn) {
        togglePasswordConfirmBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            clearTimeout(confirmLastCharTimer);
            confirmPasswordVisible = !confirmPasswordVisible;
            
            if (confirmPasswordVisible) {
                // Show actual confirm password
                confirmPasswordField.value = actualConfirmPassword;
                icon.classList.remove('mdi-eye-off-outline');
                icon.classList.add('mdi-eye-outline');
            } else {
                // Show masked confirm password
                confirmPasswordField.value = '•'.repeat(actualConfirmPassword.length);
                icon.classList.remove('mdi-eye-outline');
                icon.classList.add('mdi-eye-off-outline');
            }
        });
    }
})();
</script>
