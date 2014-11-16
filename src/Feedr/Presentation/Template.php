<?php
/**
 * The class is responsible for rendering HTML templates
 *
 * PHP version 5.5
 *
 * @category   Feedr
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Presentation;

/**
 * The class is responsible for rendering HTML templates
 *
 * @category   Feedr
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
abstract class Template implements Renderer
{
    /**
     * @var string The directory where all the templates are stored
     */
    protected $templateDirectory;

    /**
     * @var array List of template variables
     */
    protected $variables = [];

    /**
     * Creates instance
     *
     * @param string                      $templateDirectory The directory where all the templates are stored
     */
    public function __construct($templateDirectory)
    {
        $this->templateDirectory = $templateDirectory;
    }

    /**
     * Magically get template variables, because magic that's why
     *
     * Disclaimer: I am fully aware this kinda sucks and will bite me in the arse
     *             at some point, so don't bother bugging me about this :-)
     *
     * @param mixed The key of which to get the data
     *
     * @return mixed The value which belongs to the key provided
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->variables)) {
            return null;
        }

        return $this->variables[$key];
    }

    /**
     * Magically check whether magic variables exist in a magical way using magic
     *
     * @param mixed The key to check
     *
     * @return boolean true when tha magical thing is set
     */
    public function __isset($key)
    {
        return isset($this->variables[$key]);
    }
}
