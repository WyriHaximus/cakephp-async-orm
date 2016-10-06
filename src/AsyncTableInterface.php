<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use React\Promise\PromiseInterface;
use WyriHaximus\React\Cake\Orm\Annotations\Async;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

interface AsyncTableInterface
{
    /**
     * @param Pool $pool
     * @param string $tableName
     * @param string $tableClass
     * @return void
     */
    public function setUpAsyncTable(Pool $pool, $tableName, $tableClass);
}
