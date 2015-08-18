<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlockFactory;
use WyriHaximus\React\Cake\Orm\Annotations\Async;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

class AsyncTable
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @param Pool $pool
     * @param string $tableName
     */
    public function __construct(Pool $pool, $tableName, $tableClass)
    {
        $this->pool = $pool;
        $this->tableName = $tableName;
        $this->annotationReader = new AnnotationReader();
        $this->reflectionClass = new \ReflectionClass($tableClass);
    }

    public function __call($function, array $arguments = [])
    {
        if (
            $this->returnsQuery($function) ||
            $this->hasMethodAnnotation($function, Async::class) ||
            (
                $this->hasClassAnnotation(Async::class) &&
                $this->hasNoMethodAnnotation($function)
            ) ||
            strpos(strtolower($function), 'find') === 0 ||
            strpos(strtolower($function), 'fetch') === 0 ||
            strpos(strtolower($function), 'retrieve') === 0
        ) {
            return $this->callAsync($function, $arguments);
        }

        return $this->callSync($function, $arguments);
    }

    protected function callSync($function, array $arguments = [])
    {
        return \React\Promise\resolve(
            call_user_func_array(
                [
                    TableRegistry::get($this->tableName),
                    $function
                ],
                $arguments
            )
        );
    }

    protected function callAsync($function, array $arguments = [])
    {
        $unSerialize = function ($input) {
            if (is_string($input)) {
                return unserialize($input);
            }

            return $input;
        };
        return $this->
            pool->
            call($this->tableName, $function, $arguments)->
            then($unSerialize, $unSerialize, $unSerialize);
    }

    protected function hasClassAnnotation($class)
    {
        return is_a($this->annotationReader->getClassAnnotation($this->reflectionClass, $class), $class);
    }

    protected function hasMethodAnnotation($method, $class)
    {
        $methodReflection = $this->reflectionClass->getMethod($method);
        return is_a($this->annotationReader->getMethodAnnotation($methodReflection, $class), $class);
    }

    protected function hasNoMethodAnnotation($method)
    {
        $methodReflection = $this->reflectionClass->getMethod($method);
        return (
            $this->annotationReader->getMethodAnnotation($methodReflection, Async::class) === null &&
            $this->annotationReader->getMethodAnnotation($methodReflection, Sync::class) === null
        );
    }

    protected function returnsQuery($function)
    {
        $docBlockContents = $this->reflectionClass->getMethod($function)->getDocComment();
        if (!is_string($docBlockContents)) {
            return false;
        }

        $docBlock = new DocBlock($docBlockContents);
        foreach ($docBlock->getTags() as $tag) {
            if ($tag instanceof Return_ && ltrim($tag->getType(), '\\') == Query::class) {
                return true;
            }
        }

        return false;
    }
}
