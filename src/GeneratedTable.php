<?php

namespace WyriHaximus\React\Cake\Orm;

final class GeneratedTable
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $className;

    /**
     * @param string $namespace
     * @param string $className
     */
    public function __construct($namespace, $className)
    {
        $this->namespace = $namespace;
        $this->className = $className;
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
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFQCN()
    {
        return $this->namespace . '\\' . $this->className;
    }
}
