<?php

if (isset($_GET['debug'])) {
    define('START_TIME', microtime(true));
    define('START_MEMORY_USAGE', memory_get_usage());
}

require_once '../../../vendor/proem/proem/lib/Proem/Autoloader.php';

(new Proem\Autoloader())
    ->attachNamespace('Proem', '../../../vendor/proem/proem/lib')
    ->attachNamespace('Module', '../lib')
    ->register();

(new Proem\Proem)
    ->attachEventListener('proem.pre.in.router', function() {
            $asset = new Proem\Service\Asset\Standard;
            return $asset->set('Proem\Routing\Router\Template', $asset->single(function() {
                return (new Proem\Routing\Router\Standard(new Proem\IO\Request\Http\Standard))
                    ->attach(
                        '/',
                        new Proem\Routing\Route\StaticRoute([
                            'rule'    => '/',
                            'targets' => [
                                'module'     => 'index',
                                'controller' => 'index',
                                'action'     => 'index'
                            ]
                        ])
                    );
            }));
        })
    	->init();

$xhprof_data = xhprof_disable();

if (!isset($_GET['debug'])) {
    die();
}

echo "Page rendered in <b>"
    . round((microtime(true) - START_TIME), 5) * 1000 ." ms</b>, taking <b>"
    . round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2) ." KB</b>";
$f = get_included_files();
echo ", include files: ".count($f);
