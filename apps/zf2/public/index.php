<?php

require_once '../../../vendor/proem/proem/lib/Proem/Autoloader.php';

(new Proem\Autoloader())
    ->attachNamespace('Xhprof', realpath(__DIR__) . '/../../../lib')
    ->attachNamespace('Benches', realpath(__DIR__) . '/../../../lib')
    ->register();

$profiler = new Benches\Profiler(isset($_GET['debug']));
$profiler->pre();

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

$profiler->post();
