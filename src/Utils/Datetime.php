<?php

namespace Jabli\Utils;

class Datetime extends \DateTime {

	static function get($string = NULL) {
		return new DateTime($string);
	}

	public function getDBDatetimeFormat() {
		return $this->format('Y-m-d H:i:s');
	}

}
