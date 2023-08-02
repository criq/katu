<?php

namespace Katu\Tools\Session;

abstract class PayloadFlash extends Flash
{
	protected $payload;

	public function __construct($payload)
	{
		$this->setPayload($payload);
	}

	public function setPayload($payload): PayloadFlash
	{
		$this->payload = $payload;

		return $this;
	}

	public function getPayload()
	{
		return $this->payload;
	}
}
