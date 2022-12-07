<?php

namespace Katu\Tools\Rest;

use Psr\Http\Message\StreamInterface;

class RestResponse
{
	protected $payload = [];

	public function __construct($payload)
	{
		$this->payload = $payload;
	}

	public function setPayload($payload): RestResponse
	{
		$this->payload = $payload;

		return $this;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function getResponse()
	{
		$payload = $this->getPayload();

		if (is_array($payload)) {
			array_walk_recursive($payload, function (&$value, $key) {
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
		}

		return $payload;
	}

	public function getStream(): StreamInterface
	{
		return \GuzzleHttp\Psr7\Utils::streamFor(\Katu\Files\Formats\JSON::encode($this->getResponse()));
	}
}
