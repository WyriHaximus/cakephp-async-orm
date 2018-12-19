<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\Configure;
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
 * Class Pool.
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
     * @var bool
     */
    protected static $reset = false;

    /**
     * @param LoopInterface $loop
     * @param array         $config
     */
    protected function __construct(LoopInterface $loop, array $config = [])
    {
        $this->loop = $loop;

        Flexible::createFromClass(
            WorkerChild::class,
            $this->loop,
            $this->applyConfig($config)
        )->then(function (PoolInterface $pool) {
            $this->pool = $pool;
        });
    }

    /**
     * @param  LoopInterface|null $loop
     * @param  array              $config
     * @throws \Exception
     * @return Pool
     */
    public static function getInstance(LoopInterface $loop = null, array $config = [])
    {
        if (null === self::$instance || self::$reset) {
            if (null === $loop) {
                throw new \Exception('Missing event loop');
            }
            self::$instance = new static($loop, $config);
            self::$reset = false;
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$reset = true;
    }

    /**
     * @param $className
     * @param $tableName
     * @param $function
     * @param  array            $arguments
     * @return PromiseInterface
     */
    public function call($className, $tableName, $function, array $arguments)
    {
        if ($this->pool instanceof PoolInterface) {
            return $this->poolCall($className, $tableName, $function, $arguments);
        }

        return $this->waitForPoolCall($className, $tableName, $function, $arguments);
    }

    public function paginate($tableName, $params, $settings)
    {
        if ($this->pool instanceof PoolInterface) {
            return $this->paginateCall($tableName, $params, $settings);
        }

        return $this->waitForPaginateCall($tableName, $params, $settings);
    }

    private function paginateCall($tableName, $params, $settings)
    {
        return $this->pool->rpc(Factory::rpc('paginate', [
            'table' => $tableName,
            'params' => $params,
            'settings' => $settings,
        ]));
    }

    protected function waitForPaginateCall($tableName, $params, $settings)
    {
        $deferred = new Deferred();

        $this->loop->addPeriodicTimer(
            0.1,
            function (TimerInterface $timer) use ($deferred, $tableName, $params, $settings) {
                if ($this->pool instanceof PoolInterface) {
                    $timer->cancel();
                    $deferred->resolve($this->paginateCall($tableName, $params, $settings));
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

    /**
     * @param  array $config
     * @return array
     */
    protected function applyConfig(array $config)
    {
        if (!isset($config['processOptions'])) {
            $config['processOptions'] = Configure::read('WyriHaximus.React.Cake.Orm.Line');
        }

        if (!isset($config[Options::TTL])) {
            $config[Options::TTL] = Configure::read('WyriHaximus.React.Cake.Orm.TTL');
        }

        return $config;
    }

    /**
     * @param $className
     * @param $tableName
     * @param $function
     * @param  array            $arguments
     * @return PromiseInterface
     */
    protected function poolCall($className, $tableName, $function, array $arguments)
    {
        return $this->pool->rpc(Factory::rpc('table.call', [
            'className' => $className,
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
     * @param  array            $arguments
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
}
