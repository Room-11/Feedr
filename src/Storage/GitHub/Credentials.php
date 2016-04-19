<?php declare(strict_types=1);
/**
 * GitHub API credentials
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
namespace Feedr\Storage\GitHub;

/**
 * GitHub API credentials
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Credentials
{
    /**
     * @var string The GitHub API key
     */
    private $key;

    /**
     * @var string The GitHub API secret
     */
    private $secret;

    /**
     * Creates instance
     *
     * @param string $key    TheGitHub API key
     * @param string $secret The GitHub API secret
     */
    public function __construct(string $key, string $secret)
    {
        $this->key    = $key;
        $this->secret = $secret;
    }

    /**
     * Gets the API key
     *
     * @return string The API key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Gets the API secret
     *
     * @return string The API secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }
}
