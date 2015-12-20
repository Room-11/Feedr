<?php declare(strict_types=1);
/**
 * Generates an encryption key
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

use CodeCollab\Encryption\Defuse\Key;

/**
 * Bootstrap project
 */
require_once __DIR__ . '/../bootstrap.php';

/**
 * Generate a new key
 */
$encryptionKey = new Key();

file_put_contents(__DIR__ . '/../encryption.key', $encryptionKey->generate());
