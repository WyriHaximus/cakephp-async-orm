<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

/**
 * @Sync()
 */
class SyncClassSyncMethodTable extends Table
{
    /**
     * @Sync()
     */
    public function method()
    {
    }
}
