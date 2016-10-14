<?php

namespace WyriHaximus\React\Cake\Orm;

use Cake\Datasource\EntityInterface;
use Composer\Autoload\ClassLoader;
use Generator;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;

final class AsyncTableGenerator
{
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
     * @param string $storageLocation
     */
    public function __construct($storageLocation)
    {
        $this->storageLocation = $storageLocation;
        $this->factory = new BuilderFactory();
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->classLoader = $this->locateClassloader();
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
     * @param string $tableClass
     * @return string
     */
    public function generate($tableClass)
    {
        $fileName = $this->classLoader->findFile($tableClass);
        $ast = $this->parser->parse(file_get_contents($fileName));

        $hashedClass = 'C' . md5($tableClass);

        $class = $this->factory->class($hashedClass)
            ->extend('BaseTable')
            ->implement('AsyncTableInterface')
        ;

        $class->addStmt(
            new Node\Stmt\TraitUse([
                new Node\Name('AsyncTable')
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
            $class->addStmt(
                self::createMethod(
                    $method->name,
                    $method->params
                )
            );
        }

        $node = $this->factory->namespace('WyriHaximus\GeneratedAsyncCakeTable\\' . $this->extractNamespace($ast))
            ->addStmt($this->factory->use(EntityInterface::class))
            ->addStmt($this->factory->use($tableClass)->as('BaseTable'))
            ->addStmt($this->factory->use(AsyncTable::class))
            ->addStmt($this->factory->use(AsyncTableInterface::class))
            ->addStmt($class)
            ->getNode()
        ;

        $prettyPrinter = new Standard();
        file_put_contents(
            $this->storageLocation . DIRECTORY_SEPARATOR . $hashedClass . '.php',
            $prettyPrinter->prettyPrintFile([
                $node
            ]) . PHP_EOL
        );

        return $hashedClass;
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
     * @param array $params
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
     * @param string $tableClass
     * @param Node[] $ast
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

            foreach ($this->iterageStmts($node->stmts) as $stmt) {
                yield $stmt;
            }
        }
    }

    protected function extractNamespace(array $ast)
    {
        foreach ($ast as $node) {
            if ($node instanceof Node\Stmt\Namespace_) {
                return (string)$node->name;
            }
        }

        return 'N' . uniqid('', true);
    }
}
