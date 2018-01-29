<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\ORM\Table;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolUtilizerInterface;

class AsyncTableRegistry implements PoolUtilizerInterface
{
    /**
     * @var AsyncTable[]
     */
    protected static $tables = [];

    /**
     * @var AsyncTableRegistry
     */
    protected static $instance = null;

    /**
     * @var bool
     */
    protected static $reset = false;

    /**
     * @param LoopInterface $loop
     * @param array         $config
     */
    public static function init(LoopInterface $loop, array $config = [])
    {
        Pool::getInstance($loop, $config);
    }

    /**
     * @param Table $table
     *
     * @return AsyncTable
     */
    public static function get(Table $table)
    {
        $tableName = get_class($table);

        if (isset(static::$tables[$tableName])) {
            return static::$tables[$tableName];
        }

        $asyncTableName = (new AsyncTableGenerator(
            Configure::read('WyriHaximus.React.Cake.Orm.Cache.AsyncTables')
        ))->generate($tableName)->getFQCN();

        $asyncTable = new $asyncTableName();

        if ($asyncTable instanceof AsyncTableInterface) {
            $asyncTable->setUpAsyncTable(
                Pool::getInstance(),
                $table->table(),
                App::className($tableName, 'Model/Table', 'Table')
            );
        }

        static::$tables[$tableName] = $asyncTable;

        return static::$tables[$tableName];
    }

    public static function getInstance()
    {
        if (null === self::$instance || self::$reset) {
            self::$instance = new static();
            self::$reset = false;
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$reset = true;
    }

    /**
     * @inheritDoc
     */
    public function info()
    {
        return Pool::getInstance()->info();
    }
}
