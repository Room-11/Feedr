<?php
/**
 * This class represents a single route
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

use Feedr\Router\Path\Parser;
use Feedr\Network\Http\RequestData;
use Feedr\Router\Path\Segment;

/**
 * This class represents a single route
 *
 * @category   Feedr
 * @package    Router
 * @subpackage Route
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Route implements AccessPoint
{
    /**
     * @var string The name of this route
     */
    private $name;

    /**
     * @var \Feedr\Router\Path\Parser The path of the route
     */
    private $path;

    /**
     * @var callable The callback
     */
    private $callback;

    /**
     * @var array The (optional) requirements of path variables in the route
     */
    private $requirements = [];

    /**
     * @var array The (optional) mapping of path variable in the route
     */
    private $defaults = [];

    /**
     * @var array List of the path variables of the route
     */
    private $variables = [];

    /**
     * Creates the instance of the route
     *
     * @param string                        $name     The name of the route
     * @param \Feedr\Router\Path\Parser $path     The path of the route
     * @param callable                      $callback The callback of the route
     */
    public function __construct($name, Parser $path, callable $callback)
    {
        $this->name     = $name;
        $this->path     = $path;
        $this->callback = $callback;
    }

    /**
     * Gets the name of the route
     *
     * @return string The name of the route
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets a path variable
     *
     * @param string $name The name of the variable to get
     *
     * @return string                                                  The value of the variable
     * @throws \Feedr\Router\Route\UndefinedPathVariableException When trying to access an undefined variable
     */
    public function getVariable($name)
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new UndefinedPathVariableException('Undefined pat variable `' . $name . '`.');
        }

        return $this->variables[$name];
    }

    /**
     * Gets the callback of the route
     *
     * @return callable The callback of the route
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Adds a regex patterns as requirements for path variables
     *
     * @param array $requirements The regex patterns
     *
     * @return \Feedr\Router\Route\AccessPoint Instance of self
     */
    public function wherePattern(array $requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    /**
     * Adds default values for path variables
     *
     * @param array $defaults The defaults
     *
     * @return \Feedr\Router\Route\AccessPoint Instance of self
     */
    public function defaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Tries to match the current route against the request
     *
     * @param \Feedr\Network\Http\RequestData $request The request data
     *
     * @return boolean True when the route matches the request
     */
    public function matchesRequest(RequestData $request)
    {
        $pathParts = explode('/', trim($request->getPath(), '/'));

        if (!$this->doesMatch($this->path->getParts(), $pathParts)) {
            return false;
        }

        $this->processVariables($this->path->getParts(), $pathParts);

        return true;
    }

    /**
     * Checks whether the request matches the route
     *
     * @param \Feedr\Router\Path\Segment[] $segments     The segments
     * @param array                        $requestParts The request parts
     *
     * @return boolean True when the request matched the route
     */
    private function doesMatch(array $segments, array $requestParts)
    {
        if (count($requestParts) > count($segments)) {
            return false;
        }

        foreach ($segments as $index => $segment) {
            if (!$segment->isVariable() && !$this->doesStaticSegmentMatch($segment, $requestParts, $index)) {
                return false;
            } else if (!$segment->isOptional() && !$this->isRequiredSegmentSet($segment, $requestParts, $index)) {
                return false;
            } else if ($segment->isVariable() && !$this->areRequirementsMet($segment, $requestParts, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks whether the static segment matches
     *
     * @param \Feedr\Router\Path\Segment $segment      The segment
     * @param array                      $requestParts The request parts
     * @param int                        $index        The current index
     *
     * @return boolean True when the static part matches
     */
    private function doesStaticSegmentMatch(Segment $segment, array $requestParts, $index)
    {
        return isset($requestParts[$index]) && $segment->getValue() === $requestParts[$index];
    }

    /**
     * Checks whether the required segment matches
     *
     * @param \Feedr\Router\Path\Segment $segment      The segment
     * @param array                      $requestParts The request parts
     * @param int                        $index        The current index
     *
     * @return boolean True when the required segment matches
     */
    private function isRequiredSegmentSet(Segment $segment, array $requestParts, $index)
    {
        return !empty($requestParts[$index]) || array_key_exists($segment->getValue(), $this->defaults);
    }

    /**
     * Checks whether the requirements for the segment are met
     *
     * @param \Feedr\Router\Path\Segment $segment      The segment
     * @param array                      $requestParts The request parts
     * @param int                        $index        The current index
     *
     * @return boolean True when the requirements match
     */
    private function areRequirementsMet(Segment $segment, array $requestParts, $index)
    {
        if (!array_key_exists($segment->getValue(), $this->requirements)) {
            return true;
        }

        return preg_match('/^' . $this->requirements[$segment->getValue()] . '$/', $requestParts[$index]) === 1;
    }

    /**
     * Processes the variables in the URI path
     *
     * @param \Feedr\Router\Path\Segment[] $segments     The segments
     * @param array                        $requestParts The request parts
     */
    private function processVariables(array $segments, array $requestParts)
    {
        foreach ($segments as $index => $pathPart) {
            if (!$pathPart->isVariable()) {
                continue;
            }

            $this->variables[$pathPart->getValue()] = $this->processVariable($pathPart, $requestParts, $index);
        }
    }

    /**
     * Processes a single URI path variable
     *
     * @param \Feedr\Router\Path\Segment $segment      The segment
     * @param array                      $requestParts The request parts
     * @param int                        $index        The current index
     *
     * @return boolean True when the static part matches
     */
    private function processVariable(Segment $segment, array $requestParts, $index)
    {
        if (isset($requestParts[$index])) {
            return $requestParts[$index];
        } else if (array_key_exists($segment->getValue(), $this->defaults)) {
            return $this->defaults[$segment->getValue()];
        }

        return false;
    }
}
