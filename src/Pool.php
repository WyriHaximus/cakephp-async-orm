<?php

namespace WyriHaximus\React\Cake\Orm;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\FixedPool;

class Pool
{
    protected $loop;
    protected $pool;

    protected function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pool = new FixedPool(new Process('exec php ' . ROOT . '/bin/cake.php WyriHaximus/React/Cake/Orm.worker run -q'), $this->loop, 3);
    }

    public static function getInstance(LoopInterface $loop = null)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static($loop);
        }

        return $instance;
    }

    public function call($tableName, $function, array $arguments)
    {
        return $this->pool->rpc(new Call('table.call', new Payload([
            'function' => $function,
            'table' => $tableName,
            'arguments' => serialize($arguments),
        ])));
    }
}
