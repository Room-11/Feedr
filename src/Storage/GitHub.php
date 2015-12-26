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
        return json_decode($this->getService()->request($path), true);
    }

    /**
     * Searches for repositories
     *
     * @param string $q The search word(s)
     *
     * @return array List of repositories found
     */
    public function searchRepository(string $q): array
    {
        if (filter_var($q, FILTER_VALIDATE_URL) !== false || substr_count($q, '/') === 1) {
            $parts = explode('/', $q);

            $repo  = array_pop($parts);
            $owner = array_pop($parts);

            return $this->request('/repos/' . $owner . '/' . $repo);
        }

        return $this->request('/search/repositories?q=' . urlencode($q))['items'];
    }

    /**
     * Searches for users
     *
     * @param string $q The search word(s)
     *
     * @return array List of users found
     */
    public function searchUser(string $q): array
    {
        return $this->request('/search/users?q=' . urlencode($q))['items'];
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
        return call_user_func_array(array($this->getService(), $method), $args);
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
            $currentUri->setPath('/login');
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
