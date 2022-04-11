<?php

namespace Katu\Types;

class TPackageCollection extends \ArrayObject
{
	public static function createFromJSON(TJSON $json) : TPackageCollection
	{
		return TPayloadCollection::createFromJSON($json)->getPackages();
	}

	public function getPayloads() : TPayloadCollection
	{
		$payloads = new TPayloadCollection;
		foreach ($this as $package) {
			$payloads->append($package->getPayload());
		}

		return $payloads;
	}

	public function getJSON() : TJSON
	{
		return $this->getPayloads()->getJSON();
	}
}
