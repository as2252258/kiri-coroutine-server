<?php

declare(strict_types=1);

namespace Kiri\Coroutine\Server;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{


    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('sw:server')
            ->setDescription('server start|stop|reload|restart')
            ->addArgument('action', InputArgument::OPTIONAL, 'run action', 'start')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'is run daemonize');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return match ($input->getArgument('action')) {
            'restart' => $this->restart($input),
            'stop'    => $this->stop(),
            'start'   => $this->start($input),
            default   =>
            throw new Exception('I don\'t know what I want to do.')
        };
    }


    /**
     * @param InputInterface $input
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function restart(InputInterface $input): int
    {
        $server = \Kiri::getDi()->get(Server::class);
        $server->shutdown();
        $server->start();
        return 1;
    }


    /**
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function stop(): int
    {
        $server = \Kiri::getDi()->get(Server::class);
        $server->shutdown();
        return 1;
    }


    /**
     * @param InputInterface $input
     * @return int
     * @throws ReflectionException
     */
    public function start(InputInterface $input): int
    {
        $server = \Kiri::getDi()->get(Server::class);
        $server->start();
        return 1;
    }

}