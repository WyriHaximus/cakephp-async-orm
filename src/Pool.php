<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\Configure;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\FlexiblePool;

class Pool
{
    protected $loop;
    protected $pool;

    protected function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pool = new FlexiblePool(new Process('exec php ' . ROOT . '/bin/cake.php WyriHaximus/React/Cake/Orm.worker run -q'), $this->loop, [
            'processOptions' => Configure::read('WyriHaximus.React.Cake.Orm.Line'),
        ]);
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
        return $this->pool->rpc(Factory::rpc('table.call', [
            'function' => $function,
            'table' => $tableName,
            'arguments' => serialize($arguments),
        ]))->then(function ($result) {
            return \React\Promise\resolve($result['result']);
        });
    }
}
