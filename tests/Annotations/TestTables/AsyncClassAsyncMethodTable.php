<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;
use WyriHaximus\React\Cake\Orm\Annotations\Async;

/**
 * @Async()
 */
class AsyncClassAsyncMethodTable extends Table
{
    /**
     * @Async()
     */
    public function method()
    {
    }
}
