<?php

namespace Katu\PDO;

class Error extends \Katu\Exceptions\Exception
{
	protected $sqlStateErrorCode;

	public static function createFromErrorInfo(array $errorInfo) : Error
	{
		return (new static($errorInfo[2], $errorInfo[1]))
			->setSqlStateErrorCode($errorInfo[0])
			;
	}

	public function setSqlStateErrorCode(string $sqlStateErrorCode) : Error
	{
		$this->sqlStateErrorCode = $sqlStateErrorCode;

		return $this;
	}

	public function getSqlStateErrorCode() : string
	{
		return $this->sqlStateErrorCode;
	}
}
