<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables;

use Cake\ORM\Table;

class NullClassNullMethodTable extends Table
{
    public function method()
    {
    }

    public function findSomething()
    {
    }

    public function getSomething()
    {
    }

    public function fetchSomething()
    {
    }

    /**
     * @return \Cake\ORM\Query The query builder
     */
    public function fooBar()
    {
    }

    /**
     * @return Cake\ORM\Query The query builder
     */
    public function barFoo()
    {
    }
}
