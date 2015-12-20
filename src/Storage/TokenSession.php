<?php declare(strict_types=1);
/**
 * Session token storage
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage;

use CodeCollab\CsrfToken\Storage\Storage;
use CodeCollab\Http\Session\Session;

/**
 * Session token storage
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class TokenSession implements Storage
{
    /**
     * @var \CodeCollab\Http\Session\Session Instance of a session
     */
    private $session;

    /**
     * Creates instance
     *
     * @param \CodeCollab\Http\Session\Session $session Instance of a session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Checks whether a token has been generated
     *
     * @param string $key The key of the token
     *
     * @return bool True when a token has been generated
     */
    public function exists(string $key): bool
    {
        return $this->session->exists($key);
    }

    /**
     * Gets the token
     *
     * @param string $key The key of the token
     *
     * @return string The token
     */
    public function get(string $key): string
    {
        return $this->session->get($key);
    }

    /**
     * Sets the token
     *
     * @param string $key   The key of the token
     * @param string $token The token
     */
    public function set(string $key, string $token)
    {
        $this->session->set($key, $token);
    }
}
