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
     * @param LoopInterface $loop
     */
    public static function init(LoopInterface $loop)
    {
        Pool::getInstance($loop);
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
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    public static function reset()
    {
        static $instance = null;
    }

    /**
     * @inheritDoc
     */
    public function info()
    {
        return Pool::getInstance()->info();
    }
}
