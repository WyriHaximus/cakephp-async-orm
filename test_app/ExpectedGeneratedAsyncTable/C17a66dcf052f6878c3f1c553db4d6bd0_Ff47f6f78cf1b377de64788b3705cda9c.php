<?php declare(strict_types=1);

namespace WyriHaximus\GeneratedAsyncCakeTable;

use Cake\Datasource\EntityInterface;
use WyriHaximus\React\Cake\Orm\AsyncTable;
use WyriHaximus\React\Cake\Orm\AsyncTableInterface;
use WyriHaximus\React\TestApp\Cake\Orm\Table\ScreenshotsTable as BaseTable;

class WyriHaximus_React_TestApp_Cake_Orm_Table_C17a66dcf052f6878c3f1c553db4d6bd0_F53497c4a0f2baa918de7ec31e3ec7f23 extends BaseTable implements AsyncTableInterface
{
    use AsyncTable;

    public function save(EntityInterface $entity, $options = [])
    {
        return $this->callAsyncOrSync('save', [$entity, $options]);
    }

    public function fetchNewest()
    {
        return $this->callAsyncOrSync('fetchNewest', []);
    }

    public function getByMd5($md5)
    {
        return $this->callAsyncOrSync('getByMd5', [$md5]);
    }

    public function getByEntity($md5)
    {
        return $this->callAsyncOrSync('getByEntity', [$md5]);
    }
}
