<?php
/**
 * Generates a random string using mcrypt
 *
 * PHP version 5.5
 *
 * @category   Feedr
 * @package    Security
 * @subpackage Generator
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Security\Generator;

use Feedr\Security\Generator;

/**
 * Generates a random string using mcrypt
 *
 * @category   Feedr
 * @package    Security
 * @subpackage Generator
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Mcrypt implements Generator
{
    /**
     * Creates instance
     *
     * @throws \Feedr\Security\Generator\UnsupportedAlgorithmException When mcrypt is not installed
     */
    public function __construct()
    {
        if (!function_exists('mcrypt_create_iv')) {
            throw new UnsupportedAlgorithmException('Mcrypt isn\'t installed on the system.');
        }
    }

    /**
     * Generates a random string
     *
     * @param int $length The length of the random string to be generated
     *
     * @return string The generated token
     */
    public function generate($length)
    {
        return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
    }
}
