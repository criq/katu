<?php

namespace Katu\Pdo;

class Name extends \Sexy\Expression {

	public $name;

	public function __construct($name) {
		$this->name = $name;
	}

	public function __toString() {
		return $this->getSql();
	}

	public function getSql(&$context = []) {
		return $this->name == '*' ? '*' : "`" . $this->name . "`";
	}

}
