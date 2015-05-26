<?php
/**
 * Handles database calls for user authentication
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

/**
 * Handles database calls for user authentication
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Database
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Auth
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
     * Creates user when needed and logs authentication
     *
     * @param int    $id       The user id
     * @param string $username The username
     * @param string $ip       The IP address
     */
    public function login($id, $username, $ip)
    {
        $this->createIfNotExists($id, $username);

        $this->log($id, $ip);
    }

    /**
     * Checks whether the user exists and creates it when needed
     *
     * @param int    $id       The user id
     * @param string $username The username
     */
    public function createIfNotExists($id, $username)
    {
        if (!$this->userExists($id)) {
            $this->createUser($id, $username);
        }
    }

    /**
     * Checks whether the user already exists
     *
     * @param int $id The user id
     *
     * @return boolean true when the user already exists
     */
    private function userExists($id)
    {
        $stmt = $this->dbConnection->prepare('SELECT COUNT(id) FROM users WHERE id = :id');
        $stmt->execute([
            'id' => $id,
        ]);

        return !!$stmt->fetchColumn(0);
    }

    /**
     * Creates a user
     *
     * @param int    $id       The user id
     * @param string $username The username
     */
    private function createUser($id, $username)
    {
        $stmt = $this->dbConnection->prepare('INSERT INTO users (id, username) VALUES (:id, :username)');
        $stmt->execute([
            'id'       => $id,
            'username' => $username,
        ]);
    }

    /**
     * Logs an authentication
     *
     * @param int    $id The user id
     * @param string $ip The IP address
     */
    private function log($id, $ip)
    {
        $query = 'INSERT INTO auth_log (user_id, ip, timestamp) VALUES (:userID, :ip, :timestamp)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'userID'    => $id,
            'ip'        => $ip,
            'timestamp' => (new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
        ]);
    }
}
