<?php

namespace Katu\PDO\Expressions;

class Expression {

	public $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function getValue() {
		if ($this->value instanceof \DateTime) {
			return $this->value->format('Y-m-d H:i:s');
		}

		return (string) $this->value;
	}

}
