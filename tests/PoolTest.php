<?php

namespace WyriHaximus\React\Tests\Cake\Orm;

use Phake;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\Cake\Orm\AsyncTable;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;
use WyriHaximus\React\Cake\Orm\Pool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables\AsyncClassAsyncMethodTable;

class PoolTest extends TestCase
{
    public function testGetInstance()
    {
        $loop = Phake::mock(LoopInterface::class);
        $this->assertInstanceOf(Pool::class, Pool::getInstance($loop));
    }

    public function testReset()
    {
        $loop = Phake::mock(LoopInterface::class);
        $first = spl_object_hash(Pool::getInstance($loop));
        Pool::reset();
        $second = spl_object_hash(Pool::getInstance($loop));
        $this->assertNotSame($first, $second);
    }

    public function testGetLoop()
    {
        $loop = Phake::mock(LoopInterface::class);
        $this->assertSame($loop, Pool::getInstance($loop)->getLoop());
    }

    public function testGetPool()
    {
        $loop = Phake::mock(LoopInterface::class);
        $this->assertInstanceOf(PoolInterface::class, Pool::getInstance($loop)->getPool());
    }
}
