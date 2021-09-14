<?php

namespace Katu\Errors;

use Katu\Interfaces\Packaged;
use Katu\Types\TClass;
use Katu\Types\TPackage;

class ErrorCollection extends \ArrayObject implements Packaged
{
	public static function createFromPackage(TPackage $package): ErrorCollection
	{
		$errors = new static;

		foreach ($package->getPayload()['errorPackagePayloads'] as $errorPackagePayload) {
			$error = Error::createFromPackage(new TPackage($errorPackagePayload));
			$errors->addError($error);
		}

		return $errors;
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
}
