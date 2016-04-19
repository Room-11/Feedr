<?php declare(strict_types=1);
/**
 * GitHub API storage
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
namespace Feedr\Storage\Github;

use OAuth\ServiceFactory;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session as OauthSession;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth2\Service\GitHub as GitHubService;
use OAuth\Common\Exception\Exception;

/**
 * GitHub API storage
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Service
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
     * @var \Feedr\Storage\Github\ErrorHandler The custom error handler
     */
    private $errorHandler;

    /**
     * @var null|\OAuth\OAuth2\Service\GitHub The GitHub service
     */
    private $service;

    /**
     * Creates instance
     *
     * @param string                             $key          The GitHub API key
     * @param string                             $secret       The GitHub API secret
     * @param \Feedr\Storage\Github\ErrorHandler $errorHandler The error handler
     */
    public function __construct(string $key, string $secret, ErrorHandler $errorHandler)
    {
        $this->key          = $key;
        $this->secret       = $secret;
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
     * @return array
     */
    private function handleError(Exception $exception): array
    {
        try {
            $this->errorHandler->handle($exception);
        } catch (NotFoundException $e) {
            return [];
        }
    }

    /**
     * Searches for repositories
     *
     * @param string $q The search word(s)
     *
     * @return array List of repositories found
     */
    public function searchRepository(string $q): RepositoryCollection
    {
        if (filter_var($q, FILTER_VALIDATE_URL) !== false || substr_count($q, '/') === 1) {
            return RepositoryCollection::createFromGitHubApiResult($this->searchRepositoryByName($q));
        }

        return RepositoryCollection::createFromGitHubApiResult($this->searchRepositoryByKeywords($q));
    }

    /**
     * Searches a repository by name
     *
     * @param string $q The name of the repo (either a repo URI or a owner/name pair)
     *
     * @return array List of repositories found
     */
    private function searchRepositoryByName(string $q): array
    {
        $parts = explode('/', $q);

        $repo  = array_pop($parts);
        $owner = array_pop($parts);

        $repositories = $this->request('/repos/' . $owner . '/' . $repo);

        if (!$repositories) {
            return [];
        }

        return [$repositories];
    }

    /**
     * Searches a repository by keyword(s)
     *
     * @param string $q The search word(s)
     *
     * @return array List of repositories found
     */
    private function searchRepositoryByKeywords(string $q): array
    {
        $repositories = $this->request('/search/repositories?q=' . urlencode($q));

        if (!$repositories) {
            return [];
        }

        return $repositories['items'];
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
     * Retrieves releases based on a list of owner/name repo pairs
     *
     * @param array $ids The owner/name repo pairs
     *
     * @return array List of releases sorted by timestamp
     */
    public function getReleases(array $ids): array
    {
        $releases = [];

        foreach ($ids as $id) {
            $repoReleases = $this->request('repos/' . $id . '/releases');

            foreach ($repoReleases as $release) {

                $releases[$release['published_at']] = [
                    'url'         => $release['html_url'],
                    'version'     => $release['tag_name'],
                    'name'        => $id,
                    'avatar'      => $release['author']['avatar_url'],
                    'description' => $release['body'],
                    'timestamp'   => new \DateTime($release['published_at']),
                ];
            }
        }

        krsort($releases);

        return $releases;
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
