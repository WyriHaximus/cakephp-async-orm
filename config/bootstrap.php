<?php

use Cake\Core\Configure;

if (!Configure::check('WyriHaximus.React.Cake.Orm.Line')) {
    Configure::write('WyriHaximus.React.Cake.Orm.Line', [
        'lineClass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\SecureLine',
        'lineOptions' => [
            'key' => 'CHANGETHISTOSOMETHINGSAFE!!!!!!9^(%!@#*T!@*&G!*@^&ET',
        ],
    ]);
}

if (!Configure::check('WyriHaximus.React.Cake.Orm.Process')) {
    Configure::write('WyriHaximus.React.Cake.Orm.Process', 'exec php ' . ROOT . '/bin/cake.php WyriHaximus/React/Cake/Orm.worker run -q');
}
