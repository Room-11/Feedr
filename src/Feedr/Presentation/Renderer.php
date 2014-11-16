<?php
/**
 * Interface for presentation renderers
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
 * Interface for presentation renderers
 *
 * @category   Feedr
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Renderer
{
    /**
     * Renders a template
     *
     * @param string $template The template to render
     * @param array  $data     The data to use in the template
     */
    public function render($template, array $data = []);
}
