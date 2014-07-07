<?php

namespace Katu\Pdo\Expressions;

class Alias extends \Katu\Pdo\Expression {

	public $name;

	public function __construct($name) {
		$this->name = $name;
	}

	public function getSql(&$context = array()) {
		return $this->name;
	}

}
