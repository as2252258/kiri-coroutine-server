<?php

namespace Kiri\Coroutine\Server\Process;



/**
 *
 */
abstract class BaseProcess implements OnProcessInterface
{

	protected bool $isStop = false;


	protected bool $redirect_stdin_and_stdout = FALSE;


	protected int $pipe_type = SOCK_DGRAM;


	protected bool $enable_coroutine = false;


	public string $name = '';


	/**
	 * @return string
	 */
	public function getName(): string
	{
		if (empty($this->name)) {
			$this->name = uniqid('p.');
		}
		return $this->name;
	}


	/**
	 * @return bool
	 */
	public function isStop(): bool
	{
		return $this->isStop;
	}

	/**
	 * @return bool
	 */
	public function getRedirectStdinAndStdout(): bool
	{
		return $this->redirect_stdin_and_stdout;
	}

	/**
	 * @return int
	 */
	public function getPipeType(): int
	{
		return $this->pipe_type;
	}

	/**
	 * @return bool
	 */
	public function isEnableCoroutine(): bool
	{
		return $this->enable_coroutine;
	}


	/**
	 *
	 */
	public function onProcessStop(): void
	{
		$this->isStop = true;
	}


	/**
	 * @return $this
	 */
	abstract public function onSigterm(): static;

}
