<?php

namespace Kiri\Coroutine\Server\Process;

use Exception;
use Kiri;
use Kiri\Abstracts\Component;
use Kiri\Server\Contract\OnProcessInterface;
use Swoole\Coroutine;
use Swoole\Process;
use Kiri\Events\EventProvider;
use Kiri\Server\Events\OnServerBeforeStart;

class ProcessManager extends Component
{


	/** @var array<string, BaseProcess> */
	private array $_process = [];


	/**
	 * @return void
	 * @throws Exception
	 */
	public function init(): void
	{
		$provider = Kiri::getDi()->get(EventProvider::class);
		$provider->on(OnServerBeforeStart::class, [$this, 'OnServerBeforeStart']);
	}


    /**
     * @param OnServerBeforeStart $beforeStart
     * @return void
     */
	public function OnServerBeforeStart(OnServerBeforeStart $beforeStart): void
	{
		foreach ($this->_process as $custom) {
            Coroutine::create(static function() use ($custom) {
                $custom->onSigterm()->process(null);
            });
		}
	}


	/**
	 * @return Process[]
	 */
	public function getProcesses(): array
	{
		return $this->_process;
	}


	/**
	 * @param string|OnProcessInterface|BaseProcess $custom
	 * @throws Exception
	 */
	public function add(string|OnProcessInterface|BaseProcess $custom): void
	{
		if (is_string($custom)) {
			$custom = Kiri::getDi()->get($custom);
		}

		if (isset($this->_process[$custom->getName()])) {
			throw new Exception('Process(' . $custom->getName() . ') is exists.');
		}

		$this->_process[$custom->getName()] = $custom;
	}

}
