<?php

namespace Katu\Types;

class TURLCollection extends \ArrayObject
{
	public function getJSON(): \Katu\Types\TJSON
	{
		return new TJSON(\Katu\Files\Formats\JSON::encodeInline(array_map(function (TURL $url) {
			return (string)$url;
		}, $this->getArrayCopy())));
	}
}
