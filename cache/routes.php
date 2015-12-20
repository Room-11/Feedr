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
        0 => 'Feedr\\Presentation\\Controller\\Authentication',
        1 => 'login',
      ),
    ),
    'POST' => 
    array (
      '/' => 
      array (
        0 => 'Feedr\\Presentation\\Controller\\Authentication',
        1 => 'doLogin',
      ),
    ),
  ),
  1 => 
  array (
  ),
);