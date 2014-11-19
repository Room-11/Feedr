<?php
/**
 * The class is responsible for preparing data for URLs
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
 * The class is responsible for preparing data for URLs
 *
 * @category   Feedr
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Url
{
    public function slugify($text)
    {
        // replace characters which can't be normally replaced
        $search = ['ᴰ', 'ᴶ', '®', '©', '☼', '☮', 'Ξ', '∆'];
        $replace = ['D', 'J', 'r', 'c', 'sun', 'o', 'e', 'a'];
        $text = str_replace($search, $replace, $text);

        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');

        if (function_exists('iconv')) {
            $text = trim(iconv('utf-8', 'us-ascii//IGNORE//TRANSLIT', $text), '-');
        }

        $text = strtolower($text);

        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
