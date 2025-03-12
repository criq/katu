<?php

namespace Katu\Config;

class SolrConnectionConfig extends \Katu\Config\Config
{
	protected $collection;
	protected $host = "127.0.0.1";
	protected $port = 8983;
	protected $timeout = 5;

	public function __construct(string $collection, ?string $host, ?int $port)
	{
		$this->setCollection($collection);

		if ($host) {
			$this->setHost($host);
		}

		if ($port) {
			$this->setPort($port);
		}
	}

	public function setCollection(string $collection): SolrConnectionConfig
	{
		$this->collection = $collection;

		return $this;
	}

	public function getCollection(): string
	{
		return $this->collection;
	}

	public function setHost(string $host): SolrConnectionConfig
	{
		$this->host = $host;

		return $this;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function setPort(int $port): SolrConnectionConfig
	{
		$this->port = $port;

		return $this;
	}

	public function getPort(): int
	{
		return $this->port;
	}

	public function setTimeout(int $timeout): SolrConnectionConfig
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout(): int
	{
		return $this->timeout;
	}
}
