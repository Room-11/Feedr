<?php declare(strict_types=1);
/**
 * JSON field validator
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Form
 * @subpackage Validation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Form\Validation;

use CodeCollab\Form\Validation\Generic;

/**
 * JSON field validator
 *
 * @category   Feedr
 * @package    Form
 * @subpackage Validation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Json extends Generic
{
    /**
     * Validates a form field value
     */
    public function validate(string $value)
    {
        json_decode($value);

        if (!$value || json_last_error() === JSON_ERROR_NONE) {
            return;
        }
        $this->error['json'] = [];
    }
}
