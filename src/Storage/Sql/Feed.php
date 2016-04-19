<?php declare(strict_types=1);
/**
 * Feed storage
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

use Feedr\Form\CreateFeed;

/**
 * Feed storage
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage Sql
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Feed
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * @var \Feedr\Storage\Sql\Log The log storage
     */
    private $log;

    /**
     * Creates instance
     *
     * @param \PDO                   $dbConnection The database connection
     * @param \Feedr\Storage\Sql\Log $log          The log storage
     */
    public function __construct(\PDO $dbConnection, Log $log)
    {
        $this->dbConnection = $dbConnection;
        $this->log          = $log;
    }

    /**
     * Adds a new feed
     *
     * @param \Feedr\Form\CreateFeed $form   The form
     * @param int                    $userId The user id
     */
    public function create(CreateFeed $form, int $userId)
    {
        $feedId = $this->createFeed($form);

        $this->log->addCreateFeedEntry($userId, $feedId);

        foreach (json_decode($form['administrators']->getValue(), true) as $administrator) {
            $this->log->addAddAdminEntry(
                $userId,
                $this->addAdministrator($feedId, $administrator),
                $feedId
            );
        }
    }

    /**
     * Stores the feed
     *
     * @param \Feedr\Form\CreateFeed $form The form
     *
     * @return int The id of the created feed
     */
    private function createFeed(CreateFeed $form): int
    {
        $query = 'INSERT INTO feeds (name, created, password) VALUES (:name, :created, :password)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'name'     => $form['name']->getValue(),
            'created'  => (new \DateTime())->format('Y-m-d H:i:s'),
            'password' => $form['visibility']->getValue() === 'private' ? password_hash($form['password']->getValue(), PASSWORD_DEFAULT, ['cost' => 14]) : null,
        ]);

        return (int) $this->dbConnection->lastInsertId('feeds_id_seq');
    }

    private function addAdministrator(int $feedId, int $userId): int
    {
        $query = 'INSERT INTO feeds_admins (feed_id, user_id, created) VALUES (:feed_id, :user_id, :created)';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'feed_id' => $feedId,
            'user_id' => $userId,
            'created' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return $this->dbConnection->lastInsertId('feeds_admins_id_seq');
    }
}
