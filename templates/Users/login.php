<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the original single-column form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- WELCOME TEXT -->
<h4 class="auth-title">Welcome back to GENTA</h4>
<div class="auth-subtitle">Sign in to access your dashboard — manage quizzes, students, and results.</div>

<!-- Real-time warning/error messages container -->
<div id="login-alerts-container"></div>

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
    
    <!-- Real-time Login Validation Warnings -->
    <div id="login-warning-container" style="display: none; margin-bottom: 1rem;">
        <div id="attempts-warning" class="alert alert-warning" role="alert" style="display: none;">
            <i class="mdi mdi-alert-outline"></i>
            <strong>Warning:</strong> <span id="attempts-count"></span> login attempt(s) remaining before temporary lockout.
        </div>
        <div id="account-not-found" class="alert alert-danger" role="alert" style="display: none;">
            <i class="mdi mdi-account-alert-outline"></i>
            <strong>Account not found:</strong> This email is not registered. Please <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>" class="alert-link">create an account</a> first.
        </div>
        <div id="account-inactive" class="alert alert-warning" role="alert" style="display: none;">
            <i class="mdi mdi-account-clock-outline"></i>
            <strong>Account not active:</strong> Your account may be pending admin approval.
        </div>
        <div id="account-locked" class="alert alert-danger" role="alert" style="display: none;">
            <i class="mdi mdi-lock-alert-outline"></i>
            <strong>Account locked:</strong> Too many failed attempts. Try again in <span id="lockout-minutes"></span> minutes.
        </div>
    </div>
    
    <div class="form-group position-relative">
        <?= $this->Form->password('password', ['class' => 'form-control form-control-lg', 'id' => 'password', 'placeholder' => 'Password', 'required' => 'required', 'aria-label' => 'Password']) ?>
    <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility"><i class="mdi mdi-eye-off-outline" aria-hidden="true"></i></button>
    </div>
    
    <!-- Dynamic CAPTCHA Container -->
    <div id="dynamic-captcha-container"></div>
    
    <div id="captcha-container" style="<?= (isset($showCaptcha) && $showCaptcha) ? '' : 'display:none;' ?>">
        <div class="form-group captcha-group">
            <label class="font-weight-medium">Security Check</label>
            <div class="captcha-challenge mb-2">
                <span id="captcha-question" class="captcha-question"><?= isset($captchaChallenge) ? h($captchaChallenge) : '' ?></span>
            </div>
            <?= $this->Form->control('captcha', [
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Enter your answer',
                'label' => false,
                'type' => 'text',
                'autocomplete' => 'off',
                'id' => 'captcha-input'
            ]) ?>
        </div>
    </div>
    
    <div id="attempts-warning-container">
        <?php if (isset($remainingAttempts) && $remainingAttempts < 5): ?>
        <div class="alert alert-warning mb-3" role="alert" id="attempts-warning">
            <i class="mdi mdi-alert-outline"></i>
            <strong>Warning:</strong> <span id="remaining-attempts-text"><?= $remainingAttempts ?></span> login attempt(s) remaining before temporary lockout.
        </div>
        <?php endif; ?>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <div class="text-right">
            <?= $this->Html->link('Forgot Password?', ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'text-primary font-weight-medium']) ?>
        </div>
    </div>
    
    <div class="mt-3">
        <?= $this->Form->button('LOG IN', ['class' => 'btn btn-primary btn-block btn-lg font-weight-medium', 'type' => 'submit', 'id' => 'login-btn']) ?>
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
    
    // AJAX Login Form Handler for real-time feedback
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('login-btn');
    const alertsContainer = document.getElementById('login-alerts-container');
    const attemptsWarningContainer = document.getElementById('attempts-warning-container');
    const captchaContainer = document.getElementById('captcha-container');
    const captchaQuestion = document.getElementById('captcha-question');
    const captchaInput = document.getElementById('captcha-input');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button and show loading state
            const originalBtnText = loginBtn.innerHTML;
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Signing in...';
            
            // Clear previous alerts
            alertsContainer.innerHTML = '';
            
            // Gather form data
            const formData = new FormData(loginForm);
            
            // Send AJAX request
            fetch(loginForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                // Check if response is a redirect (successful login)
                if (response.redirected) {
                    window.location.href = response.url;
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return; // Was a redirect
                
                // Re-enable button
                loginBtn.disabled = false;
                loginBtn.innerHTML = originalBtnText;
                
                if (data.success) {
                    // Successful login - redirect
                    window.location.href = data.redirect || '/teacher/dashboard';
                } else {
                    // Show error message with animation
                    showAlert(data.message || 'Invalid email or password.', 'danger');
                    
                    // Update remaining attempts warning
                    if (data.remainingAttempts !== undefined) {
                        updateAttemptsWarning(data.remainingAttempts);
                    }
                    
                    // Show/update CAPTCHA if needed
                    if (data.showCaptcha && data.captchaChallenge) {
                        captchaContainer.style.display = 'block';
                        captchaQuestion.textContent = data.captchaChallenge;
                        captchaInput.value = '';
                        captchaInput.required = true;
                        
                        // Highlight captcha field
                        captchaInput.classList.add('shake-animation');
                        setTimeout(() => captchaInput.classList.remove('shake-animation'), 500);
                    }
                    
                    // Handle lockout
                    if (data.rateLimited) {
                        showAlert('Too many failed login attempts. Please try again in ' + data.lockoutMinutes + ' minutes.', 'danger');
                        loginBtn.disabled = true;
                        
                        // Start countdown
                        startLockoutCountdown(data.lockoutMinutes);
                    }
                    
                    // Shake the form for visual feedback
                    loginForm.classList.add('shake-animation');
                    setTimeout(() => loginForm.classList.remove('shake-animation'), 500);
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                // Fallback: submit the form normally
                loginForm.submit();
            });
        });
    }
    
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show mb-3" role="alert" style="animation: slideInDown 0.3s ease-out;">
                <i class="mdi mdi-${type === 'danger' ? 'alert-circle' : 'check-circle'}-outline"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        alertsContainer.innerHTML = alertHtml;
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = alertsContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    }
    
    function updateAttemptsWarning(remaining) {
        if (remaining < 5 && remaining > 0) {
            attemptsWarningContainer.innerHTML = `
                <div class="alert alert-warning mb-3" role="alert" id="attempts-warning" style="animation: slideInDown 0.3s ease-out;">
                    <i class="mdi mdi-alert-outline"></i>
                    <strong>Warning:</strong> <span id="remaining-attempts-text">${remaining}</span> login attempt(s) remaining before temporary lockout.
                </div>
            `;
        } else if (remaining <= 0) {
            attemptsWarningContainer.innerHTML = `
                <div class="alert alert-danger mb-3" role="alert" style="animation: slideInDown 0.3s ease-out;">
                    <i class="mdi mdi-lock-outline"></i>
                    <strong>Locked Out:</strong> Too many failed attempts. Please wait before trying again.
                </div>
            `;
        } else {
            attemptsWarningContainer.innerHTML = '';
        }
    }
    
    function startLockoutCountdown(minutes) {
        let seconds = minutes * 60;
        const countdownInterval = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                loginBtn.disabled = false;
                attemptsWarningContainer.innerHTML = '';
                alertsContainer.innerHTML = `
                    <div class="alert alert-info mb-3" role="alert">
                        <i class="mdi mdi-information-outline"></i>
                        You can try logging in again now.
                    </div>
                `;
            } else {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                const existingAlert = attemptsWarningContainer.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.innerHTML = `
                        <i class="mdi mdi-lock-clock"></i>
                        <strong>Locked Out:</strong> Please wait ${mins}:${secs.toString().padStart(2, '0')} before trying again.
                    `;
                }
            }
        }, 1000);
    }
});
</script>

<style>
@keyframes slideInDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.shake-animation {
    animation: shake 0.5s ease-in-out;
}

#login-alerts-container .alert {
    border-radius: 8px;
}

.mdi-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>" defer></script>