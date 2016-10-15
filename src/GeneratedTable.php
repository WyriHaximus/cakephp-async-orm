<?php

namespace WyriHaximus\React\Cake\Orm;

final class GeneratedTable
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @param string $className
     * @param string $namespace
     */
    public function __construct($className, $namespace)
    {
        $this->className = $className;
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getFQCN()
    {
        return $this->namespace . '\\' . $this->className;
    }
}
