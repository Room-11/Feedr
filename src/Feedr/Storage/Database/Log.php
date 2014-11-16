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
            'userid' => $userId,
        ]);

        $recordset = $stmt->fetchAll();

        if (!$recordset) {
            return [];
        }

        $result = [];

        foreach ($recordset as $record) {
            $record['timestamp'] = $timeFormatter->calculate(new \DateTime($record['timestamp']));

            $result[$record['logid']] = $record;
        }

        return $result;
    }
}
