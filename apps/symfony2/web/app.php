<?php
if (isset($_GET['debug'])) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    define('START_TIME', microtime(true));
    define('START_MEMORY_USAGE', memory_get_usage());
}

require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->handle(Request::createFromGlobals())->send();

$xhprof_data = xhprof_disable();

if (!isset($_GET['debug'])) {
    die();
}

echo "Page rendered in <b>"
    . round((microtime(true) - START_TIME), 5) * 1000 ." ms</b>, taking <b>"
    . round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2) ." KB</b>";
$f = get_included_files();
echo ", include files: ".count($f);


$xhprof = realpath(dirname(__FILE__)) . '/../../../lib/xhprof/xhprof_lib/utils';
include_once $xhprof . "/xhprof_lib.php";
include_once $xhprof . "/xhprof_runs.php";

// save raw data for this profiler run using default
// implementation of iXHProfRuns.
$xhprof_runs = new XHProfRuns_Default();

// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");

echo ", xhprof <a href=\"http://xhprof.bench/xhprof_html/index.php?run=$run_id&source=xhprof_foo\">url</a>";
