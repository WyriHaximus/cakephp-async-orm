<?php

namespace WyriHaximus\React\Cake\Orm\Shell;

use Cake\Console\Shell;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

class WorkerShell extends Shell
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    public function run()
    {
        $this->loop = Factory::create();
        $recipient = new Recipient($this->loop);

        $recipient->registerRpc('table.call', function (Invoke $invoke) {
            $this->handleTableCall($invoke);
        });

        $this->loop->run();
    }

    protected function handleTableCall(Invoke $invoke)
    {
        $payload = $invoke->getPayload();

        $result = call_user_func_array([
            TableRegistry::get($payload['table']),
            $payload['function'],
        ], unserialize($payload['arguments']));

        if ($result instanceof Query) {
            $result = $result->all();
        }

        $invoke->getDeferred()->resolve(serialize($result));
    }
}
