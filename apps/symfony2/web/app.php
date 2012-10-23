<?php

require_once '../../../vendor/proem/proem/lib/Proem/Autoloader.php';

(new Proem\Autoloader())
    ->attachNamespace('Xhprof', realpath(__DIR__) . '/../../../lib')
    ->attachNamespace('Benches', realpath(__DIR__) . '/../../../lib')
    ->register();

$profiler = new Benches\Profiler(isset($_GET['debug']));
$profiler->pre();

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();

$profiler->post();
