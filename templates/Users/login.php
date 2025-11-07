<!-- WELCOME TEXT -->
<h4>Hello! Welcome to GENTA.</h4>
<h6 class="font-weight-light">Log in to continue.</h6>

<!-- LOG IN FORM -->
<?= $this->Form->create(NULL, ['url' => ['controller' => 'Users', 'action' => 'login', '?' => $this->request->getQuery()]]) ?>
    <div class="form-group">
        <?= $this->Form->email('email', ['class' => 'form-control form-control-lg', 'id' => 'email', 'placeholder' => 'Email Address', 'required' => 'required']) ?>
    </div>
    <div class="form-group">
        <?= $this->Form->password('password', ['class' => 'form-control form-control-lg', 'id' => 'password', 'placeholder' => 'Password', 'required' => 'required']) ?>
    </div>
    <div class="mt-3">
        <?= $this->Form->button('LOG IN', ['class' => 'btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn', 'type' => 'submit']) ?>
    </div>
    <div class="my-2 d-flex justify-content-end align-items-center">
        <!-- <div class="form-check">
            <label class="form-check-label text-muted">
                <?= $this->Form->checkbox('keep_logged_in', ['class' => 'form-check-input']) ?> Keep me logged in
            </label>
        </div> -->

        <!-- <?= $this->Html->link('Forgot password?', ['controller' => 'Users', 'action' => 'forgotPassword'], ['escape' => false, 'class' => 'auth-link text-black']) ?> -->
    </div>
    <div class="text-center mt-4 font-weight-light"> 
        <?= $this->Html->link('Create new account', ['controller' => 'Users', 'action' => 'register'], ['escape' => false, 'class' => 'text-primary']) ?>
    </div>
<?= $this->Form->end() ?>

<!-- Session Timeout Detection -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if user was redirected due to session timeout/inactivity
    const urlParams = new URLSearchParams(window.location.search);
    const redirectParam = urlParams.get('redirect');
    
    // If there's a redirect parameter, it means the user was trying to access a protected page
    // but their session expired
    if (redirectParam && typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Session Expired',
            html: '<div style="text-align: center;">Your account has been logged out due to a long period of inactivity. Please log in again to continue.</div>',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#b66dff',
            allowOutsideClick: true,
            allowEscapeKey: true
        });
        
        // Clean up URL (remove redirect parameter from address bar)
        if (window.history && window.history.replaceState) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
});
</script>