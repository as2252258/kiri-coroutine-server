<?php
declare(strict_types=1);


namespace Kiri\Coroutine\Server;


use Kiri\Abstracts\Providers;
use Kiri\Di\LocalService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Kiri;

/**
 * Class DatabasesProviders
 * @package Database
 */
class ServerProviders extends Providers
{


	/**
	 * @param LocalService $application
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function onImport(LocalService $application): void
	{
		$server = $this->container->get(ServerCommand::class);

		$console = $this->container->get(Application::class);
		$console->add($server);
	}
}
