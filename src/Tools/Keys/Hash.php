<?php

namespace Katu\Tools\Keys;

class Hash extends Key
{
	public function getParts()
	{
		$parts = new \Katu\Types\TArray;
		$parts->append(sha1(var_export($this->source, true)));

		return $parts->getArray();
	}
}
