<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GENTA</title>
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
        .forgot-password-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        .forgot-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .forgot-password-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .forgot-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .forgot-password-header p {
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
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-header">
            <i class="fas fa-key"></i>
            <h2>Forgot Password?</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <?= $this->Flash->render() ?>

        <?= $this->Form->create(null, ['url' => ['action' => 'forgotPassword']]) ?>
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <?= $this->Form->control('email', [
                    'type' => 'email',
                    'class' => 'form-control',
                    'placeholder' => 'Enter your email address',
                    'label' => false,
                    'required' => true,
                    'autocomplete' => 'email'
                ]) ?>
            </div>

            <?= $this->Form->button('Send Reset Link', [
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
</body>
</html>
