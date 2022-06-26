<?php

namespace Katu\Tools\Rest;

class RestResponse
{
	protected $payload = [];

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function setPayload(array $payload): RestResponse
	{
		$this->payload = $payload;

		return $this;
	}

	public function getPayload(): array
	{
		return $this->payload;
	}

	public function getResponse(): array
	{
		$array = $this->getPayload();

		array_walk_recursive($array, function (&$value, $key) {
			if ($value instanceof static) {
				$value = $value->getResponse();
			}
			if ($value instanceof \DateTime) {
				$value = $value->format("r");
			}
		});

		return $array;
	}
}