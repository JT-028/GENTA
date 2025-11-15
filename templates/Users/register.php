<link rel="stylesheet" href="/GENTA/assets/css/mascot.css">

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
        <?= $this->Form->text('first_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['first_name']['class'] ?? ''), 'id' => 'first_name', 'placeholder' => 'First Name', 'required' => 'required']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['first_name']['message'] ?? '' ?></div>
    </div>
    <div class="form-group">
        <?= $this->Form->text('last_name', ['class' => 'form-control form-control-lg ' . ($fieldErrors['last_name']['class'] ?? ''), 'id' => 'last_name', 'placeholder' => 'Last Name', 'required' => 'required']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['last_name']['message'] ?? '' ?></div>
    </div>
    <div class="form-group">
        <?= $this->Form->email('email', ['class' => 'form-control form-control-lg ' . ($fieldErrors['email']['class'] ?? ''), 'id' => 'email', 'placeholder' => 'Email Address', 'required' => 'required']) ?>
        <div class="invalid-feedback"><?= $fieldErrors['email']['message'] ?? '' ?></div>
    </div>
    <div class="form-group position-relative">
        <?= $this->Form->password('password', ['class' => 'form-control form-control-lg ' . ($fieldErrors['password']['class'] ?? ''), 'id' => 'password', 'placeholder' => 'Password', 'required' => 'required']) ?>
    <button type="button" id="toggle-password-visibility" class="password-toggle-icon" aria-label="Toggle password visibility"><i class="mdi mdi-eye-off-outline" aria-hidden="true"></i></button>
        <div class="invalid-feedback"><?= $fieldErrors['password']['message'] ?? '' ?></div>
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

<script src="/GENTA/assets/js/mascot.js" defer></script>