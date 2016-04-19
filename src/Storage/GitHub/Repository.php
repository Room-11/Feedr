<?php declare(strict_types=1);
/**
 * GitHub API repository client
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

use Feedr\Storage\GitHub\Data\RepositoryCollection;

/**
 * GitHub API repository client
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Repository
{
    /**
     * @var \Feedr\Storage\GitHub\Client The GitHub API client
     */
    private $client;

    /**
     * @var \Feedr\Storage\GitHub\Data\RepositoryCollection The repository collection
     */
    private $collection;

    /**
     * Creates instance
     *
     * @param \Feedr\Storage\GitHub\Client                    $client     The GitHub API client
     * @param \Feedr\Storage\GitHub\Data\RepositoryCollection $collection The repository collection
     */
    public function __construct(Client $client, RepositoryCollection $collection)
    {
        $this->client     = $client;
        $this->collection = $collection;
    }

    /**
     * Searches for repositories
     *
     * @param string $q The search word(s)
     *
     * @return \Feedr\Storage\GitHub\Data\RepositoryCollection The repository collection
     */
    public function search(string $q): RepositoryCollection
    {
        if (filter_var($q, FILTER_VALIDATE_URL) !== false || substr_count($q, '/') === 1) {
            $this->collection->addFromApiResult($this->searchByName($q));
        } else {
            $this->collection->addFromApiResult($this->searchByKeywords($q));
        }

        return $this->collection;
    }

    /**
     * Searches a repository by name
     *
     * @param string $q The name of the repo (either a repo URI or a owner/name pair)
     *
     * @return array List of repositories found
     */
    private function searchByName(string $q): array
    {
        $parts = explode('/', $q);

        $repo  = array_pop($parts);
        $owner = array_pop($parts);

        $repositories = $this->client->request('/repos/' . $owner . '/' . $repo);

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
    private function searchByKeywords(string $q): array
    {
        $repositories = $this->client->request('/search/repositories?q=' . urlencode($q));

        if (!$repositories) {
            return [];
        }

        return $repositories['items'];
    }
}
