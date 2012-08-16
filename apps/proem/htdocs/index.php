<?php

// Include benches pre.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/pre.php';
//

require_once '../../../vendor/proem/proem/lib/Proem/Autoloader.php';

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

// Include benches post.
include realpath(dirname(__FILE__)) . '/../../../lib/benches/post.php';
//
