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
use CodeCollab\Http\Request\Request;
use CodeCollab\Template\Html;
use Feedr\Form\CreateFeed;
use Feedr\Storage\GitHub\Service as GitHub;

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
     * Renders the dashboard
     *
     * @param \CodeCollab\Template\Html $template A HTML template renderer
     * @param \Feedr\Form\CreateFeed    $form     The create feed form
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function create(Html $template, CreateFeed $form): Response
    {
        $this->response->setContent($template->renderPage('/feed/create.phtml', [
            'form' => $form,
        ]));

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
