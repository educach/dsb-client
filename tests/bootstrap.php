<?php

/**
 * @file
 * Test bootstrapping file.*
 */

// Get the autoloader.
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Educa\DSB\Client\Tests', __DIR__);

// Define the fixtures directory path.
define('FIXTURES_DIR', __DIR__ . '/fixtures');
