<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use React\Promise\PromiseInterface;
use WyriHaximus\React\Cake\Orm\Annotations\Async;
use WyriHaximus\React\Cake\Orm\Annotations\Sync;

trait AsyncTable
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @param Pool $pool
     * @param string $tableName
     */
    public function setUpAsyncTable(Pool $pool, $tableName, $tableClass)
    {
        $this->pool = $pool;
        $this->tableName = $tableName;
        $this->annotationReader = new AnnotationReader();
        $this->reflectionClass = new \ReflectionClass($tableClass);
    }

    /**
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    protected function callAsyncOrSync($function, $arguments)
    {
        if ($this->pool === null) {
            return (new $this->tableName)->$function(...$arguments);
        }

        if (
            $this->returnsQuery($function) ||
            $this->hasMethodAnnotation($function, Async::class) ||
            (
                $this->hasClassAnnotation(Async::class) &&
                $this->hasNoMethodAnnotation($function)
            ) ||
            strpos(strtolower($function), 'save') === 0 ||
            strpos(strtolower($function), 'find') === 0 ||
            strpos(strtolower($function), 'fetch') === 0 ||
            strpos(strtolower($function), 'retrieve') === 0
        ) {
            return $this->callAsync($function, $arguments);
        }

        return $this->callSync($function, $arguments);
    }

    /**
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    private function callSync($function, array $arguments = [])
    {
        $table = TableRegistry::get(md5($this->tableName), [
            'className' => $this->tableName,
            'table' => 'screenshots',
        ]);
        if (isset(class_uses($table)[TableRegistryTrait::class])) {
            $table->setRegistry(AsyncTableRegistry::class);
        }
        return \React\Promise\resolve(
            call_user_func_array(
                [
                    $table,
                    $function
                ],
                $arguments
            )
        );
    }

    /**
     * @param $function
     * @param array $arguments
     * @return PromiseInterface
     */
    private function callAsync($function, array $arguments = [])
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

    /**
     * @param $class
     * @return bool
     */
    private function hasClassAnnotation($class)
    {
        return is_a($this->annotationReader->getClassAnnotation($this->reflectionClass, $class), $class);
    }

    /**
     * @param $method
     * @param $class
     * @return bool
     */
    private function hasMethodAnnotation($method, $class)
    {
        $methodReflection = $this->reflectionClass->getMethod($method);
        return is_a($this->annotationReader->getMethodAnnotation($methodReflection, $class), $class);
    }

    /**
     * @param $method
     * @return bool
     */
    private function hasNoMethodAnnotation($method)
    {
        $methodReflection = $this->reflectionClass->getMethod($method);
        return (
            $this->annotationReader->getMethodAnnotation($methodReflection, Async::class) === null &&
            $this->annotationReader->getMethodAnnotation($methodReflection, Sync::class) === null
        );
    }

    /**
     * @param $function
     * @return bool
     */
    private function returnsQuery($function)
    {
        $docBlockContents = $this->reflectionClass->getMethod($function)->getDocComment();
        if (!is_string($docBlockContents)) {
            return false;
        }

        $docBlock = $this->getDocBlock($docBlockContents);
        foreach ($docBlock->getTags() as $tag) {
            if ($tag->getName() === 'return' && ltrim($tag->getType(), '\\') == Query::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $docBlockContents
     * @return DocBlock
     */
    private function getDocBlock($docBlockContents)
    {
        if (class_exists('phpDocumentor\Reflection\DocBlockFactory')) {
            return DocBlockFactory::createInstance()->create($docBlockContents);
        }

        return new DocBlock($docBlockContents);
    }
}
