<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

interface AsyncTableInterface
{
    /**
     * @param Pool   $pool
     * @param string $tableName
     * @param string $tableClass
     */
    public function setUpAsyncTable(Pool $pool, $tableName, $tableClass);
}
