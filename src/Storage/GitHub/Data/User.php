<?php declare(strict_types=1);
/**
 * GitHub user
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
namespace Feedr\Storage\GitHub\Data;

/**
 * GitHub user
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class User
{
    /**
     * @var int The id
     */
    private $id;

    /**
     * @var string The name
     */
    private $name;

    /**
     * @var string The avatar url
     */
    private $avatarUrl;

    /**
     * @var string The HTMl url
     */
    private $htmlUrl;

    /**
     * Creates instance
     *
     * @param array $user The user data
     */
    public function __construct(array $user)
    {
        $this->id        = (int) $user['id'];
        $this->name      = $user['login'];
        $this->avatarUrl = $user['avatar_url'];
        $this->htmlUrl   = $user['html_url'];
    }

    /**
     * Gets the id
     *
     * @return int The id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the name
     *
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the full name
     *
     * @return string The full name
     */
    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    /**
     * Gets the HTML url
     *
     * @return string
     */
    public function getHtmlUrl(): string
    {
        return $this->htmlUrl;
    }
}
