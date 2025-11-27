<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the original single-column form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- WELCOME TEXT -->
<h4 class="auth-title">Welcome back to GENTA</h4>
<div class="auth-subtitle">Sign in to access your dashboard — manage quizzes, students, and results.</div>

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

<script src="<?= $this->Url->build('/assets/js/mascot.js') ?>" defer></script>