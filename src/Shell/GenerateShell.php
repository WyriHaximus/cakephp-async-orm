<?php

namespace WyriHaximus\React\Cake\Orm\Shell;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;

class GenerateShell extends Shell
{
    public function all()
    {
        foreach (App::path('Table') as $path) {
            if (is_dir($path)) {
                $this->iteratePath($path);
            }
        }

        foreach (Plugin::loaded() as $plugin) {
            foreach (App::path('Table', $plugin) as $path) {
                if (is_dir($path)) {
                    $this->iteratePath($path);
                }
            }
        }

    }

    public function iteratePath($path)
    {
        foreach ($this->setupIterator($path) as $item) {
            $this->iterateClasses($this->getClassByFile($item));
        }
    }

    public function iterateClasses($classes)
    {
        foreach ($classes as $class) {
            AsyncTableRegistry::get(
                TableRegistry::get(
                    md5($class),
                    [
                        'className' => $class,
                    ]
                )
            );
        }
    }

    public function getClassByFile($fileName)
    {
        return (new ClassReflector(new SingleFileSourceLocator($fileName)))->getAllClasses();
    }

    protected function setupIterator($path)
    {
        return new \RegexIterator(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        ), '/.*?.php$/', \RegexIterator::GET_MATCH);
    }


    /**
     * Set options for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    // @codingStandardsIgnoreStart
    public function getOptionParser()
    {
        // @codingStandardsIgnoreEnd
        return parent::getOptionParser()->addSubcommand(
            'all',
            [
                'short' => 'a',
                // @codingStandardsIgnoreStart
                'help' => __('Searches and pregenerates all async tables it finds.'),
                // @codingStandardsIgnoreEnd
            ]
        // @codingStandardsIgnoreStart
        )->description(__('Async table pregenerator'));
        // @codingStandardsIgnoreEnd
    }
}
