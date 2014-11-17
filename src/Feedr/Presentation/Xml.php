<?php
/**
 * The class is responsible for rendering XML templates
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
 * The class is responsible for rendering XML templates
 *
 * @category   Feedr
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class XML extends Template
{
    /**
     * Creates instance
     *
     * @param string $templateDirectory The directory where all the templates are stored
     */
    public function __construct($templateDirectory)
    {
        parent::__construct($templateDirectory);
    }

    /**
     * Renders a template
     *
     * @param string $template The template to render
     * @param array  $data     The data to use in the template
     */
    public function render($template, array $data = [])
    {
        $this->variables = $data;

        ob_start();
        require $this->templateDirectory . '/' . $template;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
