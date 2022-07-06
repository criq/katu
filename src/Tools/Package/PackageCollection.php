<?php

namespace Katu\Tools\Package;

use Katu\Types\TJSON;
use Katu\Types\TPayloadCollection;

class PackageCollection extends \ArrayObject
{
	public static function createFromJSON(TJSON $json): PackageCollection
	{
		return TPayloadCollection::createFromJSON($json)->getPackages();
	}

	public function getPayloads(): TPayloadCollection
	{
		$payloads = new TPayloadCollection;
		foreach ($this as $package) {
			$payloads->append($package->getPayload());
		}

		return $payloads;
	}

	public function getJSON(): TJSON
	{
		return $this->getPayloads()->getJSON();
	}
}
