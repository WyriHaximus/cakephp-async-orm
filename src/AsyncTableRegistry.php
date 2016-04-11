<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\App;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolUtilizerInterface;

class AsyncTableRegistry implements PoolUtilizerInterface
{
    /**
     * @var AsyncTable[]
     */
    protected static $tables = [];

    /**
     * @var AsyncTableRegistry
     */
    protected static $instance = null;

    /**
     * @var boolean
     */
    protected static $reset = false;

    /**
     * @param LoopInterface $loop
     * @param array $config
     */
    public static function init(LoopInterface $loop, array $config = [])
    {
        Pool::getInstance($loop, $config);
    }

    /**
     * @param $tableName
     *
     * @return AsyncTable
     */
    public static function get($tableName)
    {
        if (isset(static::$tables[$tableName])) {
            return static::$tables[$tableName];
        }

        static::$tables[$tableName] = new AsyncTable(
            Pool::getInstance(),
            $tableName,
            App::className($tableName, 'Model/Table', 'Table')
        );
        return static::$tables[$tableName];
    }

    public static function getInstance()
    {
        if (null === self::$instance || self::$reset) {
            self::$instance = new static();
            self::$reset = false;
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$reset = true;
    }

    /**
     * @inheritDoc
     */
    public function info()
    {
        return Pool::getInstance()->info();
    }
}
