<?php declare(strict_types=1);
/**
 * Create feed form
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
use CodeCollab\Form\Field\Text as TextField;
use CodeCollab\Form\Field\Radio as RadioField;
use CodeCollab\Form\Field\Password as PasswordField;
use Feedr\Form\Field\Json as JsonField;
use CodeCollab\Form\Validation\Required as RequiredValidator;
use CodeCollab\Form\Validation\Match as MatchValidator;
use CodeCollab\Form\Validation\Options as OptionsValidator;
use Feedr\Form\validation\Json as JsonValidator;

/**
 * Create feed form
 *
 * @category   Feedr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class CreateFeed extends BaseForm
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

        $this->addField(new TextField('name', [
            new RequiredValidator(),
        ]));

        $this->addField(new RadioField('visibility', [
            new RequiredValidator(),
            new OptionsValidator(['public', 'private']),
        ], 'public'));

        $this->addField(new PasswordField('password'));

        $this->addField(new PasswordField('password2'));

        $this->addField(new JsonField('repositories', [
            new JsonValidator(),
        ]));

        $this->addField(new JsonField('administrators', [
            new JsonValidator(),
        ]));
    }
}
