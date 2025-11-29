<?php

namespace Katu\Errors;

use Katu\Tools\Intl\Locale;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Strings\Code;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\ParamCollection;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

class Error implements PackagedInterface, RestResponseInterface
{
	protected ?array $options;
	protected ?ErrorVersionCollection $versions;
	protected ?ParamCollection $params;
	protected ?Code $code;
	protected ?string $help;
	protected ?string $message;

	public function __construct(?string $message = null, $code = null, ?ErrorVersionCollection $versions = null)
	{
		$this->options = null;
		$this->versions = null;
		$this->params = null;
		$this->code = null;
		$this->help = null;
		$this->message = null;

		$this->setMessage($message);
		$this->setCode($code);
		$this->setVersions($versions);
	}

	public function __toString(): string
	{
		return (string)$this->getMessage();
	}

	public static function createFromPackage(Package $package): Error
	{
		$class = TClass::createFromPortableName($package->getPayload()["class"]);
		$className = $class->getName();

		return new $className($package->getPayload()["message"], $package->getPayload()["code"]);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"message" => $this->getMessage(),
			"code" => $this->getCode() ? (string)$this->getCode() : null,
		]);
	}

	public function setMessage(?string $value): Error
	{
		$this->message = $value;

		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	public function getMessageWithoutPeriod(): ?string
	{
		return rtrim($this->getMessage(), ".");
	}

	public function setCode($code): Error
	{
		$this->code = $code !== null ? new Code($code) : null;

		return $this;
	}

	public function getCode(): ?Code
	{
		return $this->code;
	}

	public function setVersions(?ErrorVersionCollection $versions): Error
	{
		$this->versions = $versions;

		return $this;
	}

	public function getVersions(): ErrorVersionCollection
	{
		if (is_null($this->versions)) {
			$this->versions = new ErrorVersionCollection;
		}

		return $this->versions;
	}

	/**
	 * @deprecated
	 */
	public function addVersion(Locale $locale, string $message): Error
	{
		$this->getVersions()[] = new ErrorVersion($locale, $message);

		return $this;
	}

	public function setHelp(?string $value): Error
	{
		$this->help = $value;

		return $this;
	}

	public function getHelp(): ?string
	{
		return $this->help;
	}

	public function setOptions(?array $value): Error
	{
		$this->options = $value;

		return $this;
	}

	public function getOptions(): ?array
	{
		return $this->options;
	}

	public function setParams(ParamCollection $params): Error
	{
		$this->params = $params;

		return $this;
	}

	public function getParams(): ParamCollection
	{
		if (!isset($this->params) || !$this->params) {
			$this->params = new ParamCollection;
		}

		return $this->params;
	}

	public function addParam(Param $param): Error
	{
		$this->getParams()->append($param);

		return $this;
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		$array = [
			"message" => $this->getMessage(),
		];

		$codeString = (string)$this->getCode();
		if ($codeString) {
			$array["code"] = $codeString;
		}

		if ($this->getVersions()) {
			$array["versions"] = $this->getVersions()->getRestResponse($request, $options);
		}

		if ($this->getHelp()) {
			$array["help"] = $this->getHelp();
		}

		if ($this->getOptions()) {
			$array["options"] = $this->getOptions();
		}

		if (count($this->getParams())) {
			$array["params"] = $this->getParams()->getRestResponse($request, $options);
		}

		return new RestResponse($array);
	}
}
