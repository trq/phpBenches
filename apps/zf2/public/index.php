<?php

// Include benches pre.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/pre.php';
//

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

define('ZF_CLASS_CACHE', 'data/cache/classes.php.cache'); if (file_exists(ZF_CLASS_CACHE)) require_once ZF_CLASS_CACHE;

// Setup autoloading
include 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(include 'config/application.config.php')->run();

// Include benches post.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/post.php';
//
