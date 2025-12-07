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
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?= $this->Form->text('first_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['first_name']['class'] ?? ''), 'id' => 'first_name', 'placeholder' => 'First Name', 'required' => 'required', 'pattern' => '[a-zA-Z\s\'-]+', 'title' => 'Only letters, spaces, hyphens, and apostrophes allowed']) ?>
                <div class="invalid-feedback"><?= $fieldErrors['first_name']['message'] ?? '' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= $this->Form->text('last_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['last_name']['class'] ?? ''), 'id' => 'last_name', 'placeholder' => 'Last Name', 'required' => 'required', 'pattern' => '[a-zA-Z\s\'-]+', 'title' => 'Only letters, spaces, hyphens, and apostrophes allowed']) ?>
                <div class="invalid-feedback"><?= $fieldErrors['last_name']['message'] ?? '' ?></div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= $this->Form->email('email', ['class' => 'form-control form-control-lg ' . ($fieldErrors['email']['class'] ?? ''), 'id' => 'email', 'placeholder' => 'Email Address', 'required' => 'required', 'title' => 'Enter your email address']) ?>
        <small id="email-instruction" class="form-text text-muted mt-1" style="font-size: 0.8rem;">
            <i class="mdi mdi-information-outline"></i> Use a valid email format (e.g., user@example.com)
        </small>
        <div class="invalid-feedback"><?= $fieldErrors['email']['message'] ?? '' ?></div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative">
                <?= $this->Form->text('password', ['class' => 'form-control form-control-lg ' . ($fieldErrors['password']['class'] ?? ''), 'id' => 'password', 'placeholder' => 'Password', 'required' => 'required', 'minlength' => '8', 'maxlength' => '32', 'autocomplete' => 'new-password']) ?>
                <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility"><i class="mdi mdi-eye-off-outline" aria-hidden="true"></i></button>
                <div class="invalid-feedback"><?= $fieldErrors['password']['message'] ?? '' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= $this->Form->text('confirm_password', ['class' => 'form-control form-control-lg', 'id' => 'confirm_password', 'placeholder' => 'Confirm Password', 'required' => 'required', 'autocomplete' => 'new-password']) ?>
                <small id="password-match-indicator" class="form-text" style="font-size: 0.75rem; min-height: 20px; display: block; margin-top: 0.25rem;" aria-live="polite"></small>
            </div>
        </div>
    </div>
    <div id="password-instruction" class="small mb-2 px-2 py-1 text-muted" style="border-radius: 4px; font-size: 0.8rem; background-color: #f8f9fa; border: 1px solid #e9ecef;">
        <i class="mdi mdi-information-outline"></i> Password must have: 8-32 characters, uppercase, lowercase, number, special character (@,#,!,$,%)
    </div>
    <div id="password-strength-indicator" class="small mb-2 px-2 py-1" style="display:none; border-radius: 4px; font-size: 0.8rem;" role="alert">
        <i class="mdi mdi-information-outline"></i> <span id="password-strength-text">Password requirements</span>
    </div>
    <div class="mb-3">
        <div class="form-check custom-checkbox">
            <?= $this->Form->checkbox('terms_and_conditions', ['class' => 'form-check-input', 'required' => 'required', 'id' => 'terms_checkbox']) ?>
            <label class="form-check-label text-muted" for="terms_checkbox">
                I agree to the <a href="#" id="open-terms-modal" class="text-primary">Terms & Conditions</a>
            </label>
        </div>
    </div>
    <div class="mt-3">
        <?= $this->Form->button('SIGN UP', ['class' => 'btn btn-primary btn-block btn-lg', 'type' => 'submit']) ?>
    </div>
    <div class="text-center mt-3 font-weight-light">
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

    // Password field with character-by-character masking when hidden
    let actualPassword = '';
    let lastCharTimer = null;
    let passwordVisible = false; // Track if password is shown
    let maskingEnabled = true; // Flag to temporarily disable masking during toggle

    // Confirm password field variables (also in outer scope for toggle access)
    let actualConfirmPassword = '';
    let confirmLastCharTimer = null;
    let confirmPasswordVisible = false;
    let confirmMaskingEnabled = true; // Flag to temporarily disable masking during toggle
    
    window.__passwordRevealed = false; // Global flag for mascot.js to check
    
    if (passwordField) {
        // Initialize - if field already has value (autocomplete), mask it
        if (passwordField.value && !passwordField.value.includes('•')) {
            actualPassword = passwordField.value;
            passwordField.value = '•'.repeat(actualPassword.length);
            validatePasswordStrength(actualPassword);
        }
        
        // Handle autocomplete/paste - detect when field suddenly has text without bullets
        passwordField.addEventListener('change', function() {
            if (!passwordVisible && this.value && !this.value.includes('•')) {
                actualPassword = this.value;
                this.value = '•'.repeat(actualPassword.length);
                validatePasswordStrength(actualPassword);
                if (confirmPasswordField && actualConfirmPassword) {
                    checkPasswordMatch();
                }
            }
        });
        
        passwordField.addEventListener('input', function(e) {
            if (passwordVisible) {
                // When password is visible, just validate directly
                actualPassword = this.value;
                validatePasswordStrength(actualPassword);
            } else {
                if (!maskingEnabled) return; // Skip masking if temporarily disabled
                
                // When password is hidden, use character-by-character masking
                const currentValue = this.value;
                const cursorPos = this.selectionStart;
                
                // Handle paste - if we get many chars at once without bullets
                if (currentValue && !currentValue.includes('•') && currentValue.length > actualPassword.length + 1) {
                    actualPassword = currentValue;
                    this.value = '•'.repeat(actualPassword.length);
                    this.setSelectionRange(cursorPos, cursorPos);
                    validatePasswordStrength(actualPassword);
                } else if (currentValue.length > actualPassword.length) {
                    // Character(s) added normally
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
                        if (!passwordVisible && passwordField.value.length === actualPassword.length) {
                            passwordField.value = '•'.repeat(actualPassword.length);
                            passwordField.setSelectionRange(cursorPos, cursorPos);
                        }
                    }, 500);
                    validatePasswordStrength(actualPassword);
                } else if (currentValue.length < actualPassword.length) {
                    // Character(s) deleted
                    const deletedCount = actualPassword.length - currentValue.length;
                    actualPassword = actualPassword.substring(0, cursorPos) + actualPassword.substring(cursorPos + deletedCount);
                    
                    // Show all as masked
                    clearTimeout(lastCharTimer);
                    this.value = '•'.repeat(actualPassword.length);
                    this.setSelectionRange(cursorPos, cursorPos);
                    validatePasswordStrength(actualPassword);
                }
            }
            
            // Check if passwords match
            if (confirmPasswordField && actualConfirmPassword) {
                checkPasswordMatch();
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
    
    // Password visibility toggle - switches between masking and full visibility for BOTH fields
    const togglePasswordBtn = document.getElementById('toggle-password-visibility');
    if (togglePasswordBtn && passwordField) {
        togglePasswordBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            clearTimeout(lastCharTimer);
            clearTimeout(confirmLastCharTimer);
            
            passwordVisible = !passwordVisible;
            confirmPasswordVisible = !confirmPasswordVisible;
            
            if (passwordVisible) {
                // SHOW PASSWORD STATE: Disable masking and show actual passwords
                maskingEnabled = false;
                confirmMaskingEnabled = false;
                window.__passwordRevealed = true; // Signal to mascot.js
                
                // Show full actual passwords in both fields (plain text, not bullets)
                passwordField.value = actualPassword;
                if (confirmPasswordField) {
                    confirmPasswordField.value = actualConfirmPassword;
                }
                icon.classList.remove('mdi-eye-off-outline');
                icon.classList.add('mdi-eye-outline');
                
                // Mascot shows peaking eyes (one eye closed, one peeking)
                if (typeof window.showEyes === 'function') {
                    window.showEyes('peak', true);
                }
            } else {
                // HIDDEN PASSWORD STATE: Enable masking and show bullets
                window.__passwordRevealed = false; // Signal to mascot.js
                
                // Show masked passwords in both fields (bullets)
                passwordField.value = '•'.repeat(actualPassword.length);
                if (confirmPasswordField) {
                    confirmPasswordField.value = '•'.repeat(actualConfirmPassword.length);
                }
                icon.classList.remove('mdi-eye-outline');
                icon.classList.add('mdi-eye-off-outline');
                
                // Re-enable masking for character-by-character typing
                setTimeout(() => {
                    maskingEnabled = true;
                    confirmMaskingEnabled = true;
                }, 50);
                
                // Mascot shows closed eyes (default state)
                if (typeof window.showEyes === 'function') {
                    window.showEyes('closed', true);
                }
            }
        });
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
        if (confirmPasswordField && typeof actualConfirmPassword !== 'undefined' && actualConfirmPassword) {
            checkPasswordMatchWithActual();
        }
    }

    function checkPasswordMatch() {
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

    // Email field - hide instruction after typing
    const emailField = document.getElementById('email');
    const emailInstruction = document.getElementById('email-instruction');
    const passwordInstruction = document.getElementById('password-instruction');
    
    if (emailField && emailInstruction) {
        emailField.addEventListener('input', function() {
            if (this.value.length > 0) {
                emailInstruction.style.display = 'none';
            } else {
                emailInstruction.style.display = 'block';
            }
        });
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

    // Confirm password field event handlers (variables already declared at top)
    if (confirmPasswordField) {
        // Initialize - if field already has value (autocomplete), mask it
        if (confirmPasswordField.value && !confirmPasswordField.value.includes('•')) {
            actualConfirmPassword = confirmPasswordField.value;
            confirmPasswordField.value = '•'.repeat(actualConfirmPassword.length);
            checkPasswordMatch();
        }
        
        // Handle autocomplete/paste
        confirmPasswordField.addEventListener('change', function() {
            if (!confirmPasswordVisible && this.value && !this.value.includes('•')) {
                actualConfirmPassword = this.value;
                this.value = '•'.repeat(actualConfirmPassword.length);
                checkPasswordMatch();
            }
        });
        
        confirmPasswordField.addEventListener('input', function(e) {
            if (confirmPasswordVisible) {
                // When password is visible, just validate directly
                actualConfirmPassword = this.value;
            } else {
                if (!confirmMaskingEnabled) return; // Skip masking if temporarily disabled
                
                // When password is hidden, use character-by-character masking
                const currentValue = this.value;
                const cursorPos = this.selectionStart;
                
                // Handle paste
                if (currentValue && !currentValue.includes('•') && currentValue.length > actualConfirmPassword.length + 1) {
                    actualConfirmPassword = currentValue;
                    this.value = '•'.repeat(actualConfirmPassword.length);
                    this.setSelectionRange(cursorPos, cursorPos);
                } else if (currentValue.length > actualConfirmPassword.length) {
                    const addedChars = currentValue.length - actualConfirmPassword.length;
                    const insertPos = cursorPos - addedChars;
                    const newChars = currentValue.substring(insertPos, cursorPos);
                    
                    actualConfirmPassword = actualConfirmPassword.substring(0, insertPos) + newChars + actualConfirmPassword.substring(insertPos);
                    
                    clearTimeout(confirmLastCharTimer);
                    const maskedValue = '•'.repeat(actualConfirmPassword.length - 1) + actualConfirmPassword.charAt(actualConfirmPassword.length - 1);
                    this.value = maskedValue;
                    this.setSelectionRange(cursorPos, cursorPos);
                    
                    confirmLastCharTimer = setTimeout(() => {
                        if (!confirmPasswordVisible && confirmPasswordField.value.length === actualConfirmPassword.length) {
                            confirmPasswordField.value = '•'.repeat(actualConfirmPassword.length);
                            confirmPasswordField.setSelectionRange(cursorPos, cursorPos);
                        }
                    }, 500);
                } else if (currentValue.length < actualConfirmPassword.length) {
                    const deletedCount = actualConfirmPassword.length - currentValue.length;
                    actualConfirmPassword = actualConfirmPassword.substring(0, cursorPos) + actualConfirmPassword.substring(cursorPos + deletedCount);
                    
                    clearTimeout(confirmLastCharTimer);
                    this.value = '•'.repeat(actualConfirmPassword.length);
                    this.setSelectionRange(cursorPos, cursorPos);
                }
            }
            
            checkPasswordMatch();
        });
        
        const form = confirmPasswordField.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                confirmPasswordField.value = actualConfirmPassword;
            });
        }
    }
    

    
    // Terms & Conditions Modal
    const openTermsModal = document.getElementById('open-terms-modal');
    const termsCheckbox = document.getElementById('terms_checkbox');
    
    if (openTermsModal) {
        openTermsModal.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showTermsModal();
        });
    }
    
    // Prevent checkbox from being checked directly - force modal interaction
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function(e) {
            // If checkbox is being checked (not via modal), prevent it and show modal
            if (this.checked && !this.dataset.acceptedViaModal) {
                e.preventDefault();
                this.checked = false;
                showTermsModal();
            }
            // If unchecking, allow it and clear the flag
            else if (!this.checked) {
                delete this.dataset.acceptedViaModal;
            }
        });
    }
    
    function showTermsModal() {
        const modalHTML = `
            <div class="modal fade show" id="termsModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h5 class="modal-title"><i class="mdi mdi-file-document-outline"></i> Terms & Conditions</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                            <h6 class="font-weight-bold">1. Acceptance of Terms</h6>
                            <p>By creating an account on GENTA (Gamified Educational and Networking Tool for Academics), you agree to comply with and be bound by these Terms and Conditions.</p>
                            
                            <h6 class="font-weight-bold">2. User Accounts</h6>
                            <p>You are responsible for maintaining the confidentiality of your account credentials. You must:</p>
                            <ul>
                                <li>Provide accurate and complete information during registration</li>
                                <li>Keep your password secure and not share it with others</li>
                                <li>Notify us immediately of any unauthorized access to your account</li>
                                <li>Be responsible for all activities that occur under your account</li>
                            </ul>
                            
                            <h6 class="font-weight-bold">3. Acceptable Use</h6>
                            <p>You agree to use GENTA only for lawful educational purposes. You will not:</p>
                            <ul>
                                <li>Upload or share inappropriate, offensive, or copyrighted content</li>
                                <li>Attempt to gain unauthorized access to the system</li>
                                <li>Interfere with or disrupt the service</li>
                                <li>Impersonate others or misrepresent your identity</li>
                                <li>Use the platform for commercial purposes without authorization</li>
                            </ul>
                            
                            <h6 class="font-weight-bold">4. Privacy and Data Protection</h6>
                            <p>We respect your privacy and are committed to protecting your personal data:</p>
                            <ul>
                                <li>Your personal information will be collected, stored, and processed in accordance with applicable data protection laws</li>
                                <li>We will not share your data with third parties without your consent, except as required by law</li>
                                <li>You have the right to access, correct, or delete your personal information</li>
                            </ul>
                            
                            <h6 class="font-weight-bold">5. Academic Integrity</h6>
                            <p>All users must maintain academic honesty:</p>
                            <ul>
                                <li>Quiz answers must be your own work</li>
                                <li>Sharing quiz answers or questions with others is prohibited</li>
                                <li>Cheating or plagiarism will result in account suspension</li>
                            </ul>
                            
                            <h6 class="font-weight-bold">6. Intellectual Property</h6>
                            <p>All content, features, and functionality of GENTA are owned by the platform and protected by intellectual property laws. You may not:</p>
                            <ul>
                                <li>Copy, modify, or distribute platform content without permission</li>
                                <li>Reverse engineer or attempt to extract source code</li>
                                <li>Remove copyright or proprietary notices</li>
                            </ul>
                            
                            <h6 class="font-weight-bold">7. Termination</h6>
                            <p>We reserve the right to suspend or terminate your account if you violate these Terms and Conditions or engage in conduct that we deem harmful to the platform or other users.</p>
                            
                            <h6 class="font-weight-bold">8. Disclaimer of Warranties</h6>
                            <p>GENTA is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted, secure, or error-free.</p>
                            
                            <h6 class="font-weight-bold">9. Limitation of Liability</h6>
                            <p>We shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the platform.</p>
                            
                            <h6 class="font-weight-bold">10. Changes to Terms</h6>
                            <p>We reserve the right to modify these Terms and Conditions at any time. Continued use of the platform after changes constitutes acceptance of the updated terms.</p>
                            
                            <h6 class="font-weight-bold">11. Contact Information</h6>
                            <p>For questions about these Terms and Conditions, please contact the GENTA administration team.</p>
                            
                            <hr>
                            <p class="text-muted small"><strong>Last Updated:</strong> December 6, 2025</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="accept-terms-btn">I Accept</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modal = document.getElementById('termsModal');
        const closeBtn = modal.querySelector('.close');
        const dismissBtns = modal.querySelectorAll('[data-dismiss="modal"]');
        const acceptBtn = document.getElementById('accept-terms-btn');
        
        function closeModal() {
            modal.style.display = 'none';
            setTimeout(() => modal.remove(), 300);
        }
        
        closeBtn.addEventListener('click', closeModal);
        // Add event listener to all dismiss buttons (Close and Cancel)
        dismissBtns.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        
        acceptBtn.addEventListener('click', function() {
            termsCheckbox.dataset.acceptedViaModal = 'true';
            termsCheckbox.checked = true;
            closeModal();
        });
    }
})();
</script>

<style>
/* Custom checkbox styling to match site theme */
.custom-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
}

.custom-checkbox .form-check-input {
    width: 20px;
    height: 20px;
    margin: 0;
    border: 2px solid #667eea;
    border-radius: 4px;
    cursor: pointer;
    position: relative;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: white;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.custom-checkbox .form-check-input:checked {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
}

.custom-checkbox .form-check-input:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 13px;
    font-weight: bold;
    line-height: 1;
}

.custom-checkbox .form-check-input:hover {
    border-color: #764ba2;
    box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.2);
}

.custom-checkbox .form-check-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.custom-checkbox .form-check-label {
    cursor: pointer;
    user-select: none;
    margin: 0;
    padding: 0;
    font-size: 0.95rem;
    line-height: 1.4;
}

#open-terms-modal {
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

#open-terms-modal:hover {
    text-decoration: underline;
    color: #764ba2 !important;
}
</style>