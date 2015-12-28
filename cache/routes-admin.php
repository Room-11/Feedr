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
      '/repositories/search' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Repository',
        1 => 'search',
      ),
      '/repositories/add' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Repository',
        1 => 'addRow',
      ),
      '/administrators/search' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Administrator',
        1 => 'search',
      ),
      '/administrators/add' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Administrator',
        1 => 'addRow',
      ),
      '/feeds/preview' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Feed',
        1 => 'preview',
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