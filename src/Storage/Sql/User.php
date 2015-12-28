<?php declare(strict_types=1);
/**
 * User storage
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
 * User storage
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class User
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
     * Persists an user
     *
     * @param array $user The user data
     */
    public function persistUser(array $user)
    {
        if (!$this->userExists($user['id'])) {
            $this->addUser($user);
        } else {
            $this->updateUser($user);
        }
    }

    /**
     * Adds an user
     *
     * @param array $user The user data
     */
    private function addUser(array $user)
    {
        $query = 'INSERT INTO users';
        $query.= ' (id, username, name, avatar, url, created)';
        $query.= ' VALUES';
        $query.= ' (:id, :username, :name, :avatar, :url, :created)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id'       => $user['id'],
            'username' => $user['login'],
            'name'     => $user['name'],
            'avatar'   => $user['avatar_url'],
            'url'      => $user['html_url'],
            'created'  => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Updates an user
     *
     * @param array $user The user data
     */
    private function updateUser(array $user)
    {
        $query = 'UPDATE users';
        $query.= ' SET id = :id, username = :username, name = :name, avatar = :avatar, url = :url';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id'       => $user['id'],
            'username' => $user['login'],
            'name'     => $user['name'],
            'avatar'   => $user['avatar_url'],
            'url'      => $user['html_url'],
        ]);
    }

    /**
     * Checks whether a user already exists
     *
     * @param int $id The id of the user
     *
     * @return bool True when the user already exists
     */
    private function userExists(int $id): bool
    {
        $query = 'SELECT COUNT(id)';
        $query.= ' FROM users';
        $query.= ' WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id' => $id,
        ]);

        return (bool) $stmt->fetchColumn(0);
    }
}
