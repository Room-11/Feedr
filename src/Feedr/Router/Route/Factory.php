<?php
/**
 * Factory for creating routes
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Route
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Router\Route;

use Feedr\Router\Path\Builder as PathBuilder;

/**
 * Factory for creating routes
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Route
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Factory implements Builder
{
    /**
     * @var \Feedr\Router\Path\Builder Instance of the path factory
     */
    private $pathFactory;

    /**
     * Creates instance
     *
     * @param \Feedr\Router\Path\Builder $pathFactory Instance of a path factory
     */
    public function __construct(PathBuilder $pathFactory)
    {
        $this->pathFactory = $pathFactory;
    }

    /**
     * Creates new instance of a route
     *
     * @param string   $name     The identifier for the route
     * @param string   $rawPath  The raw path of the route
     * @param callable $callback The callback that is run when the route is called
     *
     * @return \Feedr\Router\Route\AccessPoint The built route
     */
    public function build($name, $rawPath, callable $callback)
    {
        return new Route($name, $this->pathFactory->build($rawPath), $callback);
    }
}
