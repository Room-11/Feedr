<?php
/**
 * Handles user authentication
 *
 * PHP version 5.5
 *
 * @category   Feedr
 * @package    Auth
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Auth;

use Feedr\Storage\Session;
use Feedr\Storage\Database\Auth;

/**
 * Handles user authentication
 *
 * @category   Feedr
 * @package    Auth
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class User
{
    /**
     * @var \Feedr\Storage\Session The session
     */
    private $sessionStorage;

    /**
     * @var \Feedr\Storage\Database\Auth Database handler
     */
    private $database;

    /**
     * Creates instance
     *
     * @param \Feedr\Storage\Session       $sessionStorage The session
     * @param \Feedr\Storage\Database\Auth $database       Database handler
     */
    public function __construct(Session $sessionStorage, Auth $database)
    {
        $this->sessionStorage = $sessionStorage;
        $this->database       = $database;
    }

    /**
     * Checks whether the user is logged in
     *
     * @return boolean True when the user is logged in
     */
    public function isLoggedIn()
    {
        return $this->sessionStorage->isKeyValid('user');
    }

    /**
     * Tries to log the user in
     *
     * @param array  $userdata The userdata
     * @param string $ip       The IP address
     *
     * @return boolean True when the user successfully authenticated
     */
    public function login(array $userdata, $ip)
    {
        if (array_key_exists('id', $userdata)) {
            $this->sessionStorage->set('user', $userdata);

            $this->database->login($userdata['id'], $userdata['login'], $ip);

            return true;
        }

        return false;
    }

    /**
     * Logs the user out
     */
    public function logout()
    {
        $this->sessionStorage->regenerate();
    }

    /**
     * Gets a user property
     *
     * @param string $key The key to get
     *
     * @return mixed The value
     */
    public function get($key)
    {
        $userdata = $this->sessionStorage->get('user');

        return $userdata[$key];
    }
}
