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
use Feedr\Form\Login as LoginForm;
use Feedr\Form\Logout as LogoutForm;
use CodeCollab\Authentication\Authentication as User;

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
     * @param \CodeCollab\Template\Html $template A HTML template renderer
     * @param \Feedr\Form\Login          $form     The login for
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function login(Html $template, LoginForm $form): Response
    {
        $this->response->setContent($template->renderPage('/authentication/login.phtml', [
            'form' => $form,
        ]));

        if ($form->isValidated()) {
            $this->response->setStatusCode(StatusCode::UNAUTHORIZED);
        }

        return $this->response;
    }

    /**
     * Handles the login form
     *
     * @param \CodeCollab\Template\Html                 $template A HTML template renderer
     * @param \Feedr\Form\Login                         $form     The login form
     * @param \CodeCollab\Http\Request\Request          $request  The request object
     * @param \CodeCollab\Authentication\Authentication $user     The authentication object
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function doLogin(Html $template, LoginForm $form, Request $request, User $user): Response
    {
        $form->bindRequest($request);

        // Hardcoded user info. Normally this would be retrieved from the database.
        // This contains a user with username + password of demo + demo.
        $userInfo = [
            'username' => 'demo',
            'name'     => 'Demo Demo',
            'hash'     => '$2y$14$hPOMx1/RiQHriUVLgst0mOiZj1CyE7ziXk9LNf3UgZxsNuST.xnpe',
        ];

        if (!$form->isValid() || !$user->logIn($form['password']->getValue(), $userInfo['hash'], $userInfo)) {
            return $this->login($template, $form, $request);
        }

        $this->response->setStatusCode(StatusCode::FOUND);
        $this->response->addHeader('Location', $request->getBaseUrl());

        return $this->response;
    }

    /**
     * Handles the logout form
     *
     * @param \Demo\Form\Logout                         $form    The logout form
     * @param \CodeCollab\Http\Request\Request          $request The request object
     * @param \CodeCollab\Authentication\Authentication $user    The authentication object
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function doLogout(LogoutForm $form, Request $request, User $user): Response
    {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $user->logOut();
        }

        $this->response->setStatusCode(StatusCode::FOUND);
        $this->response->addHeader('Location', $request->getBaseUrl());

        return $this->response;
    }
}
