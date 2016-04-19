<?php declare(strict_types=1);
/**
 * Feed controller
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
use Feedr\Form\CreateFeed;
use Feedr\Storage\GitHub\Service as GitHub;
use Feedr\Storage\Sql\Feed as FeedStorage;
use Feedr\Authentication\GitHub as Authenticator;

/**
 * Feed controller
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Feed
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
     * Renders the create feed form
     *
     * @param \CodeCollab\Template\Html $template      A HTML template renderer
     * @param \Feedr\Form\CreateFeed    $form          The create feed form
     * @param bool                      $passwordError Whether there was a password error
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function create(Html $template, CreateFeed $form, bool $passwordError = false): Response
    {
        $this->response->setContent($template->renderPage('/feed/create.phtml', [
            'form'          => $form,
            'passwordError' => $passwordError,
        ]));

        return $this->response;
    }

    /**
     * Handles the create feed form
     *
     * @param \CodeCollab\Template\Html        $template      A HTML template renderer
     * @param \Feedr\Form\CreateFeed           $form          The create feed form
     * @param \CodeCollab\Http\Request\Request $request       The request object
     * @param \Feedr\Storage\Sql\Feed          $storage       The feed storage
     * @param \Feedr\Authentication\GitHub     $authenticator The authentication object
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function doCreate(
        Html $template,
        CreateFeed $form,
        Request $request,
        FeedStorage $storage,
        Authenticator $authenticator
    ): Response
    {
        $form->bindRequest($request);
        $form->validate();

        $passwordError = false;

        if ($form['visibility']->getValue() === 'private' && $form['password']->getValue() !== $form['password2']->getValue()) {
            $passwordError = true;
        }

        if ($form['visibility']->getValue() === 'private' && !$form['password']->getValue()) {
            $passwordError = true;
        }

        if (!$form->isValid() || $passwordError) {
            return $this->create($template, $form, $passwordError);
        }

        $storage->create($form, $authenticator->id);

        $this->response->setStatusCode(StatusCode::FOUND);
        $this->response->addHeader('Location', $request->getBaseUrl());

        return $this->response;
    }

    /**
     * Renders the preview
     *
     * @param \CodeCollab\Template\Html        $template A HTML template renderer
     * @param \CodeCollab\Http\Request\Request $request  The request object
     * @param \Feedr\Storage\GitHub\Service    $github   The GitHub storage
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function preview(Html $template, Request $request, GitHub $github): Response
    {
        $this->response->setContent($template->render('/feed/preview.phtml', [
            'releases' => $request->get('repositories') ? $github->getReleases($request->get('repositories')) : [],
        ]));

        return $this->response;
    }
}
