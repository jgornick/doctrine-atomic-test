<?php

if (!file_exists($file = __DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require_once $file;

$loader->add('Doctrine\ODM\MongoDB\Tests', __DIR__ . '/../vendor/doctrine/mongodb-odm/tests');
$loader->add('Documents', __DIR__);
$loader->add('Documents', __DIR__ . '/../vendor/doctrine/mongodb-odm/tests');

\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();