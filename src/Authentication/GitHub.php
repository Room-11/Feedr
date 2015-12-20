<?php declare(strict_types=1);
/**
 * GitHub authentication object
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Authentication
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Authentication;

use CodeCollab\Authentication\User;

/**
 * GitHub authentication object
 *
 * @category   Feedr
 * @package    Authentication
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class GitHub extends User
{
    /**
     * Logs a user in
     *
     * @param array $user The user data
     */
    public function logInWithOauth(array $user)
    {
        $this->session->set('user', $user);
    }
}
