<?php

namespace WyriHaximus\React\Cake\Orm;

class AsyncTable
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param Pool $pool
     * @param string $tableName
     */
    public function __construct(Pool $pool, $tableName)
    {
        $this->pool = $pool;
        $this->tableName = $tableName;
    }

    public function __call($function, array $arguments = [])
    {
        $unSerialize = function ($input) {
            return unserialize($input);
        };
        return $this->pool->call($this->tableName, $function, $arguments)->then($unSerialize, $unSerialize, $unSerialize);
    }
}
