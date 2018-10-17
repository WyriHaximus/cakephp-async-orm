<?php declare(strict_types=1);

namespace WyriHaximus\React\Cake\Orm;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class WorkerChild implements ChildInterface
{
    private $messenger;
    private $loop;

    /**
     * WorkerChild constructor.
     */
    public function __construct(Messenger $messenger, LoopInterface $loop)
    {
        $this->messenger = $messenger;
        $this->loop = $loop;

        $this->messenger->registerRpc('table.call', function (Payload $payload) {
            $deferred = new Deferred();
            $this->loop->futureTick(function () use ($payload, $deferred) {
                $this->handleTableCall($payload, $deferred);
            });

            return $deferred->promise();
        });
    }

    /**
     * @inheritDoc
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        require dirname(dirname(dirname(dirname(__DIR__)))) . '/config/paths.php';
        require CORE_PATH . 'config' . DS . 'bootstrap.php';
        Configure::config('default', new Configure\Engine\PhpConfig());
        Configure::load('app', 'default', false);
        Cache::setConfig(Configure::consume('Cache'));
        ConnectionManager::setConfig(Configure::consume('Datasources'));

        return new self($messenger, $loop);
    }

    /**
     * @param Payload  $payload
     * @param Deferred $deferred
     */
    protected function handleTableCall(Payload $payload, Deferred $deferred)
    {
        $result = call_user_func_array([
            TableRegistry::get(
                $payload['table']/*,
                [
                    'className' => $payload['className'],
                    'table' => $payload['table'],
                ]*/
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
