<?php

namespace Katu\Exceptions;

class RedirectException extends Exception
{
	protected $url;

	public function setURL($value) : RedirectException
	{
		$this->url = $value;

		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}
}
