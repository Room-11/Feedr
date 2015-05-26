<?php
/**
 * Handles database calls for feeds
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Database
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Storage\Database;

use Feedr\Network\Http\RequestData;
use Feedr\Auth\User;
use Feedr\Format\TimeAgo;

/**
 * Handles database calls for feeds
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Database
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Feed
{
    /**
     * @var \PDO A database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \PDO $dbConnection A database connection
     */
    public function __construct(\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Creates a new feed
     *
     * @param \Feedr\Network\Http\RequestData $request      The request
     * @param \Feedr\Auth\User                $user         The user
     * @param \Feedr\Storage\Database\Auth    $authDatabase The auth database handler
     */
    public function create(RequestData $request, User $user, Auth $authDatabase)
    {
        $repos = json_decode($request->post('repos'), true);
        $admins = json_decode($request->post('admins'), true);

        $feedId = $this->createFeed($request->post('name'));
        $this->log('newFeed', new \DateTime, $user->get('id'), $feedId);

        $this->addRepositories($feedId, $repos);

        $this->createAdmins($admins, $authDatabase);
        $this->addAdmins($feedId, $admins);
    }

    /**
     * Creates admin accounts
     *
     * @param array                        $admins       List of admins to create
     * @param \Feedr\Storage\Database\Auth $authDatabase The auth database handler
     */
    private function createAdmins(array $admins, Auth $authDatabase)
    {
        foreach ($admins as $admin) {
            $authDatabase->createIfNotExists($admin['id'], $admin['username']);
        }
    }

    /**
     * Creates a feed
     *
     * @param string $name The name of the feed
     *
     * @return int The id of the new feed
     */
    private function createFeed($name)
    {
        $stmt = $this->dbConnection->prepare('INSERT INTO feeds (name) VALUES (:name)');
        $stmt->execute([
            'name' => $name
        ]);

        return $this->dbConnection->lastInsertId('feeds_id_seq');
    }

    /**
     * Adds admins to a feed
     *
     * @param int   $feedId The id of the feed
     * @param array $admins List of admins to add to the feed
     */
    private function addAdmins($feedId, array $admins)
    {
        $stmt = $this->dbConnection->prepare('INSERT INTO admins (feed_id, user_id) VALUES (:feed_id, :user_id)');
        $timestamp = new \DateTime;

        foreach ($admins as $admin) {
            $stmt->execute([
                'feed_id' => $feedId,
                'user_id' => $admin['id'],
            ]);

            $this->log('addedToFeed', $timestamp, $admin['id'], $feedId);
        }
    }

    /**
     * Adds admins to a feed
     *
     * @param int   $feedId The id of the feed
     * @param array $admins List of admins to remove from the feed
     */
    private function removeAdmins($feedId, array $admins)
    {
        if (!$admins) {
            return;
        }

        $query = 'DELETE FROM admins WHERE id IN (' . join(',', array_fill(0, count($admins), '?')) . ')';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute(array_column($admins, 'id'));

        $timestamp = new \DateTime;
        foreach ($admins as $admin) {
            $this->log('removedFromFeed', $timestamp, $admin['id'], $feedId);
        }
    }

    /**
     * Adds repositories to feeds
     *
     * @param int   $feedId       The id of the feed
     * @param array $repositories List of repositories to add
     */
    private function addRepositories($feedId, array $repositories)
    {
        $query = 'INSERT INTO feeds_repositories (feed_id, repository, repository_id) VALUES (:feed_id, :repository, :repository_id)';

        $stmt = $this->dbConnection->prepare($query);

        foreach ($repositories as $repository) {
            $stmt->execute([
                'feed_id'       => $feedId,
                'repository'    => $repository['fullname'],
                'repository_id' => $repository['id'],
            ]);
        }
    }

    /**
     * Removes repositories from feeds
     *
     * @param array $repositories List of repositories to add
     */
    private function removeRepositories(array $repositories)
    {
        if (!$repositories) {
            return;
        }

        $query = 'DELETE FROM feeds_repositories WHERE id IN (' . join(',', array_fill(0, count($repositories), '?')) . ')';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute(array_column($repositories, 'id'));
    }

    /**
     * Logs feed actions
     *
     * @param string    $type      The type of the entry
     * @param \DateTime $timestamp The timestamp for the entry
     * @param int       $userId    The user id for the entry
     * @param int       $feedId    The feed id for the entry
     * @param int       $postId    The post id for the entry
     */
    private function log($type, $timestamp, $userId = null, $feedId = null, $postId = null)
    {
        $query = 'INSERT INTO log (user_id, feed_id, post_id, type, timestamp)';
        $query.= ' VALUES (:user_id, :feed_id, :post_id, :type, :timestamp)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'user_id'   => $userId,
            'feed_id'   => $feedId,
            'post_id'   => $postId,
            'type'      => $type,
            'timestamp' => $timestamp->format('Y-m-d H:i:s'),
        ]);
    }

    public function getFeeds($userId)
    {
        $query = 'SELECT feeds.id AS feedID, feeds.name, users.id AS userID, users.username';
        $query.= ' FROM feeds';
        $query.= ' JOIN admins ON admins.feed_id = feeds.id';
        $query.= ' JOIN users ON users.id = admins.user_id';
        $query.= ' WHERE admins.user_id = :userID';
        $query.= ' OR admins.feed_id = feeds.id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userID' => $userId,
        ]);

        $recordSet = $stmt->fetchAll();

        if (!$recordSet) {
            return [];
        }

        $ids = [];

        $result = [];

        foreach ($recordSet as $record) {
            if (array_key_exists($record['feedID'], $result)) {
                $result[$record['feedID']]['admins'][$record['userID']] = $record['username'];

                continue;
            }

            $ids[] = $record['feedID'];

            $result[$record['feedID']] = [
                'name'   => $record['name'],
                'admins' => [
                    $record['userID'] => $record['username'],
                ],
                'posts'    => $this->getPostCount($record['feedID']),
                'requests' => 0,
            ];
        }

        $query = 'SELECT log.feed_id AS id, count(log.feed_id) AS count';
        $query.= ' FROM log';
        $query.= ' WHERE log.feed_id IN(' . join(',', array_fill(0, count($ids), '?')) . ')';
        $query.= ' GROUP BY log.feed_id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute($ids);

        foreach ($stmt->fetchAll() as $record) {
            $result[$record['id']]['requests'] = $record['count'];
        }

        return $result;
    }

    private function getPostCount($feedId)
    {
        $query = 'SELECT count(posts.id)';
        $query.= ' FROM posts, feeds_repositories';
        $query.= ' WHERE posts.feed_repository_id = feeds_repositories.id';
        $query.= ' AND feeds_repositories.feed_id = :feedID';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        return $stmt->fetchColumn(0);
    }

    public function getPosts($feedId, TimeAgo $timeFormatter)
    {
        $query = 'SELECT posts.id AS postid, posts.release_id, posts.avatar_url, posts.version, posts.timestamp, posts.content, posts.url, posts.username, feeds_repositories.repository';
        $query.= ' FROM posts, feeds_repositories';
        $query.= ' WHERE feeds_repositories.feed_id = :feedID';
        $query.= ' AND feeds_repositories.id = posts.feed_repository_id';
        $query.= ' ORDER BY posts.timestamp DESC';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        $recordSet = $stmt->fetchAll();

        if (!$recordSet) {
            return [];
        }

        foreach ($recordSet as $index => $record) {
            $recordSet[$index]['timestamp'] = $timeFormatter->calculate(new \DateTime($record['timestamp']));

            $recordSet[$index]['full_content'] = $record['content'];
            $recordSet[$index]['datetime']     = new \DateTime($record['timestamp']);

            if (strlen($record['content']) > 250) {
                $recordSet[$index]['content'] = substr($record['content'], 0, 250) . '...';
            }
        }

        return $recordSet;
    }

    public function getFeed($feedId, TimeAgo $timeFormatter, $log = true)
    {
        if ($log) {
            $this->log('feedRequested', new \DateTime, null, $feedId);
        }

        $stmt = $this->dbConnection->prepare('SELECT name FROM feeds WHERE id = :id');
        $stmt->execute([
            'id' => $feedId,
        ]);

        return [
            'id'    => $feedId,
            'name'  => $stmt->fetchColumn(0),
            'posts' => $this->getPosts($feedId, $timeFormatter),
            'repos' => $this->getRepositories($feedId),
            'admins'=> $this->getAdmins($feedId),
        ];
    }

    private function getRepositories($feedId)
    {
        $query = 'SELECT id, repository, repository_id FROM feeds_repositories WHERE feed_id = :feedID';
        $query.= ' ORDER BY id ASC';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        return $stmt->fetchAll();
    }

    private function getAdmins($feedId)
    {
        $query = 'SELECT users.id, users.username';
        $query.= ' FROM users';
        $query.= ' JOIN admins ON admins.user_id = users.id';
        $query.= ' WHERE admins.feed_id = :feedID';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        return $stmt->fetchAll();
    }

    public function isAdmin($userId, $feedId)
    {
        $query = 'SELECT COUNT(id) FROM admins WHERE feed_id = :feedID AND user_id = :userID';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
            'userID' => $userId,
        ]);

        return !!$stmt->fetchColumn(0);
    }

    /**
     * Updates a new feed
     *
     * @param int                             $feedId       The feed ID
     * @param \Feedr\Network\Http\RequestData $request      The request
     * @param \Feedr\Auth\User                $user         The user
     * @param \Feedr\Storage\Database\Auth    $authDatabase The auth database handler
     */
    public function update($feedId, RequestData $request, User $user, Auth $authDatabase)
    {
        $repos = json_decode($request->post('repos'), true);
        $admins = json_decode($request->post('admins'), true);

        $this->updateRepositories($feedId, $repos);

        $this->createAdmins($admins, $authDatabase);
        $this->updateAdmins($feedId, $admins);

        $stmt = $this->dbConnection->prepare('UPDATE feeds SET name = :name WHERE id = :feedID');
        $stmt->execute([
            'name'   => $request->post('name'),
            'feedID' => $feedId,
        ]);

        $this->log('updateFeed', new \DateTime, $user->get('id'), $feedId);
    }

    private function updateRepositories($feedId, array $repositories)
    {
        $indexedRepos = [];
        foreach ($repositories as $repo) {
            $indexedRepos[$repo['id']] = $repo;
        }

        $query = 'SELECT id, repository_id FROM feeds_repositories WHERE feed_id = :feedID';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        $deletedRepos = [];

        foreach ($stmt->fetchAll() as $repo) {
            if (!array_key_exists($repo['repository_id'], $indexedRepos)) {
                $deletedRepos[] = $repo;

                continue;
            }

            unset($indexedRepos[$repo['repository_id']]);
        }

        $this->removeRepositories($deletedRepos);
        $this->addRepositories($feedId, $indexedRepos);
    }

    private function updateAdmins($feedId, array $admins)
    {
        $indexedAdmins = [];
        foreach ($admins as $admin) {
            $indexedAdmins[$admin['id']] = $admin;
        }

        $query = 'SELECT id, user_id FROM admins WHERE feed_id = :feedID';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedID' => $feedId,
        ]);

        $deletedAdmins = [];

        foreach ($stmt->fetchAll() as $admin) {
            if (!array_key_exists($admin['user_id'], $indexedAdmins)) {
                $deletedAdmins[] = $admin;

                continue;
            }

            unset($indexedAdmins[$admin['user_id']]);
        }

        $this->removeAdmins($feedId, $deletedAdmins);
        $this->addAdmins($feedId, $indexedAdmins);
    }
}
