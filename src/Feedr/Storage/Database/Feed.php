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
        $this->createAdmins(json_decode($request->post('admins'), true), $authDatabase);

        $feedId = $this->createFeed($request->post('name'));

        $this->addRepositories($feedId, json_decode($request->post('repos'), true));

        $this->addAdmins($feedId, json_decode($request->post('admins'), true));

        $timestamp = new \DateTime();

        $this->log('newFeed', $timestamp, $user->get('id'), $feedId);

        foreach (json_decode($request->post('admins'), true) as $admin) {
            $this->log('addedToFeed', $timestamp, $admin['id'], $feedId);
        }
    }

    /**
     * Creates admin accounts
     *
     * @param array                        $admin        List of admins to create
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
     * @param array $admin  List of admins to add to the feed
     */
    private function addAdmins($feedId, array $admins)
    {
        $stmt = $this->dbConnection->prepare('INSERT INTO admins (feed_id, user_id) VALUES (:feed_id, :user_id)');

        foreach ($admins as $admin) {
            $stmt->execute([
                'feed_id' => $feedId,
                'user_id' => $admin['id'],
            ]);
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
        $query = 'INSERT INTO feeds_repositories (feed_id, repository) VALUES (:feed_id, :repository)';

        $stmt = $this->dbConnection->prepare($query);

        foreach ($repositories as $repository) {
            $stmt->execute([
                'feed_id'    => $feedId,
                'repository' => $repository,
            ]);
        }
    }

    /**
     * Logs feed actions
     *
     * @param string    $type      The type of the entry
     * @param \DateTime $timestamp The timestano for the entry
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
        $query = 'SELECT feeds.id AS feedid, feeds.name, users.id AS userid, users.username';
        $query.= ' FROM feeds';
        $query.= ' JOIN admins ON admins.feed_id = feeds.id';
        $query.= ' JOIN users ON users.id = admins.user_id';
        $query.= ' WHERE admins.user_id = :userid';
        $query.= ' OR admins.feed_id = feeds.id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userid' => $userId,
        ]);

        $recordset = $stmt->fetchAll();

        if (!$recordset) {
            return [];
        }

        $ids = [];

        $result = [];

        foreach ($recordset as $record) {
            if (array_key_exists($record['feedid'], $result)) {
                $result[$record['feedid']]['admins'][$record['userid']] = $record['username'];

                continue;
            }

            $ids[] = $record['feedid'];

            $result[$record['feedid']] = [
                'name'   => $record['name'],
                'admins' => [
                    $record['userid'] => $record['username'],
                ],
                'posts'    => $this->getPostCount($record['feedid']),
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
        $query.= ' AND feeds_repositories.feed_id = :feedid';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedid' => $feedId,
        ]);

        return $stmt->fetchColumn(0);
    }

    public function getPosts($feedId, TimeAgo $timeFormatter)
    {
        $query = 'SELECT posts.id AS postid, posts.release_id, posts.avatar_url, posts.version, posts.timestamp, posts.content, posts.url, feeds_repositories.repository';
        $query.= ' FROM posts, feeds_repositories';
        $query.= ' WHERE feeds_repositories.feed_id = :feedid';
        $query.= ' AND feeds_repositories.id = posts.feed_repository_id';
        $query.= ' ORDER BY posts.timestamp DESC';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feedid' => $feedId,
        ]);

        $recordset = $stmt->fetchAll();

        if (!$recordset) {
            return [];
        }

        foreach ($recordset as $index => $record) {
            $recordset[$index]['timestamp'] = $timeFormatter->calculate(new \DateTime($record['timestamp']));

            $recordset[$index]['full_content'] = $record['content'];
            $recordset[$index]['datetime']     = new \DateTime($record['timestamp']);

            if (strlen($record['content']) > 250) {
                $recordset[$index]['content'] = substr($record['content'], 0, 250) . '...';
            }
        }

        return $recordset;
    }

    public function getFeed($feedId, TimeAgo $timeFormatter)
    {
        $this->log('feedRequested', new \DateTime(), null, $feedId);

        return [
            'posts' => $this->getPosts($feedId, $timeFormatter),
        ];
    }
}
