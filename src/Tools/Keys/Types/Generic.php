<?php

namespace Katu\Tools\Keys\Types;

class Generic extends \Katu\Tools\Keys\Key {

	public function getParts() {
		$parts = new \Katu\Types\TArray;
		$parts->append($this->getHashWithPrefix($this->source));

		return $parts;
	}

}
