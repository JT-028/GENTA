<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- META TAGS -->
        <?= $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php if (isset($this) && method_exists($this->request, 'getAttribute')): ?>
            <meta name="csrfToken" content="<?= h($this->request->getAttribute('csrfToken')) ?>">
        <?php endif; ?>
        <title>GENTA</title>

        <!-- CSS -->
        <link href="https://fonts.cdnfonts.com/css/the-bold-font" rel="stylesheet">
        <?= 
            $this->Html->css([
                // CSS VENDOR
                '/assets/vendors/mdi/css/materialdesignicons.min',
                '/assets/vendors/css/vendor.bundle.base',
                '/assets/vendors/css/dataTables.bootstrap5.min',
                '/assets/vendors/css/responsive.bootstrap5.min',
                // STYLES
                '/assets/css/style',
                '/assets/css/custom',
            ])
        ?>

    <!-- ICONS -->
    <?= $this->Html->meta('icon', '/assets/images/mascot_head.svg') ?>

    <!-- LOTTIE ANIMATION CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js"></script>

    <!-- SWEETALERT2 CDN (for logout confirmation) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
        $walkthrough_shown = false;
        if ($this->Identity->isLoggedIn()) {
            $walkthrough_shown = (bool)$this->Identity->get('walkthrough_shown');
        }
    ?>
    <script>
        window.walkthrough_shown = <?= $walkthrough_shown ? 'true' : 'false' ?>;
    </script>
    <script>
        // Base path for the app (includes subdirectory like /GENTA/ when deployed in a folder)
        window.APP_BASE = <?= json_encode($this->Url->build('/')) ?>;
    </script>
    
    <!-- PAGE LOADER STYLES (critical) - moved to head so loader appears immediately -->
    <style>
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Use brand palette variables for loader gradient */
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--vivid-sky) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        #page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        #lottie-animation {
            width: 300px;
            height: 300px;
        }

        .loader-text {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 20px;
            letter-spacing: 2px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>

    </head>
    <body>
        <!-- PAGE LOADER -->
        <div id="page-loader">
            <div id="lottie-animation"></div>
            <p class="loader-text">Loading...</p>
        </div>

        <div class="container-scroller">
            <!-- UPPER NAVIGATION TAB -->
            <!-- Removed pt-5 / mt-3 to prevent extra top spacing; set explicit pt-0 mt-0 -->
            <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 pt-0 mt-0 fixed-top d-flex flex-row">
                <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                    <!-- DEFAULT LOGO -->
                    <?= $this->Html->link(
                        '<i class="mdi mdi-robot" style="font-size:2rem; color: var(--brand-primary)"></i> <span style="font-family: Aitech Rounded, sans-serif; font-size:1.7rem; color: var(--brand-primary); vertical-align:middle;">GENTA</span>',
                        ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Teacher'],
                        ['escape' => false, 'class' => 'navbar-brand brand-logo']
                    ) ?>
                    <!-- MINI LOGO -->
                    <?= $this->Html->link(
                        '<i class="mdi mdi-robot" style="font-size:1.5rem; color: var(--brand-primary)"></i>',
                        ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Teacher'],
                        ['escape' => false, 'class' => 'navbar-brand brand-logo-mini']
                    ) ?>
                </div>
                <div class="navbar-menu-wrapper d-flex align-items-stretch">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                        <span class="mdi mdi-menu"></span>
                    </button>
                    <ul class="navbar-nav navbar-nav-right">
                        <li class="nav-item d-none d-lg-block full-screen-link">
                            <a class="nav-link">
                                <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                            </a>
                        </li>
                        <!-- HELP BUTTON FOR WALKTHROUGH -->
                        <li class="nav-item d-none d-lg-block">
                            <a class="nav-link" id="help-walkthrough-btn" title="Show Help / Walkthrough" style="position:relative;">
                                <i class="mdi mdi-help-circle-outline" style="font-size:1.7rem; color:var(--brand-primary);"></i>
                            </a>
                        </li>
                    </ul>
                    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                        <span class="mdi mdi-menu"></span>
                    </button>
                </div>
            </nav>
            <div class="container-fluid page-body-wrapper">
                <!-- SIDEBAR NAVIGATION TAB -->
                <nav class="sidebar sidebar-offcanvas" id="sidebar">
                    <ul class="nav">
                        <li class="nav-item nav-profile">
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile', 'prefix' => 'Teacher']) ?>" class="nav-link">
                                <div class="nav-profile-image">
                                    <?php
                                        $profileImage = $this->Identity->get('profile_image');
                                        // Build base-aware image URLs so they work when the app is served under a subpath.
                                        // Use the stored filename to construct the canonical uploads path. This avoids
                                        // rendering whatever stray or double-prefixed value may be present in the session.
                                        if (!empty($profileImage)) {
                                            $filename = basename((string)$profileImage);
                                            // Build a canonical path using the request base (avoid double-prefixing)
                                            $baseAttr = $this->request->getAttribute('base') ?? (string)\Cake\Core\Configure::read('App.base');
                                            $baseAttr = rtrim((string)$baseAttr, '/');
                                            $src = $baseAttr . '/uploads/profile_images/' . $filename;
                                            // Ensure leading slash
                                            if (substr($src, 0, 1) !== '/') $src = '/' . $src;
                                            echo '<img src="' . h($src) . '" alt="profile">';
                                        } else {
                                            echo $this->Html->image($this->Url->build('/assets/images/faces-clipart/pic-1.png'), ['alt' => 'profile']);
                                        }
                                    ?>
                                </div>
                                <div class="nav-profile-text d-flex flex-column">
                                    <span class="font-weight-bold mb-2"><?= $this->Identity->get('full_name') ?></span>
                                    <span class="text-secondary text-small">Professor</span>
                                </div>
                                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                            </a>
                        </li>
                        <?php 
                        $currentAction = $this->request->getParam('action');
                        $dashboardActive = in_array($currentAction, ['index', 'studentQuiz']) ? 'active' : '';
                        $studentsActive = in_array($currentAction, ['students', 'student', 'createEditStudent']) ? 'active' : '';
                        $questionsActive = in_array($currentAction, ['questions', 'createEditQuestion']) ? 'active' : '';
                        $profileActive = ($currentAction === 'profile') ? 'active' : '';
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $dashboardActive ?>" href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Teacher']) ?>">
                                <span class="menu-title">Dashboard</span>
                                <i class="mdi mdi-home menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $studentsActive ?>" href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'students', 'prefix' => 'Teacher']) ?>">
                                <span class="menu-title">Students</span>
                                <i class="mdi mdi-account-multiple menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $questionsActive ?>" href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'questions', 'prefix' => 'Teacher']) ?>">
                                <span class="menu-title">Quiz</span>
                                <i class="mdi mdi-file-document menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $profileActive ?>" href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile', 'prefix' => 'Teacher']) ?>">
                                <span class="menu-title">Profile</span>
                                <i class="mdi mdi-account menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- Force a non-prefixed logout URL so we hit App\Controller\UsersController::logout() and not a missing Teacher\UsersController -->
                            <a class="nav-link" href="<?= $this->Url->build('/users/logout') ?>" data-no-ajax="true">
                                <span class="menu-title">Log out</span>
                                <i class="mdi mdi-logout-variant menu-icon"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- PAGE CONTENT -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <!-- BREADCRUMBS -->
                        <?php if (!in_array($this->request->getParam('action'), ['studentQuiz', 'student'])): ?>
                        <div class="page-header">
                            <h3 class="page-title">
                                <?php
                                $action = $this->request->getParam('action');
                                $icon = 'mdi-home';
                                $title = 'Teacher Dashboard';
                                
                                switch($action) {
                                    case 'students':
                                        $icon = 'mdi-account-group';
                                        $title = 'List of Students';
                                        break;
                                    case 'createEditStudent':
                                        $icon = 'mdi-account-edit';
                                        $title = 'Manage Student';
                                        break;
                                    case 'questions':
                                        $icon = 'mdi-lightbulb-on';
                                        $title = 'Question Bank';
                                        break;
                                    case 'createEditQuestion':
                                        $icon = 'mdi-clipboard-edit';
                                        $title = 'Manage Question';
                                        break;
                                    case 'profile':
                                        $icon = 'mdi-account-circle';
                                        $title = 'My Profile';
                                        break;
                                    case 'index':
                                    default:
                                        $icon = 'mdi-view-dashboard';
                                        $title = 'Teacher Dashboard';
                                        break;
                                }
                                ?>
                                <span class="page-title-icon bg-gradient-primary text-white me-2">
                                    <i class="mdi <?= $icon ?>"></i>
                                </span> <?= $title ?>
                            </h3>
                        </div>
                        <?php endif; ?>

                        <?= $this->Flash->render() ?>
                        <?= $this->fetch('content') ?>
                    </div>

                    <!-- FOOTER -->
                    <footer class="footer">
                        <div class="container-fluid d-flex justify-content-between">
                            <span class="text-muted d-block text-center text-sm-start d-sm-inline-block">Copyright © GENTA <?= date('Y') ?></span>
                        </div>
                    </footer>
                </div>
            </div>
        </div>

        <!-- JS -->
        <?php
            // Only load the latest jQuery (webroot/assets/js/jquery.js) and ensure correct order.
            // Pass only string paths to HtmlHelper::script() to avoid UrlHelper receiving non-string values.
            echo $this->Html->script([
                '/assets/vendors/js/vendor.bundle.base.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min',
                '/assets/vendors/chart.js/Chart.min',
                '/assets/vendors/js/jquery.dataTables.min',
                '/assets/vendors/js/dataTables.bootstrap5.min',
                '/assets/vendors/js/dataTables.responsive.min',
                '/assets/vendors/js/responsive.bootstrap5.min',
                'https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js',
                '/assets/js/off-canvas',
                '/assets/js/hoverable-collapse',
                '/assets/vendors/js/jquery.cookie',
                '/assets/js/misc',
                '/assets/js/script'
            ]);

            // Inline: force window.jQuery to reference the latest jQuery instance
            echo $this->Html->scriptBlock('window.jQuery = window.$ = jQuery;');

            // Inline debug information and dynamic loader for jquery.cookie (kept as scriptBlock to avoid passing booleans to Html->script)
            echo $this->Html->scriptBlock('console.info("jQuery version:", (window.jQuery && window.jQuery.fn && window.jQuery.fn.jquery) || "<none>"); console.info("$.cookie:", (window.jQuery && (typeof window.jQuery.cookie !== "undefined" || (window.jQuery.fn && typeof window.jQuery.fn.cookie !== "undefined"))) ? "present" : "undefined");');

            echo $this->Html->scriptBlock('(function(){try{if(typeof window.jQuery==="undefined"){console.warn("jQuery is not available yet");return;}var hasCookie = (typeof window.jQuery.cookie!="undefined") || (window.jQuery.fn && typeof window.jQuery.fn.cookie!="undefined"); if(!hasCookie){ var base = (window.APP_BASE!==undefined)?String(window.APP_BASE):"/"; if(base.slice(-1)!="/") base += "/"; var s=document.createElement("script"); s.src = base + "assets/vendors/js/jquery.cookie.js"; s.onload = function(){ console.info("jquery.cookie reloaded. $.cookie:", typeof window.jQuery.cookie, "$.fn.cookie:", typeof (window.jQuery.fn && window.jQuery.fn.cookie)); }; s.onerror = function(){ console.error("Failed to load jquery.cookie.js from", s.src); }; document.head.appendChild(s);} }catch(e){console.error(e);} })();');

        ?>
        <?= $this->fetch('script') ?>

        <script>
            // Initialize Lottie animation
            const animation = lottie.loadAnimation({
                container: document.getElementById('lottie-animation'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '<?= $this->Url->build('/animation/robot.json') ?>'
            });

            // Hide loader when page is fully loaded (only on initial page load)
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const loader = document.getElementById('page-loader');
                    if (loader) {
                        loader.classList.add('hidden');
                        // Remove from DOM after transition
                        setTimeout(function() {
                            loader.style.display = 'none';
                        }, 500);
                    }
                }, 1000); // Show loader for at least 1 second
            });

            // Prevent loader from showing again on AJAX content loads
            window.loaderShown = true;
        </script>
        <script>
            // Ensure --brand-primary-rgba-18 matches the computed --brand-primary color.
            // This converts hex or rgb(...) CSS var to an "r,g,b" tuple usable in rgba(var(--brand-primary-rgba-18), alpha).
            (function setBrandPrimaryRgba() {
                try {
                    var root = document.documentElement;
                    var cs = getComputedStyle(root);
                    var val = cs.getPropertyValue('--brand-primary') || '';
                    val = String(val).trim().replace(/"|'/g, '');
                    if (!val) return;

                    function hexToRgb(h) {
                        if (!h) return null;
                        h = String(h).trim();
                        if (h.indexOf('#') === 0) h = h.slice(1);
                        if (h.length === 3) h = h.split('').map(function(c){ return c + c; }).join('');
                        if (h.length !== 6) return null;
                        var n = parseInt(h, 16);
                        return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
                    }

                    var rgb = null;
                    if (val.indexOf('rgb') === 0) {
                        var m = val.match(/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i);
                        if (m) rgb = [parseInt(m[1],10), parseInt(m[2],10), parseInt(m[3],10)];
                    } else {
                        rgb = hexToRgb(val);
                    }

                    if (rgb && rgb.length === 3) {
                        root.style.setProperty('--brand-primary-rgba-18', rgb.join(','));
                        // Compute a darker variant (approx 82% brightness) for hover/focus fallbacks
                        try {
                            var dark = rgb.map(function(c){ return Math.max(0, Math.min(255, Math.round(c * 0.82))); });
                            // set as an rgb(...) color string so CSS can use it directly
                            root.style.setProperty('--brand-primary-dark', 'rgb(' + dark.join(',') + ')');
                        } catch (ee) {
                            /* ignore */
                        }
                    }
                } catch (e) {
                    // Non-fatal — preserve page functionality even if color parsing fails
                    console.warn('setBrandPrimaryRgba failed', e);
                }
            })();
        </script>
    </body>
</html>