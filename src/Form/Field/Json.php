<?php declare(strict_types=1);
/**
 * JSON field
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Form
 * @subpackage Field
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Form\Field;

use CodeCollab\Form\Field\Generic;

/**
 * JSON field
 *
 * @category   Feedr
 * @package    Form
 * @subpackage Field
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Json extends Generic
{
    /**
     * Creates instance
     *
     * @param string                                  $name            The name of the field
     * @param \CodeCollab\Form\Validation\Validator[] $validationRules The validation rules
     * @param string                                  $defaultValue    The default value
     */
    public function __construct(string $name, array $validationRules = [], $defaultValue = null)
    {
        parent::__construct($name, 'json', $validationRules, $defaultValue);
    }
}
