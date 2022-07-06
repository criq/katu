<?php

namespace Katu\Types;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackageCollection;

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

	public function getPackages(): PackageCollection
	{
		$packages = new PackageCollection;
		foreach ($this as $payload) {
			$packages[] = new Package($payload);
		}

		return $packages;
	}
}
