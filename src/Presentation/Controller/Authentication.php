<?php declare(strict_types=1);
/**
 * Authentication controller
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://pieterhordijk.com>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Presentation\Controller;

use CodeCollab\Http\Response\Response;
use CodeCollab\Http\Response\StatusCode;
use CodeCollab\Http\Request\Request;
use CodeCollab\Template\Html;
use Feedr\Storage\GitHub\Service as GitHub;
use Feedr\Form\Logout as LogoutForm;
use Feedr\Authentication\GitHub as Authenticator;
use Feedr\Storage\Sql\User as UserStorage;
use Feedr\Storage\Sql\Log;

/**
 * Authentication controller
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Authentication
{
    /**
     * @var \CodeCollab\Http\Response\Response Response object
     */
    private $response;

    /**
     * Creates instance
     *
     * @param \CodeCollab\Http\Response\Response $response Response object
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Renders the login page
     *
     * @param \CodeCollab\Template\Html     $template A HTML template renderer
     * @param \Feedr\Storage\GitHub\Service $github   The GitHub storage
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function login(Html $template, GitHub $github): Response
    {
        $this->response->setContent($template->renderPage('/authentication/login.phtml', [
            'url' => $github->getAuthorizationUri(),
        ]));

        return $this->response;
    }

    /**
     * Handles the login form
     *
     * @param \CodeCollab\Http\Request\Request $request       The request object
     * @param \Feedr\Authentication\GitHub     $authenticator The authentication object
     * @param \Feedr\Storage\GitHub\Service    $github        The GitHub storage
     * @param \Feedr\Storage\Sql\User          $userStorage   The user storage
     * @param \Feedr\Storage\Sql\Log           $log           The log storage
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function doLogin(
        Request $request,
        Authenticator $authenticator,
        GitHub $github,
        UserStorage $userStorage,
        Log $log
    ): Response
    {
        $github->requestAccessToken($request->get('code'));

        $user = $github->request('user');

        $userStorage->persistUser($user);

        $authenticator->logInWithOauth($userStorage->getUser($user['id']));

        $log->addLogInEntry($user['id'], $request->server('REMOTE_ADDR'));

        $this->response->setStatusCode(StatusCode::FOUND);
        $this->response->addHeader('Location', $request->getBaseUrl());

        return $this->response;
    }

    /**
     * Handles the logout form
     *
     * @param \Feedr\Form\Logout               $form          The logout form
     * @param \CodeCollab\Http\Request\Request $request       The request object
     * @param \Feedr\Authentication\GitHub     $authenticator The authentication object
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function doLogout(LogoutForm $form, Request $request, Authenticator $authenticator): Response
    {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $authenticator->logOut();
        }

        $this->response->setStatusCode(StatusCode::FOUND);
        $this->response->addHeader('Location', $request->getBaseUrl());

        return $this->response;
    }
}
