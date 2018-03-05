<?php

use Cake\Core\Configure;
use Doctrine\Common\Annotations\AnnotationRegistry;

if (!Configure::check('WyriHaximus.React.Cake.Orm.Line')) {
    Configure::write('WyriHaximus.React.Cake.Orm.Line', [
        'lineClass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\SecureLine',
        'lineOptions' => [
            'key' => 'CHANGETHISTOSOMETHINGSAFE!!!!!!9^(%!@#*T!@*&G!*@^&ET',
        ],
    ]);
}

if (!Configure::check('WyriHaximus.React.Cake.Orm.TTL')) {
    Configure::write('WyriHaximus.React.Cake.Orm.TTL', 3);
}

if (!Configure::check('WyriHaximus.React.Cake.Orm.Cache.AsyncTables')) {
    Configure::write('WyriHaximus.React.Cake.Orm.Cache.AsyncTables', CACHE . 'asyncTables' . DS);
}

AnnotationRegistry::registerLoader(function ($class) {
    return class_exists($class);
});
