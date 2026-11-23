<?php

declare(strict_types=1);

/**
 * Bootstrap of the integration suite: loads a real WordPress, once, before the first test.
 *
 * Copy it to tests/Integration/bootstrap.php (the phpunit.integration.xml next to it points there; the paths below
 * assume that location). The `WordPress integration` job of the harness workflow installs WordPress and exports WP_PATH (the directory that holds
 * wp-load.php); locally, install WordPress anywhere and export WP_PATH, or pass it on the command line:
 *
 *     WP_PATH=/path/to/wordpress vendor/bin/pest -c phpunit.integration.xml
 *
 * Composer's autoloader is loaded first, by this file, so that the classes under test and Pest itself are available.
 * WordPress is then loaded with `wp-load.php`: the site is installed, its plugins are active, its hooks fire.
 */
$autoload = dirname(__DIR__, 2).'/vendor/autoload.php';

if (! is_file($autoload)) {
    fwrite(STDERR, "Composer's autoloader is missing: run composer install.\n");
    exit(1);
}

require_once $autoload;

$wordpressPath = getenv('WP_PATH') ?: (getenv('WP_CORE_DIR') ?: '');

if ($wordpressPath === '' || ! is_file($wordpressPath.'/wp-load.php')) {
    fwrite(STDERR, "WordPress not found: set WP_PATH to the directory that holds wp-load.php (got \"{$wordpressPath}\").\n");
    exit(1);
}

// No theme output: a test starts what it needs itself. The cron is turned off by the wp-config.php of the site
// (the harness job writes it there): defining it again here would raise a "constant already defined" warning.
define('WP_USE_THEMES', false);

require_once $wordpressPath.'/wp-load.php';

unset($autoload, $wordpressPath);
