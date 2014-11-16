<?php
/**
 * Builds routers
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

use Feedr\Router\Path\SegmentFactory;
use Feedr\Router\Path\Factory as PathFactory;
use Feedr\Router\Route\Factory as RouteFactory;

/**
 * Builds routers
 *
 * @category   Feedr
 * @package    Router
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Factory
{
    public function build()
    {
        $segmentFactory = new SegmentFactory();
        $pathFactory    = new PathFactory($segmentFactory);
        $routeFactory   = new RouteFactory($pathFactory);
        $router         = new Router($routeFactory);

        return $router;
    }
}
