<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm;

use WyriHaximus\React\Cake\Orm\GeneratedTable;

class GeneratedTableTest extends TestCase
{
    public function testGetters()
    {
        $generatedTable = new GeneratedTable('A', 'B');
        $this->assertSame('A', $generatedTable->getNamespace());
        $this->assertSame('B', $generatedTable->getClassName());
        $this->assertSame('A\B', $generatedTable->getFQCN());
    }
}
