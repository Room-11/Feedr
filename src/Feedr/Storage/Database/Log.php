<?php
/**
 * Handles database calls for logs
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

use Feedr\Format\TimeAgo;

/**
 * Handles database calls for logs
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Database
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Log
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

    public function getLogItemLimited($userId, TimeAgo $timeFormatter)
    {
        $query = 'SELECT log.id AS logid, log.type, log.timestamp, feeds.id AS feedid, feeds.name AS feedname';
        $query.= ' FROM log';
        $query.= ' RIGHT JOIN feeds ON feeds.id = log.feed_id';
        $query.= ' WHERE log.user_id = :userID';
        $query.= ' ORDER BY log.id DESC';
        $query.= ' LIMIT 10 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userID'   => $userId,
        ]);

        $userLogs = $stmt->fetchAll();

        $query = 'SELECT log.id AS logid, log.type, log.timestamp, log.post_id AS postid';
        $query.= ' FROM log, posts, feeds_repositories, admins';
        $query.= ' WHERE posts.id = log.post_id';
        $query.= ' AND feeds_repositories.id = posts.feed_repository_id';
        $query.= ' AND admins.feed_id = feeds_repositories.feed_id';
        $query.= ' AND admins.user_id = :userID';
        $query.= ' ORDER BY log.id DESC LIMIT 10 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userID'   => $userId,
        ]);

        $postLogs = $stmt->fetchAll();

        $query = 'SELECT log.id AS logid, log.type, log.timestamp, log.feed_id AS feedid';
        $query.= ' FROM log, feeds_repositories, admins';
        $query.= ' WHERE feeds_repositories.feed_id = log.feed_id';
        $query.= ' AND admins.feed_id = feeds_repositories.feed_id';
        $query.= ' AND admins.user_id = :userID';
        $query.= ' ORDER BY log.id DESC';
        $query.= ' LIMIT 10 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userID'   => $userId,
        ]);

        $feedLogs = $stmt->fetchAll();

        $recordSet = array_merge($userLogs, $postLogs, $feedLogs);

        if (!$recordSet) {
            return [];
        }

        usort($recordSet, function($a, $b) {
            return $b['logid'] - $a['logid'];
        });

        $result = [];

        foreach ($recordSet as $index => $record) {
            $record['timestamp'] = $timeFormatter->calculate(new \DateTime($record['timestamp']));

            $result[$record['logid']] = $record;

            if ($index === 9) {
                break;
            }
        }

        return $result;
    }
}
