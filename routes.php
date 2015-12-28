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
        ->get('/', ['Feedr\Presentation\Controller\Authentication', 'login'])
        ->get('/login', ['Feedr\Presentation\Controller\Authentication', 'doLogin'])
    ;
} else {
    $router
        ->get('/', ['Feedr\Presentation\Controller\Index', 'index'])
        ->post('/logout', ['Feedr\Presentation\Controller\Authentication', 'doLogout'])
        ->get('/feeds/create', ['Feedr\Presentation\Controller\Feed', 'create'])
        ->get('/repositories/search', ['Feedr\Presentation\Controller\Repository', 'search'])
        ->get('/repositories/add', ['Feedr\Presentation\Controller\Repository', 'addRow'])
        ->get('/administrators/search', ['Feedr\Presentation\Controller\Administrator', 'search'])
        ->get('/administrators/add', ['Feedr\Presentation\Controller\Administrator', 'addRow'])
        ->get('/feeds/preview', ['Feedr\Presentation\Controller\Feed', 'preview'])
    ;
}
