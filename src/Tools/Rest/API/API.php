<?php

namespace Katu\Tools\Rest\API;

use Katu\Types\TURL;

abstract class API
{
	protected $curl;

	abstract public function getBaseURL(): TURL;

	public function setCurl(?\Curl\Curl $curl): API
	{
		$this->curl = $curl;

		return $this;
	}

	public function getCurl(): \Curl\Curl
	{
		if (!$this->curl) {
			$this->curl = $this->generateCurl();
		}

		return $this->curl;
	}

	public function generateCurl(): \Curl\Curl
	{
		return new \Curl\Curl;
	}

	public function createRequest(string $method, string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Request
	{
		return new Request($this, $method, $endpoint, $params, $curl);
	}

	public function call(string $method, string $endpoint, ?array $params, ?\Curl\Curl $curl = null): Response
	{
		return $this->createRequest($method, $endpoint, $params, $curl)->getResponse();
	}

	public function get(string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Response
	{
		return $this->call("GET", $endpoint, $params, $curl);
	}

	public function post(string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Response
	{
		return $this->call("POST", $endpoint, $params, $curl);
	}
}
