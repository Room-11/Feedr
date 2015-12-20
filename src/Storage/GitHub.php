<?php declare(strict_types=1);
/**
 * GitHub API storage
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage;

use OAuth\ServiceFactory;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session as OauthSession;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth2\Service\GitHub as GitHubService;

/**
 * GitHub API storage
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class GitHub
{
    /**
     * @var string The GitHub API key
     */
    private $key;

    /**
     * @var string The GitHub API secret
     */
    private $secret;

    /**
     * @var null|\OAuth\OAuth2\Service\GitHub The GitHub service
     */
    private $service;

    /**
     * Creates instance
     *
     * @param string $key    The GitHub API key
     * @param string $secret The GitHub API secret
     */
    public function __construct(string $key, string $secret)
    {
        $this->key    = $key;
        $this->secret = $secret;
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
        $service = $this->getService();

        return json_decode($service->request($path), true);
    }

    /**
     * Lazy loads the Github service
     *
     * @return \OAuth\OAuth2\Service\GitHub The GitHub service
     */
    private function getService(): GitHubService
    {
        if ($this->service === null) {
            $serviceFactory = new ServiceFactory();
            $storage        = new OauthSession();
            $uriFactory     = new UriFactory();
            $currentUri     = $uriFactory->createFromSuperGlobalArray($_SERVER);
            $currentUri->setQuery('');
            $credentials = new Credentials(
                $this->key,
                $this->secret,
                $currentUri->getAbsoluteUri()
            );
            $this->service = $serviceFactory->createService('GitHub', $credentials, $storage, array());
        }

        return $this->service;
    }
}
