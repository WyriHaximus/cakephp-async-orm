<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\Configure;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;
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
     * @var Pool
     */
    protected static $instance = null;

    /**
     * @var boolean
     */
    protected static $reset = false;

    /**
     * @param LoopInterface $loop
     */
    protected function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        Flexible::create(
            new Process(
                Configure::read('WyriHaximus.React.Cake.Orm.Process')
            ),
            $this->loop,
            [
                'processOptions' => Configure::read('WyriHaximus.React.Cake.Orm.Line'),
                Options::TTL => Configure::read('WyriHaximus.React.Cake.Orm.TTL'),
            ]
        )->then(function (PoolInterface $pool) {
            $this->pool = $pool;
        });
    }

    /**
     * @param LoopInterface $loop
     * @return Pool
     * @throws \Exception
     */
    public static function getInstance(LoopInterface $loop = null)
    {
        if (null === self::$instance || self::$reset) {
            if (null === $loop) {
                throw new \Exception('Missing event loop');
            }
            self::$instance = new static($loop);
            self::$reset = false;
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$reset = true;
    }

    /**
     * @param $tableName
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    public function call($tableName, $function, array $arguments)
    {
        if ($this->pool instanceof PoolInterface) {
            return $this->poolCall($tableName, $function, $arguments);
        }

        return $this->waitForPoolCall($tableName, $function, $arguments);
    }

    /**
     * @param $tableName
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    protected function poolCall($tableName, $function, array $arguments)
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
     * @param $tableName
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    protected function waitForPoolCall($tableName, $function, array $arguments)
    {
        $deferred = new Deferred();

        $this->loop->addPeriodicTimer(
            0.1,
            function (TimerInterface $timer) use ($deferred, $tableName, $function, $arguments) {
                if ($this->pool instanceof PoolInterface) {
                    $timer->cancel();
                    $deferred->resolve($this->call($tableName, $function, $arguments));
                }
            }
        );

        return $deferred->promise();
    }

    /**
     * @inheritDoc
     */
    public function info()
    {
        if ($this->pool instanceof PoolInterface) {
            return $this->pool->info();
        }

        return [];
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return PoolInfoInterface
     */
    public function getPool()
    {
        return $this->pool;
    }
}
