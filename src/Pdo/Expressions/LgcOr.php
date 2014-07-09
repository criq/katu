<?php

namespace Katu\Pdo\Expressions;

class LgcOr extends \Katu\Pdo\Expression {

	public $expressions = array();

	public function __construct($expressions) {
		$this->expressions = $expressions;
	}

	public function getSql(&$context = array()) {
		return implode(" OR ", array_map(function($i) use(&$context) {
			return " ( " . $i->getSql($context) . " ) ";
		}, $this->expressions));
	}

}
