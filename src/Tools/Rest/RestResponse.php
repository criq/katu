<?php

namespace Katu\Tools\Rest;

use Psr\Http\Message\StreamInterface;

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
			if ($value instanceof \GuzzleHttp\Psr7\Stream) {
				$value = $value->getContents();
			}
			if ($value instanceof \DateTime) {
				$value = $value->format("c");
			}
			if ($value instanceof \Katu\Types\TURL) {
				$value = (string)$value;
			}
		});

		return $array;
	}

	public function getStream(): StreamInterface
	{
		return \GuzzleHttp\Psr7\Utils::streamFor(\Katu\Files\Formats\JSON::encode($this->getResponse()));
	}
}
