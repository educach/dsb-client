<?php

/**
 * @file
 * Test bootstrapping file.*
 */

// Get the autoloader.
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Educa\DSB\Client\Tests', __DIR__);
