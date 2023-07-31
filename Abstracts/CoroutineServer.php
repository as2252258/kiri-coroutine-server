<?php

namespace Kiri\Coroutine\Server\Abstracts;

use Exception;
use Kiri;
use Kiri\Abstracts\Logger;
use Kiri\Exception\ConfigException;
use Kiri\Exception\NotFindClassException;
use Kiri\Server\Config as SConfig;
use Kiri\Server\Constant;
use Kiri\Server\Events\OnServerBeforeStart;
use Kiri\Server\Events\OnShutdown;
use Kiri\Server\Handler\OnServer;
use Kiri\Server\ServerInterface;
use Kiri\Server\Task\TaskInterface;
use Kiri\Server\Task\Task;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Swoole\Coroutine;
use Swoole\Server;
use function Swoole\Coroutine\run;

/**
 *
 */
class CoroutineServer implements ServerInterface
{

    use TraitServer;


    /**
     * @var Server|null
     */
    private ?Server $server = null;

    protected array $services = [];


    /**
     * @param array $service
     * @param int $daemon
     * @return void
     * @throws Exception
     */
    public function initCoreServers(array $service, int $daemon = 0): void
    {
        $this->services = $service;
        $this->initProcess();
        $this->onSignal();
    }

    /**
     * @return void
     */
    private function initProcess(): void
    {
//        foreach ($this->_process as $process) {
//            $this->server->addProcess($process);
//        }
    }


    /**
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|ReflectionException
     */
    public function shutdown(): bool
    {
        $this->server->shutdown();

        event(new OnShutdown());

        return true;
    }


    /**
     * @return void
     * @throws
     */
    private function onTaskListen(): void
    {
        if (!isset($this->server->setting[Constant::OPTION_TASK_WORKER_NUM])) {
            return;
        }
        $container = Kiri::getDi();
        $task = $container->get(Task::class);
        $container->bind(TaskInterface::class, $task);
        $task->initTaskWorker($this->server);
    }


    /**
     * @param $no
     * @param array $signInfo
     * @return void
     */
    public function onSigint($no, array $signInfo): void
    {
        try {
            Logger::_alert('Pid ' . getmypid() . ' get signo ' . $no);
            $this->shutdown();
        } catch (\Throwable $exception) {
            error($exception);
        }
    }


    /**
     * @param Server\Port|Server $base
     * @param array $events
     * @return void
     * @throws ReflectionException
     */
    private function onEventListen(Server\Port|Server $base, array $events): void
    {
        foreach ($events as $name => $event) {
            if (is_array($event) && is_string($event[0])) {
                $event[0] = Kiri::getDi()->get($event[0]);
            }
            $base->on($name, $event);
        }
    }


    /**
     * @return void
     * @throws
     */
    public function start(): void
    {
        run(function () {
            event(new OnServerBeforeStart());
            foreach (\config('server.ports', []) as $service) {
                $service = $this->genConfigService($service);
                $class = $this->getCoroutineServerClass($service['type']);
                if (in_array($service['type'], [Constant::SERVER_TYPE_HTTP, Constant::SERVER_TYPE_WEBSOCKET])) {
                    /** @var Coroutine\Http\Server $server */
                    $server = created($class, [$service['host'], $service['port'], $service['isSsl'], true]);
                    $server->handle('/', $service['events'][Constant::REQUEST]);
                } else {
                    throw new Exception('暂不支持的类型');
                }
                Coroutine::create(function () use ($server) {
                    $server->start();
                });
            }
        });
    }


}
