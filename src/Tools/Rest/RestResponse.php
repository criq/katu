<?php

namespace Katu\Tools\Rest;

use Katu\Types\TJSON;
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
				} elseif ($value instanceof \GuzzleHttp\Psr7\Stream) {
					$value = $value->getContents();
				} elseif ($value instanceof \DateTime) {
					$value = $value->format("c");
				} elseif ($value instanceof \Katu\Types\TURL) {
					$value = (string)$value;
				} elseif ($value instanceof \Katu\Types\TClass) {
					$value = $value->getPortableName();
				}
			});
		}

		return $payload;
	}

	public function getJSON(): TJSON
	{
		return \Katu\Files\Formats\JSON::encode($this->getResponse());
	}

	public function getInlineJSON(): TJSON
	{
		return \Katu\Files\Formats\JSON::encodeInline($this->getResponse());
	}

	public function getStream(): StreamInterface
	{
		return \GuzzleHttp\Psr7\Utils::streamFor($this->getJSON());
	}
}
