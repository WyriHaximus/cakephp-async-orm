<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use React\EventLoop\Factory as LoopFactory;
use React\Promise\Deferred;
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
            $deferred = new Deferred();
            $this->loop->futureTick(function () use ($payload, $deferred) {
                $this->handleTableCall($payload, $deferred);
            });

            return $deferred->promise();
        });

        $this->loop->run();
    }

    /**
     * @param Payload  $payload
     * @param Deferred $deferred
     */
    protected function handleTableCall(Payload $payload, Deferred $deferred)
    {
        $result = call_user_func_array([
            TableRegistry::get(
                $payload['table'],
                [
                    'className' => $payload['className'],
                    'table' => $payload['table'],
                ]
            ),
            $payload['function'],
        ], unserialize($payload['arguments']));

        if (!($result instanceof Query)) {
            $deferred->resolve([
                'result' => serialize($result),
            ]);

            return;
        }

        foreach ($result->all() as $row) {
            $deferred->notify([
                'row' => $row,
            ]);
        }

        $deferred->resolve();
    }
}
