<?php

namespace Katu\Tools\Emails;

abstract class Provider
{
	abstract public function dispatch(Request $request): Response;

	public function createRequest(Email $email): Request
	{
		return new Request($this, $email);
	}
}
