<?php

namespace WyriHaximus\React\Cake\Orm\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use React\EventLoop\Factory as LoopFactory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

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

        $recipient->registerRpc('table.call', function (Payload $payload, Deferred $deferred) {
            $this->handleTableCall($payload, $deferred);
        });

        $this->loop->run();
    }

    protected function handleTableCall(Payload $payload, Deferred $deferred)
    {
        $result = call_user_func_array([
            TableRegistry::get($payload['table']),
            $payload['function'],
        ], unserialize($payload['arguments']));

        if ($result instanceof Query) {
            $result = $result->all();
        }

        $deferred->resolve([
            'result' => serialize($result),
        ]);
    }
}
