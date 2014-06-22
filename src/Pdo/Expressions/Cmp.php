<?php

namespace Katu\Pdo\Expressions;

abstract class Cmp extends \Katu\Pdo\Expression {

	public $name;
	public $value;

	public function __construct($name, $value = NULL) {
		$this->name = $name;

		if (!($value instanceof BindValue)) {
			$this->value = new BindValue(NULL, $value);
		} else {
			$this->value = $value;
		}
	}

}
