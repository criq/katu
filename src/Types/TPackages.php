<?php

namespace Katu\Types;

class TPackages extends \ArrayObject
{
	public static function createFromJSON(TJSON $json) : TPackages
	{
		return TPayloads::createFromJSON($json)->getPackages();
	}

	public function getPayloads() : TPayloads
	{
		$payloads = new TPayloads;
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
