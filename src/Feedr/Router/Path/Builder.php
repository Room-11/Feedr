<?php
/**
 * Interface for path factories
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Path
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Router\Path;

/**
 * Interface for path factories
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Path
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Builder
{
    /**
     * Creates new instance of a path
     *
     * @param string $rawPath The raw path
     *
     * @return \Feedr\Router\Path\Path The built path
     */
    public function build($rawPath);
}
