<?php declare(strict_types=1);
/**
 * HTML page template renderer
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Presentation\Template;

use CodeCollab\Template\Html as BaseTemplate;
use CodeCollab\Theme\Loader;
use Minifine\Minifine;
use CodeCollab\I18n\Translator;
use CodeCollab\Authentication\Authentication;
use CodeCollab\CsrfToken\Token;
use CodeCollab\Http\Request\Request;

/**
 * HTML page template renderer
 *
 * @category   Feedr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Html extends BaseTemplate
{
    /**
     * @var \CodeCollab\Theme\Loader Instance of a theme loader
     */
    private $theme;

    /**
     * @var \Minifine\Minifine Instance of a resource minifier
     */
    protected $minifier;

    /**
     * @var \CodeCollab\I18n\Translator Instance of a translator
     */
    private $translator;

    /**
     * @var \CodeCollab\Authentication\Authentication Instance of a user
     */
    protected $user;

    /**
     * @var \CodeCollab\CsrfToken\Token The CSRF token
     */
    protected $csrfToken;

    /**
     * @var \CodeCollab\Http\Request\Request The request object
     */
    protected $request;

    /**
     * Creates instance
     *
     * @param string                                    $basePage   The base (skeleton) page template
     * @param \CodeCollab\Theme\Loader                  $theme      Instance of a theme loader
     * @param \Minifine\Minifine                        $minifier   Instance of a resource minifier
     * @param \CodeCollab\I18n\Translator               $translator Instance of a translator
     * @param \CodeCollab\Authentication\Authentication $user       Instance of an authenticator
     * @param \CodeCollab\CsrfToken\Token               $csrfToken  The CSRF token
     * @param \CodeCollab\Http\Request\Request          $request    The request object
     */
    public function __construct(
        string $basePage,
        Loader $theme,
        Minifine $minifier,
        Translator $translator,
        Authentication $user,
        Token $csrfToken,
        Request $request
    )
    {
        parent::__construct($basePage);

        $this->theme      = $theme;
        $this->minifier   = $minifier;
        $this->translator = $translator;
        $this->user       = $user;
        $this->csrfToken  = $csrfToken;
        $this->request    = $request;
    }

    /**
     * Renders a template
     *
     * @param string $template The template to render
     * @param array  $data     The template variables
     *
     * @return string The rendered template
     */
    public function render(string $template, array $data = []): string
    {
        return parent::render($this->theme->load($template), $data);
    }

    /**
     * Renders a page
     *
     * @param string $template The template to render
     * @param array  $data     The template variables
     *
     * @return string The rendered page
     */
    public function renderPage(string $template, array $data = []): string
    {
        $content = $this->render($template, $data);

        $output = '';

        try {
            ob_start();

            require $this->theme->load($this->basePage);
        } finally {
            $output = ob_get_clean();
        }

        return $output;
    }

    /**
     * Translates a given key
     *
     * @param string $key  The key to translate
     * @param array  $data The data to use to fill in dynamic parts of the translations
     *
     * @return string The translated string
     */
    protected function translate(string $key, array $data = []): string
    {
        return $this->translator->translate($key, $data);
    }
}
