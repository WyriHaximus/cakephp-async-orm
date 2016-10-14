<?php

namespace WyriHaximus\React\Cake\Orm\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use React\EventLoop\Factory as LoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class WorkerShell extends Shell
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    public function run()
    {
        $this->loop = LoopFactory::create();
        $recipient = Factory::child($this->loop, Configure::read('WyriHaximus.React.Cake.Orm.Line'));

        $recipient->registerRpc('table.call', function (Payload $payload) {
            return $this->handleTableCall($payload);
        });

        $this->loop->run();
    }

    /**
     * @param Payload $payload
     * @return \React\Promise\PromiseInterface
     */
    protected function handleTableCall(Payload $payload)
    {
        $result = call_user_func_array([
            TableRegistry::get('screenshots', ['className' => $payload['table'], 'table' => 'screenshots']),
            $payload['function'],
        ], unserialize($payload['arguments']));

        if ($result instanceof Query) {
            $result = $result->all();
        }

        return \React\Promise\resolve([
            'result' => serialize($result),
        ]);
    }
}
