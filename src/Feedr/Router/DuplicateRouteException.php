<?php
/**
 * Exception which gets thrown when trying to add multiple routes with the same name
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Router;

/**
 * Exception which gets thrown when trying to add multiple routes with the same name
 *
 * @category   Feedr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class DuplicateRouteException extends \Exception
{
}
