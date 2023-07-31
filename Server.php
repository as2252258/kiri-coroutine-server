<?php

declare(strict_types=1);

namespace Kiri\Coroutine\Server;

use Exception;
use Kiri;
use Kiri\Coroutine\Server\Abstracts\CoroutineServer;
use Kiri\Events\EventDispatch;
use Kiri\Router\Router;
use Kiri\Server\Events\OnShutdown;
use Kiri\Server\Events\OnTaskerStart;
use Kiri\Server\Events\OnWorkerStart;
use Kiri\Server\Events\OnWorkerStop;
use Kiri\Server\State;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Swoole\Timer;

class Server
{

    /**
     * @var int
     */
    private int $daemon = 0;


    /**
     *
     */
    public function __construct()
    {
    }


    /**
     * @throws ReflectionException
     */
    private function manager(): CoroutineServer
    {
        return Kiri::getDi()->get(CoroutineServer::class);
    }


    /**
     * @param $process
     * @throws Exception
     */
    public function addProcess($process): void
    {
        $this->manager()->addProcess($process);
    }


    /**
     * @return void
     * @throws Exception
     */
    public function start(): void
    {
        on(OnWorkerStop::class, [Timer::class, 'clearAll'], 9999);
        on(OnWorkerStart::class, [$this, 'setWorkerName']);
        on(OnTaskerStart::class, [$this, 'setTaskerName']);

        $manager = Kiri::getDi()->get(Router::class);
        $manager->scan_build_route();

        $manager = $this->manager();
        $manager->initCoreServers(\config('server', []), $this->daemon);
        $manager->start();
    }


    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function shutdown(): void
    {
        $configs = \config('server', []);

        $state = Kiri::getDi()->get(State::class);
        $instances = $this->manager()->sortService($configs['ports'] ?? []);
        foreach ($instances as $config) {
            $state->exit($config->port);
        }

        $manager = Kiri::getDi()->get(EventDispatch::class);
        $manager->dispatch(new OnShutdown());
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function isRunner(): bool
    {
        $state = Kiri::getDi()->get(State::class);
        return $state->isRunner();
    }

}