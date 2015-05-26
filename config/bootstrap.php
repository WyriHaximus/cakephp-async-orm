<?php

use Cake\Core\Configure;

if (!Configure::check('WyriHaximus.React.Cake.Orm.Line')) {
    Configure::write('WyriHaximus.React.Cake.Orm.Line', [
        'lineCLass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\SecureLine',
        'lineOptions' => [
            'key' => 'CHANGETHISTOSOMETHINGSAFE!!!!!!9^(%!@#*T!@*&G!*@^&ET',
        ],
    ]);
}
