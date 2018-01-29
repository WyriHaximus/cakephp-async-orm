<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Async;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

/**
 * @Async()
 */
class AsyncClassSyncMethodTable extends Table
{
    /**
     * @Sync()
     */
    public function method()
    {
    }
}
