<?php declare(strict_types=1);
/**
 * Logout form
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://pieterhordijk.com>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Form;

use CodeCollab\Form\BaseForm;
use CodeCollab\Form\Field\Csrf as CsrfField;
use CodeCollab\Form\Validation\Required as RequiredValidator;
use CodeCollab\Form\Validation\Match as MatchValidator;

/**
 * Logout form
 *
 * @category   Feedr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Logout extends BaseForm
{
    /**
     * Sets up the fields of the form
     */
    protected function setupFields()
    {
        $this->addField(new CsrfField('csrfToken', [
            new RequiredValidator(),
            new MatchValidator(base64_encode($this->csrfToken->get())),
        ], base64_encode($this->csrfToken->get())));
    }
}
