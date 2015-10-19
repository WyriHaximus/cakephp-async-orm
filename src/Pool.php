<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\Configure;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\FlexiblePool;
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
     * @var FlexiblePool
     */
    protected $pool;

    /**
     * @param LoopInterface $loop
     */
    protected function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pool = new FlexiblePool(
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
        return $this->pool->rpc(Factory::rpc('table.call', [
            'function' => $function,
            'table' => $tableName,
            'arguments' => serialize($arguments),
        ]))->then(function ($result) {
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
