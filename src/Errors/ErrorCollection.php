<?php

namespace Katu\Errors;

use Katu\Interfaces\Packaged;
use Katu\Types\TClass;
use Katu\Types\TPackage;

class ErrorCollection extends \ArrayObject implements Packaged
{
	public static function createFromPackage(TPackage $package): ErrorCollection
	{
		$class = TClass::createFromPortableName($package->getPayload()['classPortableName']);
		$className = $class->getName();

		$errorCollection = new $className;

		foreach ($package->getPayload()['errorPackagePayloads'] as $errorPackagePayload) {
			$error = Error::createFromPackage(new TPackage($errorPackagePayload));
			$errorCollection->addError($error);
		}

		return $errorCollection;
	}

	public function getPackage(): TPackage
	{
		$errorPackagePayloads = [];
		foreach ($this as $error) {
			$errorPackagePayloads[] = $error->getPackage()->getPayload();
		}

		return new TPackage([
			'classPortableName' => (new TClass($this))->getPortableName(),
			'errorPackagePayloads' => $errorPackagePayloads,
		]);
	}

	public function addError(Error $error): ErrorCollection
	{
		$this->append($error);

		return $this;
	}

	public function addErrorCollection(ErrorCollection $errorCollection): ErrorCollection
	{
		foreach ($errorCollection as $error) {
			$this->append($error);
		}

		return $this;
	}

	public function addValidationResults(array $validationResults): ErrorCollection
	{
		foreach ($validationResults as $validationResult) {
			$this->addErrorCollection($validationResult->getErrors());
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
			'errors' => array_map(function (Error $error) {
				return $error->getResponseArray();
			}, $this->getArrayCopy()),
		];
	}
}
