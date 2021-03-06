<?php
/**
 * Example file for setting up the environment specific configuration
 *
 * Note: you probably don't want to use the configuration in your project
 * directly, but instead you want to:
 * 1) copy this file
 * 2) make the changes to the copy for your specific environment
 * 3) load your specific configuration file in the init.deployment.php file
 *
 * PHP version 5.5
 *
 * @category   Feedr
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr;

/**
 * Setup error reporting
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);

/**
 * Setup timezone
 */
ini_set('date.timezone', 'Europe/Amsterdam');

/**
 * Setup database connection
 */
$dbConnection = new \PDO('pgsql:dbname=feedr;host=127.0.0.1', '', '');
$dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbConnection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

/**
 * Setup GitHub API credentials
 */
$githubCredentials = [
    'key'       => '',
    'secret'    => '',
    'cli_token' => '',
];
