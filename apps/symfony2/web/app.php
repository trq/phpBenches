<?php

// Include benches pre.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/pre.php';
//

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();

// Include benches post.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/post.php';
//
