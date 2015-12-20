<?php declare(strict_types=1);
/**
 * Routes definitions of the project
 *
 * This file contains all the (SEO friendly) URLs of the project
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://pieterhordijk.com>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr;

$router
    ->get('/not-found', ['Feedr\Presentation\Controller\Error', 'notFound'])
    ->get('/method-not-allowed', ['Feedr\Presentation\Controller\Error', 'methodNotAllowed'])
;

if (!$user->isLoggedIn()) {
    $router
        ->get('/', ['Feedr\Presentation\Controller\User', 'login'])
        ->post('/', ['Feedr\Presentation\Controller\User', 'doLogin'])
    ;
} else {
    $router
        ->get('/', ['Feedr\Presentation\Controller\Index', 'index'])
        ->post('/logout', ['Feedr\Presentation\Controller\User', 'doLogout'])
    ;
}
