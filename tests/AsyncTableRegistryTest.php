<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;
use WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables\AsyncClassAsyncMethodTable;

class AsyncTableRegistryTest extends TestCase
{
    public function testGetInstance()
    {
        $this->assertInstanceOf(AsyncTableRegistry::class, AsyncTableRegistry::getInstance());
    }

    public function testSingleton()
    {
        $first = spl_object_hash(AsyncTableRegistry::getInstance());
        $second = spl_object_hash(AsyncTableRegistry::getInstance());
        $this->assertSame($first, $second);
    }

    public function testReset()
    {
        $first = spl_object_hash(AsyncTableRegistry::getInstance());
        AsyncTableRegistry::reset();
        $second = spl_object_hash(AsyncTableRegistry::getInstance());
        $this->assertNotSame($first, $second);
    }

    public function testGet()
    {
        $loop = $this->prophesize(LoopInterface::class)->reveal();
        AsyncTableRegistry::init($loop);
        $table = AsyncTableRegistry::get(AsyncClassAsyncMethodTable::class);
        $this->assertInstanceOf(TestTable::class, $table);
        $this->assertSame($table, AsyncTableRegistry::get(AsyncClassAsyncMethodTable::class));
    }

    public function testInfo()
    {
        $loop = $this->prophesize(LoopInterface::class)->reveal();
        AsyncTableRegistry::init($loop);
        $this->assertInternalType('array', AsyncTableRegistry::getInstance()->info());
    }
}
