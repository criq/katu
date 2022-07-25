<?php

namespace Katu\Exceptions;

class Exception extends \Exception
{
	const HTTP_CODE = 400;

	protected $abbr;
	protected $context;
	protected $errorNames = [];

	public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function __toString(): string
	{
		return (string)$this->getMessage();
	}

	public function getHttpCode(): int
	{
		return (int)static::HTTP_CODE;
	}

	public function setAbbr(string $abbr): Exception
	{
		$this->abbr = trim($abbr);

		return $this;
	}

	public function getAbbr(): ?string
	{
		return $this->abbr;
	}

	public function addErrorName(string $errorName): Exception
	{
		foreach (func_get_args() as $arg) {
			$this->errorNames[] = static::getErrorName($arg);
		}

		$this->maintainErrorNames();

		return $this;
	}

	public static function getErrorName(string $errorName): string
	{
		return implode(".", array_filter((array)$errorName));
	}

	public function getErrorNameIndex(string $errorName)
	{
		return array_search(static::getErrorName($errorName), $this->errorNames);
	}

	public function replaceErrorName(string $errorName, string $replacement): Exception
	{
		$index = $this->getErrorNameIndex($errorName);
		if ($index !== false && isset($this->errorNames[$index])) {
			$this->errorNames[$index] = static::getErrorName($replacement);
		}

		$this->maintainErrorNames();

		return $this;
	}

	private function maintainErrorNames(): Exception
	{
		$this->errorNames = array_values(array_unique(array_filter($this->errorNames)));

		return $this;
	}

	public function getErrorNames(): array
	{
		return $this->errorNames;
	}

	public function getResponseArray()
	{
		return [
			"message" => $this->getMessage(),
			"abbr" => $this->getAbbr() ?: null,
			"names" => $this->getErrorNames() ?: null,
		];
	}

	public function setContext(?array $context): Exception
	{
		$this->context = $context;

		return $this;
	}

	public function getContext(): ?array
	{
		return $this->context;
	}
}
