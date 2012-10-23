<?php

require_once '../../../vendor/proem/proem/lib/Proem/Autoloader.php';

(new Proem\Autoloader())
    ->attachNamespace('Xhprof', realpath(__DIR__) . '/../../../lib')
    ->attachNamespace('Benches', realpath(__DIR__) . '/../../../lib')
    ->register();

$profiler = new Benches\Profiler(isset($_GET['debug']));
$profiler->pre();

(new Proem\Autoloader())
    ->attachNamespace('Proem', '../../../vendor/proem/proem/lib')
    ->attachNamespace('Module', '../lib')
    ->register();

(new Proem\Proem)->attachEventListener('proem.pre.in.router', function() {
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
})->init();

$profiler->post();
