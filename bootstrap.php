<?php
/**
 * Bootstrap the project
 *
 * PHP version 5.5
 *
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
use Feedr\Storage\Session;
use Feedr\Security\Generator\Factory;
use Feedr\Security\CsrfToken;
use Feedr\Storage\Database\Auth;
use Feedr\Auth\User;
use Feedr\Storage\ImmutableArray;
use Feedr\Network\Http\Request;
use Feedr\Router\Factory as RouterFactory;
use Feedr\Presentation\Html;
use Feedr\Presentation\Xml;
use Feedr\Router\NotFoundException;

use OAuth\ServiceFactory;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session as OauthSession;
use OAuth\Common\Consumer\Credentials;

/**
 * Autoload composer dependencies
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * Bootstrap the library
 */
require_once __DIR__ . '/src/Feedr/bootstrap.php';

/**
 * Setup the environment
 */
require_once __DIR__ . '/init.deployment.php';

/**
 * Start the session
 */
session_start();

/**
 * Setup CSRF token
 */
$sessionStorage = new Session();
$csrfToken      = new CsrfToken($sessionStorage, new Factory());

/**
 * Setup the GitHub service
 */
$serviceFactory = new ServiceFactory();
$storage = new OauthSession();

/**
 * Return when on CLI
 */
if (php_sapi_name() === 'cli') {
    return;
}

/**
 * Request access token
 */
$uriFactory     = new UriFactory();
$currentUri     = $uriFactory->createFromSuperGlobalArray($_SERVER);
$currentUri->setQuery('');
$credentials = new Credentials(
    $githubCredentials['key'],
    $githubCredentials['secret'],
    $currentUri->getAbsoluteUri()
);
$github = $serviceFactory->createService('GitHub', $credentials, $storage, array());

/**
 * Setup the authentication object
 */
$user = new User($sessionStorage, new Auth($dbConnection));

/**
 * Setup the HTML template renderer
 */
$htmlTemplate = new Html(__DIR__ . '/templates', 'page.phtml');

/**
 * Setup the XML template renderer
 */
$xmlTemplate = new Xml(__DIR__ . '/templates');

/**
 * Setup the request object
 */
$request = new Request(
    new ImmutableArray([]),
    new ImmutableArray($_GET),
    new ImmutableArray($_POST),
    new ImmutableArray($_SERVER),
    new ImmutableArray($_FILES),
    new ImmutableArray($_COOKIE)
);

/**
 * Setup the router
 */
$router = (new RouterFactory())->build();

/**
 * Load the routes
 */
require_once __DIR__ . '/routes.php';

/**
 * Dispatch the request
 */
try {
    $route = $router->getRoute($request);

    $callback = $route->getCallback();

    echo $callback($route);
} catch(NotFoundException $e) {
    echo '404';
}
