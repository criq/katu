<?php

namespace Katu\PDO;

class Exception extends \Katu\Exceptions\Exception
{
	protected $sqlStateErrorCode;

	public static function createFromErrorInfo(array $errorInfo): Exception
	{
		return (new static((string)$errorInfo[2], (int)$errorInfo[1]))
			->setSqlStateErrorCode($errorInfo[0])
			;
	}

	public function setSqlStateErrorCode(string $sqlStateErrorCode): Exception
	{
		$this->sqlStateErrorCode = $sqlStateErrorCode;

		return $this;
	}

	public function getSqlStateErrorCode(): string
	{
		return $this->sqlStateErrorCode;
	}
}
