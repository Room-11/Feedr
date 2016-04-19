<?php declare(strict_types=1);
/**
 * Exception which gets thrown when trying to retrieve a resource that could not be found
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
 * Exception which gets thrown when trying to retrieve a resource that could not be found
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class NotFoundException extends \Exception
{
}
