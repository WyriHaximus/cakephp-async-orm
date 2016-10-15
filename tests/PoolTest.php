<?php

namespace WyriHaximus\React\Tests\Cake\Orm;

use Phake;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\Cake\Orm\Pool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class PoolTest extends TestCase
{
    public function testGetInstance()
    {
        $loop = $this->prophesize(LoopInterface::class);
        $this->assertInstanceOf(Pool::class, Pool::getInstance($loop->reveal()));
    }

    public function testReset()
    {
        $loop = $this->prophesize(LoopInterface::class)->reveal();
        $first = spl_object_hash(Pool::getInstance($loop));
        Pool::reset();
        $second = spl_object_hash(Pool::getInstance($loop));
        $this->assertNotSame($first, $second);
    }

    public function testGetLoop()
    {
        $loop = $this->prophesize(LoopInterface::class)->reveal();
        $this->assertSame($loop, Pool::getInstance($loop)->getLoop());
    }

    public function testGetPool()
    {
        $loop = $this->prophesize(LoopInterface::class)->reveal();
        $this->assertInstanceOf(PoolInterface::class, Pool::getInstance($loop)->getPool());
    }
}
