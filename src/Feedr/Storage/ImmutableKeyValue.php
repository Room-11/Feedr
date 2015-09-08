<?php
/**
 * Interface for immutable key-value stores
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Storage;

/**
 * Interface for immutable key-value stores
 *
 * @category   Feedr
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface ImmutableKeyValue
{
    /**
     * Gets a value from the storage based on the key or null on non existent key
     *
     * @param string $key     The key of which to get the value for
     * @param string $default The default value to return when the key does not exist
     *
     * @return mixed The value which belongs to the key or the default value
     */
    public function get($key, $default = null);

    /**
     * Checks whether the key is in the storage
     *
     * @param string $key The key of which the validity will be checked
     *
     * @return boolean true when the key is valid
     */
    public function isKeyValid($key);
}
