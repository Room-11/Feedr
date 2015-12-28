<?php declare(strict_types=1);
/**
 * Log storage
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://pieterhordijk.com>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage\Sql;

/**
 * Log storage
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Log
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \PDO $dbConnection The database connection
     */
    public function __construct(\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Adds a login
     *
     * @param int    $userId    The user id
     * @param string $ipAddress The IP address
     */
    public function addLogInEntry(int $userId, string $ipAddress)
    {
        $query = 'INSERT INTO log';
        $query.= ' (action, ip, user_id, timestamp)';
        $query.= ' VALUES';
        $query.= ' (:action, :ip, :user_id, :timestamp)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'action'    => 'login',
            'ip'        => $ipAddress,
            'user_id'   => $userId,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function getLastUserNotifications(int $userId)
    {
        $loginNotifications = $this->getLastLogInEntries($userId);

        return $loginNotifications;
    }

    private function getLastLogInEntries(int $userId): array
    {
        $query = 'SELECT id, action, user_id, timestamp, data, ip';
        $query.= ' FROM log';
        $query.= ' WHERE user_id = :user_id';
        $query.= ' AND action = :action';
        $query.= ' ORDER BY id DESC';
        $query.= ' LIMIT 20 OFFSET 0';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'user_id' => $userId,
            'action'  => 'login',
        ]);

        return $stmt->fetchAll();
    }
}
