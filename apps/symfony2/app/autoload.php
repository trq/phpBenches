<?php

require_once '../../../vendor/symfony/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

$loader = new ApcUniversalClassLoader('php-framework-benchmark-my.');
$loader->registerNamespaces(array(
    'Symfony'          => array('../../../vendor/symfony/symfony/src', '../../../vendor/symfony/bundles'),
));

$loader->registerNamespaceFallbacks(array(
    __DIR__ . '/../src',
));
$loader->register();
