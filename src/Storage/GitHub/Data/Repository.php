<?php declare(strict_types=1);
/**
 * GitHub repository
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
 * GitHub repository
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Repository
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
     * @var string The full name
     */
    private $fullName;

    /**
     * @var \Feedr\Storage\GitHub\Data\User The owner
     */
    private $owner;

    /**
     * @var string The HTMl url
     */
    private $htmlUrl;

    /**
     * @var string The description
     */
    private $description;

    /**
     * @var string The releases url
     */
    private $releasesUrl;

    /**
     * @var \DateTimeImmutable The creation date
     */
    private $createdAt;

    /**
     * Creates instance
     *
     * @param array $repository The repository data
     */
    public function __construct(array $repository)
    {
        $this->id          = (int) $repository['id'];
        $this->name        = $repository['name'];
        $this->fullName    = $repository['full_name'];
        $this->owner       = new User($repository['owner']);
        $this->htmlUrl     = $repository['html_url'];
        $this->description = $repository['description'];
        $this->releasesUrl = $repository['releases_url'];
        $this->createdAt   = new \DateTimeImmutable($repository['created_at']);
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
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * Gets the owner
     *
     * @return \Feedr\Storage\GitHub\Data\User The owner
     */
    public function getOwner(): User
    {
        return $this->owner;
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

    /**
     * gets the description
     *
     * @return string The description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Gets the releases url
     *
     * @return string The releases url
     */
    public function getReleasesUrl(): string
    {
        return $this->releasesUrl;
    }

    /**
     * Gets the creation date
     *
     * @return \DateTimeImmutable The creation date
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
