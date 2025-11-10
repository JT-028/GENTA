<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        // Log configured app base values so we can verify the application
        // is loading environment overrides (e.g. app_local.php) when running
        // under a subdirectory like /GENTA.
        try {
            // Keep a lighter-weight informational entry rather than verbose debug noise.
            // Emit this at debug level to avoid noisy info-level logs in normal runs.
            \Cake\Log\Log::write('debug', 'Configured App.base=' . (string)Configure::read('App.base') . ' fullBaseUrl=' . (string)Configure::read('App.fullBaseUrl'));
        } catch (\Throwable $e) {
            // Avoid throwing during bootstrap if logging isn't available yet.
        }
        // DebugKit plugin loading removed to fully disable the toolbar

        

        // Load more plugins here
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Log incoming request details early to diagnose base/path issues
            ->add(function (ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler) {
                try {
                    $reqUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                    $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
                    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
                    $uriPath = (string)$request->getUri()->getPath();
                    // Attempt to read Cake-specific base attribute if present
                    $baseAttr = $request->getAttribute('base') ?? (string)\Cake\Core\Configure::read('App.base');

                    // If the incoming REQUEST_URI contains the application base twice (e.g. /GENTA/GENTA),
                    // normalize it by removing the duplicated segment. This addresses cases where a
                    // relative Location header or a client-side relative link produced /GENTA/GENTA.
                    if (!empty($baseAttr) && strpos($reqUri, $baseAttr . $baseAttr) === 0) {
                        $fixedReqUri = preg_replace('#^' . preg_quote($baseAttr . $baseAttr, '#') . '#', $baseAttr, $reqUri, 1);
                        // Log a concise informational message when we detect and correct a duplicated base path.
                        // Lower to debug: helpful when diagnosing base duplication but too noisy for info.
                        \Cake\Log\Log::write('debug', 'Request diagnostics: normalized duplicated base from ' . $reqUri . ' to ' . $fixedReqUri);
                        // Update PHP server superglobal so downstream code reading it sees the normalized URI
                        $_SERVER['REQUEST_URI'] = $fixedReqUri;
                        // Normalize the PSR-7 URI path too by removing the leading baseAttr from the path
                        $newPath = $uriPath;
                        if (strpos($newPath, $baseAttr) === 0) {
                            // remove the leading baseAttr from the path so Router sees the intended sub-path
                            $newPath = substr($newPath, strlen($baseAttr));
                            if ($newPath === '') {
                                $newPath = '/';
                            }
                        }
                        $request = $request->withUri($request->getUri()->withPath($newPath));
                        $uriPath = $newPath;
                        // update local copy of reqUri for logging below
                        $reqUri = $_SERVER['REQUEST_URI'];
                    }

                    // If the request path equals the configured app base without a trailing slash,
                    // redirect to the canonical trailing-slash form (e.g. /GENTA -> /GENTA/).
                    // This prevents the router from interpreting the base segment as a controller name.
                    $baseNoSlash = rtrim($baseAttr, '/');
                    $currentPath = $request->getUri()->getPath();
                    if (!empty($baseNoSlash) && ($currentPath === $baseNoSlash || $currentPath === $baseAttr)) {
                        $target = $baseNoSlash . '/';
                        // Redirecting to the canonical base path is an important event - record at info level.
                        // Keep this at debug level during normal operation to avoid logfile noise.
                        \Cake\Log\Log::write('debug', 'Request diagnostics: redirecting base path ' . $currentPath . ' to ' . $target);
                        $response = new \Cake\Http\Response();
                        // Use 307 to preserve the original HTTP method (POST) when redirecting
                        // the canonical base path. This prevents POST -> GET conversion that
                        // breaks form submissions targeting the application root.
                        return $response->withStatus(307)->withHeader('Location', $target);
                    }

                    // Reduce volume by emitting a single informational snapshot rather than debug spam.
                    \Cake\Log\Log::write('debug', 'Request diagnostics: REQUEST_URI=' . $reqUri . ' UriPath=' . $uriPath . ' BaseAttr=' . $baseAttr);
                } catch (\Throwable $e) {
                    \Cake\Log\Log::write('error', 'Request diagnostics error: ' . $e->getMessage());
                }
                return $handler->handle($request);
            })

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // AUTHENTICATION MIDDLEWARE
            ->add(new AuthenticationMiddleware($this))

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]));

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // On unauthenticated requests redirect to the Users::login action.
        // Previous value used a relative controller path ('../Users') which
        // resulted in malformed route arrays and "Missing route/controller"
        // errors in the logs. Use the canonical controller name here.
        // Ensure the unauthenticated redirect points to the non-prefixed Users::login
        // (avoids inheriting a request prefix such as 'teacher' which produced
        // unwanted routes like /teacher/users/login and Missing Controller errors).
        $authenticationService = new AuthenticationService([
            'unauthenticatedRedirect' => Router::url([
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'login',
            ]),
            'queryParam' => 'redirect',
        ]);

        // Load identifiers, ensure we check email and password fields
        $authenticationService->loadIdentifier('Authentication.Password', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ]
        ]);

        // Load the authenticators, you want session first
        $authenticationService->loadAuthenticator('Authentication.Session');
        // Configure form data check to pick email and password
        // Compute the canonical login URL via the Router so it honors any custom
        // route mapping (for example the app root '/' can be routed to Users::login).
        // This ensures the authenticator's `loginUrl` matches the form action the
        // templates generate.
        $formLoginUrl = Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login',
        ]);
        try {
            // Auth config details are helpful when debugging but should be debug-level.
            \Cake\Log\Log::write('debug', 'Auth config: form loginUrl=' . $formLoginUrl . ' requestPath=' . (string)$request->getUri()->getPath());
        } catch (\Throwable $_) { /* ignore logging errors during bootstrap */ }

        $authenticationService->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
            'loginUrl' => $formLoginUrl,
        ]);

        return $authenticationService;
    }
}
