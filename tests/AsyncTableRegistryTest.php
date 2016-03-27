<?php

namespace WyriHaximus\React\Tests\Cake\Orm;

use Phake;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\Cake\Orm\AsyncTable;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;
use WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables\AsyncClassAsyncMethodTable;

class AsyncTableRegistryTest extends TestCase
{
    public function testGetInstance()
    {
        $this->assertInstanceOf(AsyncTableRegistry::class, AsyncTableRegistry::getInstance());
    }

    public function testReset()
    {
        $first = spl_object_hash(AsyncTableRegistry::getInstance());
        $second = spl_object_hash(AsyncTableRegistry::getInstance());
        $this->assertSame($first, $second   );
    }

    public function testGet()
    {
        $loop = Phake::mock(LoopInterface::class);
        AsyncTableRegistry::init($loop);
        $table = AsyncTableRegistry::get(AsyncClassAsyncMethodTable::class);
        $this->assertInstanceOf(AsyncTable::class, $table);
        $this->assertSame($table, AsyncTableRegistry::get(AsyncClassAsyncMethodTable::class));
    }

    public function testInfo()
    {
        $loop = Phake::mock(LoopInterface::class);
        AsyncTableRegistry::init($loop);
        $this->assertInternalType('array', AsyncTableRegistry::getInstance()->info());
    }
}
