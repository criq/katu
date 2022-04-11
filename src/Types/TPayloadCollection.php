<?php

namespace Katu\Types;

class TPayloadCollection extends \ArrayObject
{
	public static function createFromJSON(TJSON $json): TPayloadCollection
	{
		return new static($json->getArray());
	}

	public function getJSON(): TJSON
	{
		return TJSON::createFromContents($this->getArrayCopy());
	}

	public function getPackages(): TPackageCollection
	{
		$packages = new TPackageCollection;
		foreach ($this as $payload) {
			$packages[] = new TPackage($payload);
		}

		return $packages;
	}
}
