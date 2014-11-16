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
        $query.= ' WHERE log.user_id = :userid';
        $query.= ' ORDER BY log.id DESC';
        $query.= ' LIMIT 10 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userid'   => $userId,
        ]);

        $logs1 = $stmt->fetchAll();

        $query = 'SELECT log.id AS logid, log.type, log.timestamp, posts.id AS postid';
        $query.= ' FROM log, posts, feeds_repositories, admins';
        $query.= ' WHERE posts.id = log.post_id';
        $query.= ' AND feeds_repositories.id = posts.feed_repository_id';
        $query.= ' AND admins.feed_id = feeds_repositories.feed_id';
        $query.= ' AND admins.user_id = :userid';
        $query.= ' ORDER BY log.id DESC LIMIT 10 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userid'   => $userId,
        ]);

        $logs2 = $stmt->fetchAll();

        $recordset = array_merge($logs1, $logs2);

        if (!$recordset) {
            return [];
        }

        usort($recordset, function($a, $b) {
            return $b['logid'] - $a['logid'];
        });

        $result = [];

        foreach ($recordset as $index => $record) {
            $record['timestamp'] = $timeFormatter->calculate(new \DateTime($record['timestamp']));

            $result[$record['logid']] = $record;

            if ($index === 9) {
                break;
            }
        }

        return $result;
    }
}
