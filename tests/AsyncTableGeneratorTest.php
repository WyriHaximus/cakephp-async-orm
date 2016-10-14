<?php

namespace WyriHaximus\React\Tests\Cake\Orm;

use WyriHaximus\React\Cake\Orm\AsyncTableGenerator;
use WyriHaximus\React\TestApp\Cake\Orm\Table\ScreenshotsTable;

class AsyncTableGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $tmpDir = sys_get_temp_dir() . DS . uniqid('WyriHaximus-Cake-Async-ORM-', true) . DS;
        mkdir($tmpDir, 0777, true);
        $generator = new AsyncTableGenerator($tmpDir);
        $filename = $generator->generate(ScreenshotsTable::class);
        $this->assertFileEquals(
            dirname(__DIR__) . DS . 'test_app' . DS . 'ExpectedGeneratedAsyncTable' . DS . 'AsyncScreenshotsTable.php',
            $tmpDir . $filename . '.php'
        );
    }
}
