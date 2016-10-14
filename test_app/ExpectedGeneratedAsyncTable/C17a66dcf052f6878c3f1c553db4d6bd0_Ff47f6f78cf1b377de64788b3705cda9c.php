<?php

namespace WyriHaximus\GeneratedAsyncCakeTable\WyriHaximus\React\TestApp\Cake\Orm\Table;

use Cake\Datasource\EntityInterface;
use WyriHaximus\React\TestApp\Cake\Orm\Table\ScreenshotsTable as BaseTable;
use WyriHaximus\React\Cake\Orm\AsyncTable;
use WyriHaximus\React\Cake\Orm\AsyncTableInterface;
class C17a66dcf052f6878c3f1c553db4d6bd0_Ff47f6f78cf1b377de64788b3705cda9c extends BaseTable implements AsyncTableInterface
{
    use AsyncTable;
    public function save(EntityInterface $entity, $options = array())
    {
        return $this->callAsyncOrSync('save', array($entity, $options));
    }
    public function fetchNewest()
    {
        return $this->callAsyncOrSync('fetchNewest', array());
    }
    public function getByMd5($md5)
    {
        return $this->callAsyncOrSync('getByMd5', array($md5));
    }
}
