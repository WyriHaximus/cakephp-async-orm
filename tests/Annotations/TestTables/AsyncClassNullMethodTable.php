<?php

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Async;

/**
 * @Async()
 */
class AsyncClassNullMethodTable extends Table
{
    public function method()
    {

    }
}
