<?php

namespace Katu\Tools\Rest\API;

class Request
{
	protected $api;
	protected $curl;
	protected $endpoint;
	protected $method;
	protected $params;

	public function __construct(API $api, string $method, string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null)
	{
		$this->setAPI($api);
		$this->setMethod($method);
		$this->setEndpoint($endpoint);
		$this->setParams($params);
		$this->setCurl($curl);
	}

	public function setAPI(API $api): Request
	{
		$this->api = $api;

		return $this;
	}

	public function getAPI(): API
	{
		return $this->api;
	}

	public function setMethod(string $method): Request
	{
		$this->method = $method;

		return $this;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function setEndpoint(string $endpoint): Request
	{
		$this->endpoint = $endpoint;

		return $this;
	}

	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	public function setParams(?array $params): Request
	{
		$this->params = $params;

		return $this;
	}

	public function getParams(): ?array
	{
		return $this->params;
	}

	public function setCurl(?\Curl\Curl $curl): Request
	{
		$this->curl = $curl;

		return $this;
	}

	public function getCurl(): \Curl\Curl
	{
		return $this->curl ?: $this->getAPI()->getCurl();
	}

	public function getURL(): string
	{
		return implode("/", [
			rtrim($this->getAPI()->getBaseURL(), "/"),
			ltrim($this->getEndpoint(), "/"),
		]);
	}

	public function getResponse(): Response
	{
		$response = new Response($this);

		$curl = $this->getCurl();
		$method = $this->getMethod();
		$data = $curl->$method($this->getURL(), $this->getParams());

		$response->setData($data);
		$response->setInfo($curl->getInfo());

		return $response;
	}
}
