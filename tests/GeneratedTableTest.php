<?php

namespace WyriHaximus\React\Tests\Cake\Orm;

use WyriHaximus\React\Cake\Orm\GeneratedTable;

class GeneratedTableTest extends TestCase
{
    public function testGetters()
    {
        $generatedTable = new GeneratedTable('A', 'B');
        $this->assertSame('A', $generatedTable->getClassName());
        $this->assertSame('B', $generatedTable->getNamespace());
        $this->assertSame('B\A', $generatedTable->getFQCN());
    }
}
