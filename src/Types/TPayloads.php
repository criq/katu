<?php

namespace Katu\Types;

class TPayloads extends \ArrayObject
{
	public static function createFromJSON(TJSON $json): TPayloads
	{
		return new static($json->getArray());
	}

	public function getJSON(): TJSON
	{
		return TJSON::createFromContents($this->getArrayCopy());
	}

	public function getPackages(): TPackages
	{
		$packages = new TPackages;
		foreach ($this as $payload) {
			$packages->append(new TPackage($payload));
		}

		return $packages;
	}
}
