<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GENTA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .reset-password-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        .reset-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-password-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .reset-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .reset-password-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
            margin-top: 15px;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding-left: 5px;
        }
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 2px 0;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .back-to-login a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }
        .password-toggle:hover {
            color: #667eea;
        }
        .password-wrapper {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-header">
            <i class="fas fa-lock"></i>
            <h2>Reset Your Password</h2>
            <p>Enter your new password below.</p>
        </div>

        <?= $this->Flash->render() ?>

        <?= $this->Form->create(null, ['url' => ['action' => 'resetPassword', '?' => ['token' => $this->request->getQuery('token')]]]) ?>
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-key"></i> New Password
                </label>
                <div class="password-wrapper">
                    <?= $this->Form->control('password', [
                        'type' => 'password',
                        'class' => 'form-control',
                        'id' => 'password',
                        'placeholder' => 'Enter new password',
                        'label' => false,
                        'required' => true,
                        'autocomplete' => 'new-password'
                    ]) ?>
                    <i class="fas fa-eye password-toggle" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
                </div>
                <div class="password-requirements">
                    Password must contain:
                    <ul>
                        <li>At least 8 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one lowercase letter</li>
                        <li>At least one number</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirm">
                    <i class="fas fa-check-circle"></i> Confirm Password
                </label>
                <div class="password-wrapper">
                    <?= $this->Form->control('password_confirm', [
                        'type' => 'password',
                        'class' => 'form-control',
                        'id' => 'password_confirm',
                        'placeholder' => 'Confirm new password',
                        'label' => false,
                        'required' => true,
                        'autocomplete' => 'new-password'
                    ]) ?>
                    <i class="fas fa-eye password-toggle" id="togglePasswordConfirm" onclick="togglePasswordVisibility('password_confirm', 'togglePasswordConfirm')"></i>
                </div>
            </div>

            <?= $this->Form->button('Reset Password', [
                'class' => 'btn btn-reset',
                'type' => 'submit'
            ]) ?>
        <?= $this->Form->end() ?>

        <div class="back-to-login">
            <i class="fas fa-arrow-left"></i>
            <?= $this->Html->link('Back to Login', ['action' => 'login']) ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength indicator (optional enhancement)
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            // You can add visual feedback here if desired
        });

        // Confirm password match validation
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
</body>
</html>
