<?php declare(strict_types=1);
/**
 * GitHub API client
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage\GitHub;

use OAuth\ServiceFactory;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session as OauthSession;
use OAuth\Common\Consumer\Credentials as OauthCredentials;
use OAuth\OAuth2\Service\GitHub as GitHubService;
use OAuth\Common\Exception\Exception;

/**
 * GitHub API client
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Client
{
    /**
     * @var \Feedr\Storage\GitHub\Credentials The GitHub API credentials
     */
    private $credentials;

    /**
     * @var \Feedr\Storage\GitHub\ErrorHandler The custom error handler
     */
    private $errorHandler;

    /**
     * @var null|\OAuth\OAuth2\Service\GitHub The GitHub service
     */
    private $service;

    /**
     * Creates instance
     *
     * @param \Feedr\Storage\GitHub\Credentials  $credentials  The GitHub API credentials
     * @param \Feedr\Storage\GitHub\ErrorHandler $errorHandler The error handler
     */
    public function __construct(Credentials $credentials, ErrorHandler $errorHandler)
    {
        $this->credentials  = $credentials;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Makes a request to the GitHub service
     *
     * @param string $path The path to make the request to
     *
     * @return array The data returned from the service
     */
    public function request(string $path): array
    {
        try {
            return json_decode($this->getService()->request($path), true);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle request errors (in a retarded way)
     *
     * @todo I should really handle errors in a less horrific way...
     *
     * @param \OAuth\Common\Exception\Exception $exception The PHPoAuthLib exception
     *
     * @return array Empty array
     */
    private function handleError(Exception $exception): array
    {
        try {
            $this->errorHandler->handle($exception);
        } catch (NotFoundException $e) {
            return [];
        }

        return [];
    }

    /**
     * Proxies the GitHub service method calls
     *
     * @param string $method The name of the method being called
     * @param array  $args   The arguments to pass to through
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->getService(), $method], $args);
    }

    /**
     * Lazy loads the Github service
     *
     * @return \OAuth\OAuth2\Service\GitHub The GitHub service
     */
    private function getService(): GitHubService
    {
        if ($this->service === null) {
            $this->buildService();
        }

        return $this->service;
    }

    /**
     * Builds the Oauth service
     */
    private function buildService()
    {
        $serviceFactory = new ServiceFactory();
        $storage        = new OauthSession();
        $uriFactory     = new UriFactory();
        $currentUri     = $uriFactory->createFromSuperGlobalArray($_SERVER);

        $currentUri->setPath('/login');
        $currentUri->setQuery('');

        $credentials = new OauthCredentials(
            $this->credentials->getKey(),
            $this->credentials->getSecret(),
            $currentUri->getAbsoluteUri()
        );

        $this->service = $serviceFactory->createService('GitHub', $credentials, $storage, []);
    }
}
