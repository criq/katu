<?php

namespace Katu\Tools\Services;

class ServiceCollection extends \ArrayObject
{
	public static function createDefault(): ServiceCollection
	{
		return new static([
			new \Katu\Tools\Emails\Services\EcomailService(""),
		]);
	}

	public function filterByCode(string $code): ServiceCollection
	{
		$filtered = array_filter($this->getArrayCopy(), function ($service) use ($code) {
			return (method_exists($service, "getCode") && $service->getCode() === $code);
		});

		return new static($filtered);
	}

	public function getFirst()
	{
		$items = array_values($this->getArrayCopy());

		return $items[0] ?? null;
	}
}
