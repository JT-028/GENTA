<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- META TAGS -->
        <?= $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>GENTA</title>

        <!-- Inline: hide server-rendered flash messages immediately; JS will show SweetAlert toasts instead -->
        <style>
            .alert.alert-danger, .alert.alert-success, .flash-error, .flash-success, .message.error, .message.success { display: none !important; }
        </style>

        <!-- CSS -->
        <?=
            $this->Html->css([
                // CSS VENDOR
                '/assets/vendors/mdi/css/materialdesignicons.min',
                '/assets/vendors/css/vendor.bundle.base',
                // STYLES
                '/assets/css/style',
                '/assets/css/custom',
            ])
        ?>

    <!-- ICONS -->
    <?= $this->Html->meta('mascot_head.svg', '/assets/images/mascot_head.svg', ['type' => 'icon']) ?>
    <!-- SWEETALERT2 CDN (to enable consistent toast styling on guest pages) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <div class="container-scroller">
            <div class="container-fluid page-body-wrapper full-page-wrapper">
                <div class="content-wrapper d-flex align-items-center auth">
                    <div class="row flex-grow">
                        <div class="col-xxl-4 col-lg-6 col-md-12 mx-auto">
                            <div class="auth-form-light text-left p-5">
                                <!-- LOGO (hide on Users::login to avoid duplicate mascot) -->
                                <?php
                                $ctl = $this->request->getParam('controller');
                                $act = $this->request->getParam('action');
                                // Hide the brand logo on Users::login and Users::register to avoid
                                // rendering a duplicate mascot/icon when the page templates include their own mascot.
                                if (!($ctl === 'Users' && in_array($act, ['login', 'register']))): ?>
                                <div class="brand-logo">
                                    <?= $this->Html->image('/assets/images/mascot_head.svg', ['alt' => 'GENTA Icon']) ?>
                                </div>
                                <?php endif; ?>
                                <?= $this->Flash->render() ?>
                                <?= $this->fetch('content') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JS -->
        <?=
            $this->Html->script([
                // JS VENDOR
                '/assets/vendors/js/vendor.bundle.base',
                // JS
                '/assets/js/off-canvas',
                '/assets/js/hoverable-collapse',
                '/assets/js/misc'
            ])
        ?>
    </body>
</html>