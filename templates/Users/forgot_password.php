<link rel="stylesheet" href="<?= $this->Url->build('/assets/css/mascot.css') ?>">

<!-- Mascot centered above the form -->
<div id="genta-mascot" class="genta-mascot" aria-hidden="true">
    <div id="genta-mascot-container" class="frame-circle" aria-hidden="true"></div>
</div>

<!-- FORGOT PASSWORD TITLE -->
<h4 class="auth-title"><i class="mdi mdi-key-variant"></i> Forgot Password?</h4>
<div class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</div>

<!-- FORGOT PASSWORD FORM -->
<?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'forgotPassword', 'prefix' => false]]) ?>
    <div class="form-group">
        <?= $this->Form->email('email', [
            'class' => 'form-control form-control-lg', 
            'id' => 'email', 
            'placeholder' => 'Email Address', 
            'required' => 'required', 
            'aria-label' => 'Email',
            'autocomplete' => 'email'
        ]) ?>
    </div>
    
    <div class="mt-3">
        <?= $this->Form->button('Send Reset Link', [
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
