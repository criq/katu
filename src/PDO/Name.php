<?php

namespace Katu\PDO;

class Name extends \Sexy\Expression {

	public $name;

	public function __construct($name) {
		$this->name = $name instanceof static ? $name->getName() : (string)$name;
	}

	public function __toString() {
		return $this->getSql();
	}

	public function getName() {
		return $this->name;
	}

	public function getSql(&$context = []) {
		return "`" . $this->name . "`";
	}

}
