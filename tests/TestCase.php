<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Cake\Orm;

use Cake\TestSuite\TestCase as CakeTestCase;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;
use WyriHaximus\React\Cake\Orm\Pool;

class TestCase extends CakeTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        Pool::reset();
        AsyncTableRegistry::reset();
    }
}
