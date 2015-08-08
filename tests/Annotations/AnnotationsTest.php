<?php

namespace WyriHaximus\React\Tests\Cake\Orm\Annotations;

use WyriHaximus\React\Cake\Orm\AsyncTable;
use WyriHaximus\React\Cake\Orm\Pool;

class AnnotationsTest extends \PHPUnit_Framework_TestCase
{
    const METHOD = 'method';

    protected static function method()
    {
        return static::METHOD;
    }

    protected static function arguments()
    {
        return [];
    }

    public function getAsyncTableMock($tableName, $method = self::METHOD)
    {
        $table = \Phake::partialMock(
            AsyncTable::class,
            \Phake::mock(Pool::class),
            $tableName,
            'WyriHaximus\React\Tests\Cake\Orm\Annotations\TestTables\\' . $tableName
        );

        \Phake::when($table)->callSync($method, static::arguments())->thenReturn(true);
        \Phake::when($table)->callAsync($method, static::arguments())->thenReturn(true);

        return $table;
    }

    public function providerMethod()
    {
        yield ['AsyncClassAsyncMethodTable', 'callAsync'];
        yield ['AsyncClassNullMethodTable', 'callAsync'];
        yield ['AsyncClassSyncMethodTable', 'callSync'];
        yield ['NullClassAsyncMethodTable', 'callAsync'];
        yield ['NullClassNullMethodTable', 'callSync'];
        yield ['NullClassSyncMethodTable', 'callSync'];
        yield ['SyncClassAsyncMethodTable', 'callAsync'];
        yield ['SyncClassNullMethodTable', 'callSync'];
        yield ['SyncClassSyncMethodTable', 'callSync'];
    }

    /**
     * @dataProvider providerMethod
     */
    public function testAnnotations($tableName, $method)
    {
        $table = $this->getAsyncTableMock($tableName);

        $this->assertTrue(
            call_user_func_array(
                [
                    $table,
                    static::method()
                ],
                static::arguments()
            )
        );

        \Phake::verify($table)->$method(static::method(), static::arguments());
    }

    public function providerOtherBlocks()
    {
        yield ['findSomething', 'callAsync'];
        yield ['getSomething', 'callSync'];
        yield ['fetchSomething', 'callAsync'];
        yield ['foobar', 'callAsync'];
        yield ['barFoo', 'callAsync'];
    }

    /**
     * @dataProvider providerOtherBlocks
     */
    public function testOtherBlocks($method, $function)
    {
        $table = $this->getAsyncTableMock('NullClassNullMethodTable', $method);

        $this->assertTrue(
            call_user_func_array(
                [
                    $table,
                    $method
                ],
                static::arguments()
            )
        );

        \Phake::verify($table)->$function($method, static::arguments());
    }
}
