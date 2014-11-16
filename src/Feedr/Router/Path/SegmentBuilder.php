<?php
/**
 * Interface for segment factories
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Path
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Router\Path;

/**
 * Interface for segment factories
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Path
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface SegmentBuilder
{
    /**
     * Creates instances of segments
     *
     * @param string $rawValue The raw value of the segment
     *
     * @return \Feedr\Router\Path\Part The created segment
     */
    public function build($rawValue);
}
