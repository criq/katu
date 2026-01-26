<?php

namespace Katu\Tools\Services;

interface ServiceInterface
{
	public function getCode(): string;
	public function getDescription(): ?string;
	public function getTitle(): string;
	public function setConfigFromSecret(string $secret): ?array;
	public function setConfig(?array $config): ServiceInterface;
	public function getConfig(): ?array;
}
