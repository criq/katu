<?php

namespace Katu\Tools\Services;

abstract class Service implements ServiceInterface
{
	protected $config;

	abstract public function getCode(): string;
	abstract public function getDescription(): ?string;
	abstract public function getTitle(): string;
	abstract public function setConfigFromSecret(string $secret): ?array;

	public function setConfig(?array $config): ServiceInterface
	{
		$this->config = $config;

		return $this;
	}

	public function getConfig(): ?array
	{
		return $this->config;
	}
}
