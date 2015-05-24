<?php

namespace WyriHaximus\React\Cake\Orm;

use React\EventLoop\LoopInterface;

class AsyncTableRegistry
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

        static::$tables[$tableName] = new AsyncTable(Pool::getInstance(), $tableName);
        return static::$tables[$tableName];
    }
}
