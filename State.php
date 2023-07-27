<?php

namespace Kiri\Coroutine\Server;

use Kiri\Abstracts\Component;
use Kiri\Coroutine\Server\Abstracts\TraitServer;
use Swoole\Process;
use Exception;

class State extends Component
{

    use TraitServer;


    public array $servers = [];


    /**
     * @return void
     */
    public function init(): void
    {
        $this->servers = \config('server.ports');
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function isRunner(): bool
    {
        $ports = $this->sortService($this->servers);
        foreach ($ports as $config) {
            if (checkPortIsAlready($config['port'])) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param $port
     * @throws Exception
     */
    public function exit($port): void
    {
        if (!($pid = checkPortIsAlready($port))) {
            return;
        }
        while (checkPortIsAlready($port)) {
            Process::kill($pid, 0) && Process::kill($pid, SIGTERM);
            usleep(300);
        }
    }

}