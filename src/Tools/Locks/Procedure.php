<?php

namespace Katu\Tools\Locks;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Procedure
{
	protected $callback;
	protected $identifier;
	protected $lockExcludedPlatforms;
	protected $maxLoadAverage;
	protected $timeout;

	public function __construct(TIdentifier $identifier, Timeout $timeout, callable $callback)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
		$this->setCallback($callback);
	}

	public function setIdentifier(TIdentifier $value): Procedure
	{
		$this->identifier = $value;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function setTimeout(Timeout $value): Procedure
	{
		$this->timeout = $value;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout;
	}

	public function setCallback(callable $value): Procedure
	{
		$this->callback = $value;

		return $this;
	}

	public function getCallback(): callable
	{
		return $this->callback;
	}

	public function setMaxLoadAverage(?float $value): Procedure
	{
		$this->maxLoadAverage = $value;

		return $this;
	}

	public function getMaxLoadAverage(): ?float
	{
		return $this->maxLoadAverage;
	}

	public function setLockExcludedPlatforms(?array $value): Procedure
	{
		$this->lockExcludedPlatforms = $value;

		return $this;
	}

	public function getLockExcludedPlatforms(): array
	{
		return $this->lockExcludedPlatforms ?: [];
	}

	public function run()
	{
		try {
			if ($this->getMaxLoadAverage()) {
				\Katu\Tools\System\System::assertMaxLoadAverage($this->getMaxLoadAverage());
			}

			$lock = new \Katu\Tools\Locks\Lock($this->getIdentifier(), $this->getTimeout(), function () {
				call_user_func($this->getCallback());
			});

			foreach ($this->getLockExcludedPlatforms() as $platform) {
				$lock->excludePlatform($platform);
			}

			return $lock->run();
		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		} catch (\Katu\Exceptions\LoadAverageExceededException $e) {
			// Nevermind.
		} catch (\Throwable $e) {
			\App\App::getLogger($this->getIdentifier())->error($e);
			if ($lock ?? null) {
				$lock->unlock();
			}
		}
	}
}
