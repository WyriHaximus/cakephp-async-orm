<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

use Cake\Datasource\EntityInterface;
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Generator;
use PhpParser\Builder\Use_;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use RuntimeException;
use WyriHaximus\React\Cake\Orm\Annotations\Ignore;

final class AsyncTableGenerator
{
    const NAMESPACE_PREFIX = 'WyriHaximus\GeneratedAsyncCakeTable';

    /**
     * @var string
     */
    private $storageLocation;

    /**
     * @var BuilderFactory
     */
    private $factory;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @param string $storageLocation
     */
    public function __construct($storageLocation)
    {
        $this->storageLocation = $storageLocation;
        $this->factory = new BuilderFactory();
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->classLoader = $this->locateClassloader();
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * @param  string         $tableClass
     * @param  bool           $force
     * @return GeneratedTable
     */
    public function generate($tableClass, $force = false)
    {
        $fileName = $this->classLoader->findFile($tableClass);
        $contents = file_get_contents($fileName);
        $ast = $this->parser->parse($contents);
        $namespace = static::NAMESPACE_PREFIX;

        $hashedClass = $this->extractNamespace($ast) . '_C' . md5($tableClass) . '_F' . md5($contents);

        $generatedTable = new GeneratedTable($namespace, $hashedClass);

        if (!$force && file_exists($this->storageLocation . DIRECTORY_SEPARATOR . $hashedClass . '.php')) {
            return $generatedTable;
        }

        $class = $this->factory->class($hashedClass)
            ->extend('BaseTable')
            ->implement('AsyncTableInterface')
        ;

        $class->addStmt(
            new Node\Stmt\TraitUse([
                new Node\Name('AsyncTable'),
            ])
        );

        $class->addStmt(
            self::createMethod(
                'save',
                [
                    new Node\Param('entity', null, 'EntityInterface'),
                    new Node\Param('options', new Node\Expr\Array_()),
                ]
            )
        );

        foreach ($this->extractMethods($ast) as $method) {
            if (in_array($method->name, ['initialize', 'validationDefault'], true)) {
                continue;
            }

            if ($this->hasMethodAnnotation(new ReflectionClass($tableClass), $method->name, Ignore::class)) {
                continue;
            }

            $class->addStmt(
                self::createMethod(
                    $method->name,
                    $method->params
                )
            );
        }

        $uses = iterator_to_array($this->extractClassImports($ast));
        $uses[] = $this->factory->use(EntityInterface::class);
        $uses[] = $this->factory->use($tableClass)->as('BaseTable');
        $uses[] = $this->factory->use(AsyncTable::class);
        $uses[] = $this->factory->use(AsyncTable::class);
        $uses[] = $this->factory->use(AsyncTableInterface::class);

        $node = $this->factory->namespace($namespace)
            ->addStmts($this->removeDuplicatedUses($uses))
            ->addStmt($class)
            ->getNode()
        ;

        $fileName = $this->storageLocation . DIRECTORY_SEPARATOR . $hashedClass . '.php';
        $prettyPrinter = new Standard();
        $fileContents = $prettyPrinter->prettyPrintFile([$node,]) . PHP_EOL;
        file_put_contents(
            $fileName,
            $fileContents
        );

        do {
            usleep(500);
        } while (file_get_contents($fileName) !== $fileContents);

        $command = 'PHP_CS_FIXER_IGNORE_ENV=1 ' .
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/bin/php-cs-fixer fix ' .
            $this->storageLocation . DIRECTORY_SEPARATOR . $hashedClass . '.php' .
            ' --config=' .
            dirname(__DIR__) .
            DIRECTORY_SEPARATOR .
            '.php_cs ' .
            ' --allow-risky=yes -q -v --stop-on-violation --using-cache=no' .
            ' 2>&1';

        exec($command);

        return $generatedTable;
    }

    protected function removeDuplicatedUses(array $rawUses)
    {
        $uses = [];
        /** @var Node\Stmt\Use_ $use */
        foreach ($rawUses as $use) {
            if ($use instanceof Use_) {
                $use = $use->getNode();
            }

            $uses[$use->uses[0]->type . '_____' . $use->uses[0]->name->toString() . '_____' . $use->uses[0]->alias] = $use;
        }
        return $uses;
    }

    protected function createMethod($method, array $params)
    {
        return $this->factory->method($method)
            ->makePublic()
            ->addParams($params)
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\MethodCall(
                        new Node\Expr\Variable('this'),
                        'callAsyncOrSync',
                        [
                            new Node\Scalar\String_($method),
                            new Node\Expr\Array_(
                                $this->createMethodArguments($params)
                            ),
                        ]
                    )
                )
            )
            ;
    }

    /**
     * @param  array $params
     * @return array
     */
    protected function createMethodArguments(array $params)
    {
        $arguments = [];
        foreach ($params as $param) {
            if (!($param instanceof Node\Param)) {
                continue;
            }
            $arguments[] = new Node\Expr\Variable($param->name);
        }

        return $arguments;
    }

    /**
     * @param  Node[]    $ast
     * @return Generator
     */
    protected function extractMethods(array $ast)
    {
        foreach ($ast as $node) {
            if (!isset($node->stmts)) {
                continue;
            }

            foreach ($this->iterageStmts($node->stmts) as $stmt) {
                yield $stmt;
            }
        }
    }

    protected function iterageStmts(array $stmts)
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                yield $stmt;
            }

            if (!isset($stmt->stmts)) {
                continue;
            }

            foreach ($this->iterageStmts($stmt->stmts) as $stmt) {
                yield $stmt;
            }
        }
    }

    protected function extractNamespace(array $ast)
    {
        foreach ($ast as $node) {
            if ($node instanceof Node\Stmt\Namespace_) {
                return str_replace('\\', '_', (string)$node->name);
            }
        }

        return 'N' . uniqid('', true);
    }

    protected function extractClassImports(array $ast)
    {
        foreach ($ast as $node) {
            if ($node instanceof Node\Stmt\Namespace_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Node\Stmt\Use_) {
                        yield $stmt;
                    }
                }
            }
        }
    }

    private function locateClassloader()
    {
        foreach ([
                     dirname(__DIR__) . DS . 'vendor' . DS . 'autoload.php',
                     dirname(dirname(dirname(__DIR__))) . DS . 'autoload.php',
                 ] as $path) {
            if (file_exists($path)) {
                return require $path;
            }
        }

        throw new RuntimeException('Unable to locate class loader');
    }

    /**
     * @param  ReflectionClass $reflectionClass
     * @param  string          $method
     * @param  string          $class
     * @return bool
     */
    private function hasMethodAnnotation(ReflectionClass $reflectionClass, $method, $class)
    {
        $methodReflection = $reflectionClass->getMethod($method);

        return is_a($this->annotationReader->getMethodAnnotation($methodReflection, $class), $class);
    }
}
