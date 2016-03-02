<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\Configure;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\Pool\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInfoInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolUtilizerInterface;

/**
 * Class Pool
 * @package WyriHaximus\React\Cake\Orm
 */
class Pool implements PoolUtilizerInterface
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var PoolInfoInterface
     */
    protected $pool;

    /**
     * @param LoopInterface $loop
     */
    protected function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pool = Flexible::create(
            new Process(
                Configure::read('WyriHaximus.React.Cake.Orm.Process')
            ),
            $this->loop,
            [
                'processOptions' => Configure::read('WyriHaximus.React.Cake.Orm.Line'),
            ]
        );
    }

    /**
     * @param LoopInterface $loop
     * @return Pool
     * @throws \Exception
     */
    public static function getInstance(LoopInterface $loop = null)
    {
        static $instance = null;
        if (null === $instance) {
            if (null === $loop) {
                throw new \Exception('Missing event loop');
            }
            $instance = new static($loop);
        }

        return $instance;
    }

    /**
     * @param $tableName
     * @param $function
     * @param array $arguments
     * @return \React\Promise\PromiseInterface
     */
    public function call($tableName, $function, array $arguments)
    {
        return $this->pool->then(function (PoolInterface $pool) use ($tableName, $function, $arguments) {
            return $pool->rpc(Factory::rpc('table.call', [
                'function' => $function,
                'table' => $tableName,
                'arguments' => serialize($arguments),
            ]));
        })->then(function ($result) {
            return \React\Promise\resolve($result['result']);
        });
    }

    /**
     * @inheritDoc
     */
    public function info()
    {
        return $this->pool->info();
    }
}
