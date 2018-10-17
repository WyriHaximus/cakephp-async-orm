<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

use Cake\ORM\TableRegistry;

trait TableRegistryTrait
{
    /**
     * @var string
     */
    protected $registry = TableRegistry::class;

    /**
     * @param string $registry
     */
    public function setRegistry($registry)
    {
        if ($registry !== TableRegistry::class && $registry !== AsyncTableRegistry::class) {
            throw new \InvalidArgumentException('Not a AsyncTableRegistry or TableRegistry');
        }

        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return call_user_func_array($this->registry . '::get', func_get_args());
    }
}
