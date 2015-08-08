<?php

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

/**
 * @Sync()
 */
class SyncClassNullMethodTable extends Table
{
    public function method()
    {

    }
}
