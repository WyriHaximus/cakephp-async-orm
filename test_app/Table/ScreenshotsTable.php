<?php

namespace WyriHaximus\React\TestApp\Cake\Orm\Table;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Async;

/**
 * @Async
 */
class ScreenshotsTable extends Table
{
    public function fetchNewest()
    {
        return $this->find()->orderDesc('id')->firstOrFail();
    }

    public function getByMd5($md5)
    {
        return $this->findByMd5($md5);
    }

    public function getByEntity($md5)
    {
        return $this->findByMd5($md5);
    }
}
