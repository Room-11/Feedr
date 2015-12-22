<?php return array (
  0 => 
  array (
    'GET' => 
    array (
      '/not-found' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Error',
        1 => 'notFound',
      ),
      '/method-not-allowed' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Error',
        1 => 'methodNotAllowed',
      ),
      '/' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Index',
        1 => 'index',
      ),
      '/feeds/create' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Feed',
        1 => 'create',
      ),
    ),
    'POST' => 
    array (
      '/logout' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Authentication',
        1 => 'doLogout',
      ),
    ),
  ),
  1 => 
  array (
  ),
);