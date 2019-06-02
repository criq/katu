<?php

namespace Katu\Tools\Keys\Types;

class TNumber extends \Katu\Tools\Keys\Key {

	public function getParts() {
		$parts = new \Katu\Types\TArray;
		$parts->append($this->getSanitizedString($this->source));

		return $parts;
	}

}
