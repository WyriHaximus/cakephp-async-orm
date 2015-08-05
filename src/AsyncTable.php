<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\ORM\TableRegistry;

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
        return $this->callAsync($function, $arguments);
    }

    protected function callSync($function, array $arguments = [])
    {

        return \React\Promise\resolve(
                call_user_func_array(
                [
                    TableRegistry::get($this->tableName),
                    $function
                ],
                $arguments
            )
        );
    }

    protected function callAsync($function, array $arguments = [])
    {
        $unSerialize = function ($input) {
            return unserialize($input);
        };
        return $this->pool->call($this->tableName, $function, $arguments)->then($unSerialize, $unSerialize, $unSerialize);
    }
}
