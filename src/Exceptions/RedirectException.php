<?php

namespace Katu\Exceptions;

class RedirectException extends Exception
{
	protected $url;

	public function setUrl($value) : RedirectException
	{
		$this->url = $value;

		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}
}
