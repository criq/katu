<?php

namespace Katu\Errors;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

class ErrorCollection extends \ArrayObject implements PackagedInterface, RestResponseInterface
{
	public static function createFromPackage(Package $package): ErrorCollection
	{
		$class = TClass::createFromPortableName($package->getPayload()["class"]);
		$className = $class->getName();

		$errors = new $className;

		foreach ($package->getPayload()["errorPackagePayloads"] as $errorPackagePayload) {
			$error = Error::createFromPackage(new Package($errorPackagePayload));
			$errors->addError($error);
		}

		return $errors;
	}

	public function getPackage(): Package
	{
		$errorPackagePayloads = [];
		foreach ($this as $error) {
			$errorPackagePayloads[] = $error->getPackage()->getPayload();
		}

		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"errorPackagePayloads" => $errorPackagePayloads,
		]);
	}

	public function addError(Error $error): ErrorCollection
	{
		$this->append($error);

		return $this;
	}

	public function addErrors(ErrorCollection $errors): ErrorCollection
	{
		foreach ($errors as $error) {
			$this->append($error);
		}

		return $this;
	}

	public function addValidationResults(array $validationResults): ErrorCollection
	{
		foreach ($validationResults as $validationResult) {
			$this->addErrors($validationResult->getErrors());
		}

		return $this;
	}

	public function getTotal(): int
	{
		return count($this);
	}

	public function hasErrors(): bool
	{
		return (bool)$this->getTotal();
	}

	public function isEmpty(): bool
	{
		return !(bool)$this->getTotal();
	}

	public function getResponseArray(): array
	{
		return [
			"errors" => array_map(function (Error $error) {
				return $error->getResponseArray();
			}, $this->getArrayCopy()),
		];
	}

	public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
	{
		return new RestResponse($this->getResponseArray());
	}
}
