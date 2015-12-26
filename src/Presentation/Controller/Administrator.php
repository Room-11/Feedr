<?php declare(strict_types=1);
/**
 * Administrator controller
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
use Feedr\Storage\GitHub;

/**
 * Repository controller
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Administrator
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
     * Renders the search results modal
     *
     * @param \CodeCollab\Template\Html        $template A HTML template renderer
     * @param \CodeCollab\Http\Request\Request $request  The request object
     * @param \Feedr\Storage\GitHub            $github   The GitHub storage
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function search(Html $template, Request $request, GitHub $github): Response
    {
        $this->response->setContent($template->render('/administrator/search-result.phtml', [
            'administrators' => $github->searchUser($request->get('user')),
        ]));

        return $this->response;
    }

    /**
     * Renders the new administrator table row
     *
     * @param \CodeCollab\Template\Html        $template A HTML template renderer
     * @param \CodeCollab\Http\Request\Request $request  The request object
     *
     * @return \CodeCollab\Http\Response\Response The HTTP response
     */
    public function addRow(Html $template, Request $request): Response
    {
        $this->response->setContent($template->render('/administrator/row.phtml', [
            'id'       => $request->get('id'),
            'avatar'   => $request->get('avatar'),
            'username' => $request->get('username'),
        ]));

        return $this->response;
    }
}
