<?php

namespace Katu\Tools\Rest\API;

class Response
{
	protected $data;
	protected $info;
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function setData($data): Response
	{
		$this->data = $data;

		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setInfo(array $info): Response
	{
		$this->info = $info;

		return $this;
	}

	public function getInfo(): ?array
	{
		return $this->info;
	}

	public function getStatus(): ?int
	{
		try {
			return $this->getInfo()["http_code"];
		} catch (\Throwable $e) {
			return null;
		}
	}
}
