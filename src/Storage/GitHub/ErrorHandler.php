<?php declare(strict_types=1);
/**
 * Processes exceptions thrown from the PHPOauthLib
 *
 * Exceptions thrown from the oauth lib are less than useful
 * This class parses and throws more useful exceptions. I blame Lusitanian's mom for this.
 *
 * Here be dragons...
 *
 *                         \
 *       '.                 \.
 *        '.                 "\
 *        ::                  \\
 *        " .                 ".\
 *         ""    ;.   ,        " .
 *         ".~   ."-  .^  .     \ \
 *      -.._" \   \ \  \\  \    "  \
 *        "."\ \._ ) \ ) \.)\-\..\  :
 *          ""\ ",\"_.);-.).) )) "~~).
 *  ~"~~.._    '  -"         ""~.)    "~,
 *   ""~.  ""~~)". "-,           ",."""" "~.
 *       " ..~"," '-'"~~...___.~""  "~.     ~.
 *        ."  ."      _.~~"""".,       "~.   "~~~.~.  _..._
 *      ."    |       '. (  () )";>       ""~.      "(.___.)..
 *     /      "       ..""~~~~""_.~  ....._.  "~.             ""~.
 *    "     ___\~-      """"""""    "       ""~~.""":==>..        "~.
 *  ."          \_~               .              "~((####)) ..       ".
 * |       _.-"", /          ..~"                  ""~~~"    ""~~~~~  :>
 *               /".                       .~"~~..___............~;>~""
 *            .~"  "~.         "-~~....--""__________,,....~~~~"""
 *                    "--""~~..._____,..~~"
 *                   ."
 *                   |
 *                   ;
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage\GitHub;

use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Exception\TokenResponseException;

/**
 * Processes exceptions thrown from the PHPOauthLib
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class ErrorHandler
{
    /**
     * Rethrows saner oAuth exceptions when a match is found
     *
     * @param \OAuth\Common\Exception\Exception $exception The original oAuth exception
     */
    public function handle(Exception $exception)
    {
        switch (get_class($exception)) {
            case 'OAuth\Common\Http\Exception\TokenResponseException':
                $this->handleTokenResponseException($exception);
                break;
        }

        throw $exception;
    }

    /**
     * Handles TokenResponse exceptions
     *
     * @param \OAuth\Common\Http\Exception\TokenResponseException $exception The original oAuth exception
     *
     * @throws \Feedr\Storage\GitHub\NptFoundException When trying to retrieve a resource which does not exist
     */
    private function handleTokenResponseException(TokenResponseException $exception)
    {
        if (preg_match('/failed to open stream: HTTP request failed! HTTP\/1\.1 404 Not Found/', $exception->getMessage()) === 1) {
            throw new NotFoundException($exception->getMessage(), $exception->getCode(), $exception);
        }

        throw $exception;
    }
}
