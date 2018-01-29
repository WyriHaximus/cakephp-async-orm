<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm;

use WyriHaximus\React\Cake\Orm\AsyncTableGenerator;
use WyriHaximus\React\Cake\Orm\GeneratedTable;
use WyriHaximus\React\TestApp\Cake\Orm\Table\ScreenshotsTable;

class AsyncTableGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $tmpDir = sys_get_temp_dir() . DS . uniqid('WyriHaximus-Cake-Async-ORM-', true) . DS;
        mkdir($tmpDir, 0777, true);
        $generator = new AsyncTableGenerator($tmpDir);
        $generatedTable = $generator->generate(ScreenshotsTable::class);
        $this->assertInstanceOf(GeneratedTable::class, $generatedTable);
        $this->assertFileEquals(
            dirname(__DIR__) . DS . 'test_app' . DS . 'ExpectedGeneratedAsyncTable' . DS . 'C17a66dcf052f6878c3f1c553db4d6bd0_Ff47f6f78cf1b377de64788b3705cda9c.php',
            $tmpDir . $generatedTable->getClassName() . '.php'
        );
    }
}
