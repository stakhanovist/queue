<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('vendor/autoload.php not found. Run a composer install.');
}

error_reporting(E_ALL | E_STRICT);

$phpUnitVersion = PHPUnit_Runner_Version::id();
if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.5.0', '<')) {
    echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported by Stakhanovist Queue library' . PHP_EOL;
    exit(1);
}
unset($phpUnitVersion);

/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable(__DIR__ . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}
